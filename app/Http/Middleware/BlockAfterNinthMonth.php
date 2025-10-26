<?php

namespace App\Http\Middleware;

use App\Services\FinancialYearService;
use Closure;
use Illuminate\Http\Request;

class BlockAfterNinthMonth
{
    protected FinancialYearService $fy;

    public function __construct(FinancialYearService $fy)
    {
        $this->fy = $fy;
    }

    public function handle(Request $request, Closure $next)
    {
        $when = $request->input('date') ?? $request->input('date_of_setting') ?? now()->toDateString();
        if (! $this->fy->isBeforeNinthMonth($when)) {
            return response()->json(['message' => 'Revisions are not allowed after the 9th month of the financial year.'], 422);
        }
        return $next($request);
    }
}
