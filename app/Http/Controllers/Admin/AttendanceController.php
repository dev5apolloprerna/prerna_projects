<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpAttendance;
use App\Models\EmployeeMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date          = $request->input('date', now()->toDateString());
        $q             = $request->input('q');                 // search by employee name/mobile/email
        $filterStatus  = strtoupper($request->input('filter_status', '')); // P/H/A

        // Base query for employees
        $employeesQ = EmployeeMaster::orderBy('name', 'asc');

        if ($q) {
            $employeesQ->where(function($qq) use ($q){
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        // If a status filter is selected, only include employees having that status on the chosen date
        if (in_array($filterStatus, ['P','H','A'])) {
            $empIds = \App\Models\EmpAttendance::where('attendance_date', $date)
                        ->where('status', $filterStatus)
                        ->pluck('emp_id');
            // If none matched, show empty quickly
            $employeesQ->whereIn('emp_id', $empIds->isEmpty() ? [-1] : $empIds);
        }

        // Grab the employees you want to show (add columns as you have them)
        $employees = $employeesQ->get(['emp_id','name','mobile']);

        // Existing attendance (to preselect statuses in UI)

$existing       = EmpAttendance::whereDate('attendance_date', $date)->pluck('status', 'emp_id');
$existingReason = EmpAttendance::whereDate('attendance_date', $date)->pluck('leave_reason', 'emp_id');
// pass $existingReason to the view
return view('admin.attendance.index', compact('employees','existing','existingReason','q','date','filterStatus'));


        //return view('admin.attendance.index', compact('employees','date','existing','q','filterStatus'));
    }
    public function names(Request $request)
    {
        $date   = $request->query('date', now()->toDateString());
        $status = $request->query('status', 'P'); // 'P' or 'A'

        $rows = EmpAttendance::whereDate('attendance_date', Carbon::parse($date))
            ->where('status', $status)
            ->with(['employee' => fn($q) => $q->select('emp_id','name','mobile')])
            ->orderBy('emp_id')
            ->get();

        $list = $rows->map(fn($r) => [
            'id'   => $r->emp_id,
            'name' => $r->employee->name ?? ('Emp #'.$r->emp_id),
            'mobile' => $r->employee->mobile ?? ('mobile'.$r->emp_id),
        ])->values();

        $title = ($status === 'P' ? 'Present' : 'Absent').' Employees — '.Carbon::parse($date)->format('d M Y');

        return response()->json(['title' => $title, 'employees' => $list]);
    }
    public function store(Request $request)
{
    $data = $request->validate([
        'attendance_date' => ['required','date'],
        'selected'        => ['array'], // optional
        'attendance'      => ['array'], // attendance[empId][status], attendance[empId][reason]
        // optional: validate each reason
        // 'attendance.*.reason' => ['nullable','string','max:255'],
    ]);

    $date     = $data['attendance_date'] ?? $request->input('date');
    $selected = $data['selected'] ?? [];
    $map      = $data['attendance'] ?? [];

    if (empty($selected) && !empty($map)) {
        $selected = array_keys($map);
    }
    if (empty($selected)) {
        return back()->with('error','Please select at least one employee.')->withInput();
    }

    $enteredBy = optional(\Auth::user())->role_id ?? 'system';

    \DB::transaction(function () use ($selected, $map, $date, $enteredBy) {
        foreach ($selected as $empId) {
            $status = strtoupper($map[$empId]['status'] ?? 'P');
            if (!in_array($status, ['P','A','H'])) $status = 'P';

            // take reason only for A/H, trim & limit
            $rawReason = $map[$empId]['reason'] ?? null;
            $reason = ($status === 'P') ? null : ( $rawReason ? Str::limit(trim($rawReason), 255, '') : null );

            
            EmpAttendance::updateOrCreate(
                ['emp_id' => $empId, 'attendance_date' => $date],
                [
                    'status'       => $status,
                    'leave_reason' => $reason,
                    'enter_by'     => $enteredBy,
                    'iStatus'      => 1,
                    'isDelete'     => 0,
                ]
            );
        }
    });

    return redirect()->route('attendance.index', ['date' => $date])
        ->with('success', 'Attendance saved.');
}

    public function employeeAttendance($empId)
    {
        $from = request('from', now()->startOfMonth()->toDateString());
        $to   = request('to',   now()->endOfMonth()->toDateString());
    
        $employee = EmployeeMaster::findOrFail($empId);
    
        $attendances = EmpAttendance::where('emp_id', $empId)
            ->whereBetween('attendance_date', [$from, $to])
            ->orderByDesc('attendance_date')
            ->paginate(25); // or ->get()
    
        return view('admin.attendance.show', compact('employee', 'attendances'));
    }

}
