<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;
use App\Models\IdpMilestone;
use App\Models\AuditLog;

class IdpMilestoneController extends Controller
{
    public function store(Request $request, Idp $idp)
    {
        $this->authorize('update', $idp);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_required' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:open,in_progress,completed,blocked',
        ]);
        $data['idp_id'] = $idp->id;
        $milestone = IdpMilestone::create($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_milestone_created',
            'table_name' => 'idp_milestones',
            'record_id' => $milestone->id,
            'details' => "IDP milestone created for IDP {$idp->id}: {$milestone->title}",
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $html = view('appraisal.idp.partials._milestone_row', compact('milestone', 'idp'))->render();
            return response()->json(['html' => $html, 'milestone' => $milestone], 201);
        }
        return redirect()->back()->with('success', 'Milestone created');
    }

    public function update(Request $request, Idp $idp, IdpMilestone $milestone)
    {
        $this->authorize('update', $idp);
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'resource_required' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:open,in_progress,completed,blocked',
        ]);
        $milestone->update($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_milestone_updated',
            'table_name' => 'idp_milestones',
            'record_id' => $milestone->id,
            'details' => "IDP milestone updated for IDP {$idp->id}: {$milestone->title}",
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $html = view('appraisal.idp.partials._milestone_row', compact('milestone', 'idp'))->render();
            return response()->json(['html' => $html, 'milestone' => $milestone], 200);
        }
        return redirect()->back()->with('success', 'Milestone updated');
    }

    public function destroy(Idp $idp, IdpMilestone $milestone)
    {
        $this->authorize('delete', $idp);
        $milestoneId = $milestone->id;
        $milestone->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_milestone_deleted',
            'table_name' => 'idp_milestones',
            'record_id' => $milestoneId,
            'details' => "IDP milestone deleted for IDP {$idp->id}: {$milestoneId}",
        ]);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['deleted' => true, 'id' => $milestoneId], 200);
        }
        return redirect()->back()->with('success', 'Milestone removed');
    }

    /**
     * Mark attainment on a milestone and optionally attach visible demonstration and HR input.
     */
    public function attain(Request $request, Idp $idp, IdpMilestone $milestone)
    {
        $this->authorize('attain', $idp);

        // Ensure revisions allowed
        $fy = \App\Models\FinancialYear::active();
        if ($fy && !$fy->isRevisionAllowed()) {
            return redirect()->back()->with('error', 'IDP milestone updates are closed for the active financial year.');
        }

        $data = $request->validate([
            'attainment' => 'nullable|boolean',
            'visible_demonstration' => 'nullable|string',
            'hr_input' => 'nullable|string',
        ]);

        $user = auth()->user();
        if (isset($data['attainment'])) {
            $milestone->attainment = (bool)$data['attainment'];
            $milestone->attained_by_id = $user->id;
            $milestone->attained_at = now();
        }
        if (isset($data['visible_demonstration'])) {
            $milestone->visible_demonstration = $data['visible_demonstration'];
        }
        if (isset($data['hr_input'])) {
            $milestone->hr_input = $data['hr_input'];
        }
        $milestone->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'idp_milestone_attained',
            'table_name' => 'idp_milestones',
            'record_id' => $milestone->id,
            'details' => "Milestone attainment updated for IDP {$idp->id} milestone {$milestone->id} by user {$user->id}",
        ]);
        if ($request->wantsJson() || $request->ajax()) {
            $html = view('appraisal.idp.partials._milestone_row', compact('milestone', 'idp'))->render();
            return response()->json(['html' => $html, 'milestone' => $milestone], 200);
        }

        return redirect()->back()->with('success', 'Milestone updated');
    }
}
