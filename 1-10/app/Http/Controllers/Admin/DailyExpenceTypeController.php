<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExpenceType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DailyExpenceTypeController extends Controller
{
    public function index(Request $request)
    {
        $q = DailyExpenceType::alive();

        if ($search = $request->get('search')) {
            $q->where('type', 'like', "%{$search}%");
        }

        $types = $q->orderBy('type')->paginate(12)->withQueryString();

        return view('admin.daily-expence-types.index', compact('types'));
    }

    public function store(Request $request)
    {
       $data = $request->validate([
        'type' => [
            'required','max:150',
            Rule::unique('daily_expence_type', 'type')
                ->where(fn($q) => $q->where('isDelete', 0)),
            ],
            'iStatus' => ['nullable', Rule::in([0,1])],
        ]);

        $data['iStatus']  = $data['iStatus'] ?? 1;
        $data['isDelete'] = 0;

        DailyExpenceType::create($data);

        return redirect()
            ->route('admin.daily-expence-types.index')
            ->with('success', 'Expense type added successfully.');
    }

    /** Return JSON for edit modal */
    public function show($id)
    {
        $row = DailyExpenceType::alive()->findOrFail($id);
        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $row = DailyExpenceType::alive()->findOrFail($id);

        $data = $request->validate([
             'type' => [
                'required','max:150',
                Rule::unique('daily_expence_type', 'type')
                    ->where(fn($q) => $q->where('isDelete', 0))
                    ->ignore($id, 'expence_type_id'),
            ],
            'iStatus' => ['required', Rule::in([0,1])],
        ]);

        $row->update($data);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('admin.daily-expence-types.index')
            ->with('success', 'Expense type updated.');
    }

    /** Soft delete single */
    public function destroy($id)
    {
        $row = DailyExpenceType::alive()->findOrFail($id);
        $row->update(['isDelete' => 1]);

        return redirect()
            ->route('admin.daily-expence-types.index')
            ->with('success', 'Expense type deleted.');
    }

    /** Bulk soft delete */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No items selected.');
        }

        DailyExpenceType::whereIn('expence_type_id', $ids)->update(['isDelete' => 1]);
        return back()->with('success', 'Selected types deleted.');
    }

    /** Quick status toggle (AJAX) */
    public function toggleStatus($id)
    {
        $row = DailyExpenceType::alive()->findOrFail($id);
        $row->iStatus = $row->iStatus ? 0 : 1;
        $row->save();

        return response()->json(['ok' => true, 'iStatus' => $row->iStatus]);
    }
}
