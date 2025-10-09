<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyExpence;
use Carbon\Carbon;
use DB;

class ExpenceReportController extends Controller
{
    public function index(Request $request)
    {
        // Filters (optional)
        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        $query = DailyExpence::notDeleted();

        if ($start && $end) {
            $query->whereBetween('expence_date', [$start, $end]);
        }

        // Group by date
        $records = $query->select(
                'expence_date',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(expence_id) as count')
            )
            ->groupBy('expence_date')
            ->orderByDesc('expence_date')
            ->get();

        $grandTotal = $records->sum('total_amount');

        return view('admin.reports.expence_report', compact('records', 'grandTotal', 'start', 'end'));
    }
    public function show($date)
    {
        $expences = DailyExpence::notDeleted()
            ->whereDate('expence_date', $date)
            ->with('types')
            ->orderByDesc('expence_id')
            ->get();

        $total = $expences->sum('amount');

        // Return small HTML snippet for modal body
        return view('admin.reports.expence_detail', compact('expences', 'total'))->render();
    }


    /*public function show($date)
    {
        $expences = DailyExpence::notDeleted()
            ->whereDate('expence_date', $date)
            ->with('types')
            ->orderByDesc('expence_id')
            ->get();

        $total = $expences->sum('amount');

        return view('admin.reports.expence_show', compact('expences', 'date', 'total'));
    }*/
}
