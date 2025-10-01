<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeMaster;
use Illuminate\Http\Request;

class EmployeeMasterController extends Controller
{
    // Listing with search (Name, Designation, Mobile). Pagination, bulk.
    public function index(Request $request)
    {
        $query = EmployeeMaster::query();

        // not showing deleted; we are using hard delete due to unique index on 'mobile'
        // so no isDelete filter is required.

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('designation', 'like', "%{$s}%")
                  ->orWhere('mobile', 'like', "%{$s}%");
            });
        }

        // Optional status filter if you ever pass ?status=1/0
        if ($request->filled('status') && in_array($request->status, ['0','1'])) {
            $query->where('iStatus', (int)$request->status);
        }

        $employees = $query->orderByDesc('emp_id')->paginate(10)->withQueryString();

        return view('admin.employee.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employee.add-edit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'designation' => 'nullable|string|max:100',
            'mobile'      => 'required|string|max:15|unique:employee_master,mobile',
            'daily wages'     => 'nullable|int',
            'address'     => 'nullable|string|max:255',
            'iStatus'     => 'required|in:0,1',
        ]);

        EmployeeMaster::create([
            'name'        => $request->name,
            'designation' => $request->designation,
            'mobile'      => $request->mobile,
            'daily_wages'      => $request->daily_wages,
            'address'     => $request->address,
            'iStatus'     => (int)$request->iStatus,
        ]);

        return redirect()->route('employee.index')->with('success', 'Employee added successfully.');
    }

    public function edit($id)
    {
        $employee = EmployeeMaster::findOrFail($id);
        return view('admin.employee.add-edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = EmployeeMaster::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:100',
            'designation' => 'nullable|string|max:100',
            'mobile'      => 'required|string|max:15|unique:employee_master,mobile,' . $employee->emp_id . ',emp_id',
            'daily wages'     => 'nullable|int',
            'address'     => 'nullable|string|max:255',
            'iStatus'     => 'required|in:0,1',
        ]);

        $employee->update([
            'name'        => $request->name,
            'designation' => $request->designation,
            'mobile'      => $request->mobile,
            'daily_wages'      => $request->daily_wages,
            'address'     => $request->address,
            'iStatus'     => (int)$request->iStatus,
        ]);

        return redirect()->route('employee.index')->with('success', 'Employee updated successfully.');
    }

    // HARD DELETE (unique index exists on `mobile`)
    public function destroy($id)
    {
        $employee = EmployeeMaster::findOrFail($id);
        $employee->delete();

        return response()->json(['status' => true, 'message' => 'Employee deleted successfully.']);
    }

    // HARD bulk delete (no <form>, called via AJAX)
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (count($ids)) {
            EmployeeMaster::whereIn('emp_id', $ids)->delete();
        }

        return response()->json(['status' => true, 'message' => 'Selected employees deleted successfully.']);
    }

    // Toggle Active/Inactive
    public function changeStatus($id)
    {
        $employee = EmployeeMaster::findOrFail($id);
        $employee->iStatus = $employee->iStatus == 1 ? 0 : 1;
        $employee->save();

        return response()->json(['status' => true, 'new_status' => $employee->iStatus]);
    }
}
