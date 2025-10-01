<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Use app timezone (config/app.php) - you can set to 'Asia/Kolkata'
        $now = Carbon::now();

        // Today range (inclusive start, inclusive end of day)
        $todayStart = $now->copy()->startOfDay();
        $todayEnd   = $now->copy()->endOfDay();

        // Month range
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        // --- TODAY TOTAL (daily expenses + salaries) ---
        $todayDaily = DB::table('daily_expence_master')
            ->where('isDelete', 0)->where('iStatus', 1)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('amount');

        $todaySalary = DB::table('emp_salary')
            ->where('isDelete', 0)->where('iStatus', 1)
            ->whereBetween('salary_date', [$todayStart, $todayEnd])
            ->sum('salary_amount');

        $todayTotal = (int)$todayDaily + (int)$todaySalary;

        // --- MONTH TOTAL (daily expenses + salaries) ---
        $monthDaily = DB::table('daily_expence_master')
            ->where('isDelete', 0)->where('iStatus', 1)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthSalary = DB::table('emp_salary')
            ->where('isDelete', 0)->where('iStatus', 1)
            ->whereBetween('salary_date', [$monthStart, $monthEnd])
            ->sum('salary_amount');

        $monthTotal = (int)$monthDaily + (int)$monthSalary;

        return view('admin.daily_expence.expense-cards', [
            'todayTotal' => $todayTotal,
            'monthTotal' => $monthTotal,
        ]);
    }

    /**
     * List view that unions daily_expence_master and emp_salary
     * period=today|month|custom  with optional date_from/date_to (YYYY-MM-DD)
     */
    public function list(Request $request)
    {
        $period = $request->string('period', 'today')->toString();
        $now    = Carbon::now();

        if ($period === 'month') {
            $from = $now->copy()->startOfMonth();
            $to   = $now->copy()->endOfMonth();
        } elseif ($period === 'custom' && $request->filled(['date_from','date_to'])) {
            $from = Carbon::parse($request->input('date_from'))->startOfDay();
            $to   = Carbon::parse($request->input('date_to'))->endOfDay();
        } else {
            // default today
            $from = $now->copy()->startOfDay();
            $to   = $now->copy()->endOfDay();
        }

        // Build queries with common shape: src, id, txn_date, label, amount, comment
        $expenseQuery = DB::table('daily_expence_master as de')
            ->leftJoin('daily_expence_type as et', 'et.expence_type_id', '=', 'de.expence_type_id') // if exists
            ->selectRaw("
                'expense' as src,
                de.expence_id as id,
                de.created_at as txn_date,
                COALESCE(et.type_name, 'Expense') as label,
                de.amount as amount,
                de.comment as comment
            ")
            ->where('de.isDelete', 0)->where('de.iStatus', 1)
            ->whereBetween('de.created_at', [$from, $to]);

        $salaryQuery = DB::table('emp_salary as es')
            ->leftJoin('employee_master as em', 'em.emp_id', '=', 'es.emp_id') // if exists
            ->selectRaw("
                'salary' as src,
                es.emp_salary_id as id,
                es.salary_date as txn_date,
                CONCAT('Salary', ' - ', COALESCE(em.emp_name, es.emp_id)) as label,
                es.salary_amount as amount,
                NULL as comment
            ")
            ->where('es.isDelete', 0)->where('es.iStatus', 1)
            ->whereBetween('es.salary_date', [$from, $to]);

        // UNION + order + paginate
        $union = $expenseQuery->unionAll($salaryQuery);

        $rows = DB::query()->fromSub($union, 'u')
            ->orderBy('txn_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Totals for the list header
        $totalAmount = (int) DB::query()->fromSub($union, 't')->sum('t.amount');

        return view('dashboard.expense-list', [
            'rows'        => $rows,
            'period'      => $period,
            'date_from'   => $request->input('date_from'),
            'date_to'     => $request->input('date_to'),
            'from'        => $from,
            'to'          => $to,
            'totalAmount' => $totalAmount,
        ]);
    }
}
