<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Models\AuditLog;

class FinancialYearController extends Controller
{
    /**
     * Safely create audit log entry, handling schema variations
     */
    protected function auditLog($action, $details, $tableName = null, $recordId = null)
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'details' => $details,
            ]);
        } catch (\Exception $e) {
            // Fallback for schema without table_name/record_id columns
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'details' => $details,
            ]);
        }
    }

    public function index()
    {
        // Eager-load objectives and appraisals to avoid N+1 and null checks in views
        $financialYears = FinancialYear::with(['objectives', 'appraisals'])->orderByDesc('start_date')->get();
        return view('financial_years.index', compact('financialYears'));
    }

    public function create()
    {
        return view('financial_years.create');
    }

    public function store(\App\Http\Requests\FinancialYearRequest $request)
    {
        $data = $request->validated();

        $start = Carbon::parse($data['start_date']);
        $data['revision_cutoff'] = $start->copy()->addMonths(9)->endOfDay();

        // keep legacy `name` populated for transition compatibility when column exists
        if (Schema::hasColumn('financial_years', 'name')) {
            $data['name'] = $data['label'];
        }

        $data['is_active'] = $request->has('is_active');
        $data['status'] = $data['is_active'] ? 'active' : 'upcoming';

        $fy = FinancialYear::create($data);

        if ($fy->is_active) {
            FinancialYear::where('id', '!=', $fy->id)->update(['is_active' => false]);
        }

        $this->auditLog('financial_year_created', "Financial year created: {$fy->label} (ID {$fy->id})", 'financial_years', $fy->id);

        return redirect()->route('financial-years.index')->with('success', 'Financial year created successfully.');
    }

    public function show(FinancialYear $financialYear)
    {
        $financialYear->loadMissing('objectives', 'appraisals');
        return view('financial_years.show', compact('financialYear'));
    }

    public function edit(FinancialYear $financialYear)
    {
        $financialYear->loadMissing('objectives', 'appraisals');
        return view('financial_years.edit', compact('financialYear'));
    }

    public function update(\App\Http\Requests\FinancialYearRequest $request, FinancialYear $financialYear)
    {
        $data = $request->validated();

        $start = Carbon::parse($data['start_date']);
        $data['revision_cutoff'] = $start->copy()->addMonths(9)->endOfDay();
        if (Schema::hasColumn('financial_years', 'name')) {
            $data['name'] = $data['label'];
        }
        $data['is_active'] = $request->has('is_active');

        $financialYear->update($data);

        if ($financialYear->is_active) {
            FinancialYear::where('id', '!=', $financialYear->id)->update(['is_active' => false]);
        }

        $this->auditLog('financial_year_updated', "Financial year updated: {$financialYear->label} (ID {$financialYear->id})", 'financial_years', $financialYear->id);

        return redirect()->route('financial-years.index')->with('success', 'Financial year updated successfully.');
    }

    public function activate(FinancialYear $financialYear)
    {
        FinancialYear::where('id', '!=', $financialYear->id)->update(['is_active' => false]);
        $financialYear->update(['is_active' => true, 'status' => 'active']);
        $this->auditLog('financial_year_activated', "Financial year activated: {$financialYear->label} (ID {$financialYear->id})", 'financial_years', $financialYear->id);

        return redirect()->route('financial-years.index')->with('success', "Financial year {$financialYear->label} is now active.");
    }

    public function close(FinancialYear $financialYear)
    {
        $financialYear->update(['is_active' => false, 'status' => 'closed']);
        $this->auditLog('financial_year_closed', "Financial year closed: {$financialYear->label} (ID {$financialYear->id})", 'financial_years', $financialYear->id);

        return redirect()->route('financial-years.index')->with('success', "Financial year {$financialYear->label} has been closed.");
    }

    public function destroy(FinancialYear $financialYear)
    {
        if ($financialYear->is_active) {
            return back()->withErrors(['error' => 'Cannot delete the active financial year.']);
        }

        $fyId = $financialYear->id;
        $fyLabel = $financialYear->label;
        $financialYear->delete();
        $this->auditLog('financial_year_deleted', "Financial year deleted: {$fyLabel} (ID {$fyId})", 'financial_years', $fyId);

        return redirect()->route('financial-years.index')->with('success', 'Financial year deleted successfully.');
    }
}
