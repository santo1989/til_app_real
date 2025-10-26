<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;
use App\Models\IdpMilestone;

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
        if ($request->wantsJson() || $request->ajax()) {
            $milestones = $idp->milestones()->orderBy('id')->get();
            return response()->json(['milestones' => $milestones, 'milestone' => $milestone], 201);
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
        if ($request->wantsJson() || $request->ajax()) {
            $milestones = $idp->milestones()->orderBy('id')->get();
            return response()->json(['milestones' => $milestones, 'milestone' => $milestone], 200);
        }
        return redirect()->back()->with('success', 'Milestone updated');
    }

    public function destroy(Idp $idp, IdpMilestone $milestone)
    {
        $this->authorize('update', $idp);
        $milestone->delete();
        if (request()->wantsJson() || request()->ajax()) {
            $milestones = $idp->milestones()->orderBy('id')->get();
            return response()->json(['milestones' => $milestones], 200);
        }
        return redirect()->back()->with('success', 'Milestone removed');
    }
}
