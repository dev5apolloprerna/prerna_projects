<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeExtraWithdrawal;
use App\Models\EmployeeMaster;
use Illuminate\Validation\Rule;

class EmployeeExtraWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $q = EmployeeExtraWithdrawal::with('employee');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->whereHas('employee', fn($x) => 
                $x->where('name', 'like', "%{$s}%")
            )->orWhere('reason', 'like', "%{$s}%");
        }

        $withdrawals = $q->orderByDesc('withdrawal_date')->paginate(10)->withQueryString();
        $employees = EmployeeMaster::select('emp_id', 'name')->orderBy('name')->get();

        return view('admin.emp_withdrawal.index', compact('withdrawals', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'emp_id' => ['required', 'exists:employee_master,emp_id'],
            'withdrawal_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
            'emi_amount' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        // Set remaining_amount = amount when created
        $validated['remaining_amount'] = $validated['amount'];

        EmployeeExtraWithdrawal::create($validated);

        return back()->with('success', 'Withdrawal added successfully.');
    }
    public function edit($id)
    {
        return response()->json(EmployeeExtraWithdrawal::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $data = EmployeeExtraWithdrawal::findOrFail($id);

        $validated = $request->validate([
            'emp_id' => ['required', 'exists:employee_master,emp_id'],
            'withdrawal_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
            'emi_amount' => ['nullable', 'integer', 'min:0'],
            'remaining_amount' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data->update($validated);
        return back()->with('success', 'Withdrawal updated successfully.');
    }

    public function destroy(Request $request)
    {
        $ids = $request->ids ?? [];
        if (count($ids)) {
            EmployeeExtraWithdrawal::whereIn('withdrawal_id', $ids)->delete();
        }
        return back()->with('success', 'Selected records deleted.');
    }
}
