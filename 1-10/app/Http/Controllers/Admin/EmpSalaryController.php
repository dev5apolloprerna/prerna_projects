<?php
// app/Http/Controllers/Admin/EmpSalaryController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpSalary;
use App\Models\EmployeeMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmpSalaryController extends Controller
{
    public function index(Request $request)
    {
        $q        = $request->input('q');            // employee name/mobile/email
        $from     = $request->input('from');         // YYYY-MM-DD
        $to       = $request->input('to');           // YYYY-MM-DD
        $empId    = $request->input('emp_id');       // filter by employee
        $status   = $request->input('status');       // 1/0

        $employees = EmployeeMaster::orderBy('name')->get(['emp_id','name']);

        $rows = EmpSalary::query()
            ->with('employee')
            ->when($empId, fn($q2) => $q2->where('emp_id', $empId))
            ->when(in_array($status, ['0','1'], true), fn($q2) => $q2->where('iStatus', $status))
            ->when($from, fn($q2) => $q2->whereDate('salary_date', '>=', $from))
            ->when($to,   fn($q2) => $q2->whereDate('salary_date', '<=', $to))
            ->when($q, function ($q2) use ($q) {
                $q2->whereHas('employee', function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('mobile', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('emp_salary_id')
            ->paginate(15)
            ->withQueryString();

        // totals for current filter
        $totals = EmpSalary::query()
            ->when($empId, fn($q2) => $q2->where('emp_id', $empId))
            ->when(in_array($status, ['0','1'], true), fn($q2) => $q2->where('iStatus', $status))
            ->when($from, fn($q2) => $q2->whereDate('salary_date', '>=', $from))
            ->when($to,   fn($q2) => $q2->whereDate('salary_date', '<=', $to))
            ->sum('salary_amount');

        return view('admin.emp_salary.index', compact('rows','employees','q','from','to','empId','status','totals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'emp_id'        => ['required','integer','exists:employee_master,emp_id'],
            'salary_date'   => ['required','date'],
            'salary_amount' => ['required','numeric','min:0'],
            'iStatus'       => ['required','in:0,1'],
        ]);

        // datetime-local comes as 'Y-m-d\TH:i'
        $data['salary_date'] = Carbon::parse($data['salary_date'])->format('Y-m-d H:i:s');

        EmpSalary::create($data + ['isDelete' => 0]);

        return back()->with('success', 'Salary entry added.');
    }

    public function update(Request $request, EmpSalary $emp_salary)
    {
        $data = $request->validate([
            'emp_id'        => ['required','integer','exists:employee_master,emp_id'],
            'salary_date'   => ['required','date'],
            'salary_amount' => ['required','numeric','min:0'],
            'iStatus'       => ['required','in:0,1'],
        ]);

        $data['salary_date'] = Carbon::parse($data['salary_date'])->format('Y-m-d H:i:s');

        $emp_salary->update($data);

        return back()->with('success', 'Salary entry updated.');
    }

    public function destroy(EmpSalary $emp_salary)
    {
        // MyISAM → hard delete (or set isDelete=1 if you prefer)
        $emp_salary->delete();
        return back()->with('success', 'Salary entry deleted.');
    }
}
