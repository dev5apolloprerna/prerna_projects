<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExpenceType;
use App\Models\DailyExpence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class DailyExpenceController extends Controller
{
    public function index(Request $request)
    {
            $query   = DailyExpence::with('types')->where('iStatus', 1);

            $search  = $request->get('search');
            $from    = $request->get('from_date');
            $to      = $request->get('to_date');
            $typeId  = $request->get('expence_type_id');
            $preset  = $request->get('preset'); // 'today' | 'month' | null

            // Apply preset only if no custom dates were provided
            if (!$from && !$to && $preset) {
                if ($preset === 'today') {
                    $query->whereBetween('expence_date', [Carbon::today(), Carbon::today()->endOfDay()]);
                } elseif ($preset === 'month') {
                    $query->whereBetween('expence_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                }
            }

            // Custom date range overrides preset
            if ($from && $to) {
                $query->whereBetween('expence_date', [
                    Carbon::parse($from)->startOfDay(),
                    Carbon::parse($to)->endOfDay(),
                ]);
            } elseif ($from) {
                $query->where('expence_date', '>=', Carbon::parse($from)->startOfDay());
            } elseif ($to) {
                $query->where('expence_date', '<=', Carbon::parse($to)->endOfDay());
            }

            // Prefill values for the form (Y-m-d for <input type="date">)
            $uiFromDate = $from ?: ($preset === 'today'
                ? Carbon::today()->toDateString()
                : ($preset === 'month' ? Carbon::now()->startOfMonth()->toDateString() : '')
            );

            $uiToDate = $to ?: ($preset === 'today'
                ? Carbon::today()->toDateString()
                : ($preset === 'month' ? Carbon::now()->endOfMonth()->toDateString() : '')
            );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                      ->orWhere('amount', 'like', "%{$search}%")
                      ->orWhere('expence_type_id', 'like', "%{$search}%");
                });
            }

            if (!empty($typeId)) {
                $query->where('expence_type_id', $typeId);
            }

            $filteredTotal = (clone $query)->sum('amount');

            $expences = $query->orderByDesc('expence_id')
                ->paginate(10)
                ->appends($request->query());

            $types = DailyExpenceType::where(['iStatus'=>1])->get();

            return view('admin.daily-expences.index', compact(
                'expences', 'types', 'filteredTotal', 'preset', 'uiFromDate', 'uiToDate'
            ));

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expence_type_id' => ['required','integer','min:1'],
            'amount'          => ['required','integer','min:0'],
            'expence_date'          => ['required'],
            'comment'         => ['nullable','string'],
            'iStatus'         => ['nullable', Rule::in([0,1])],
        ]);

        $data['iStatus']  = $data['iStatus'] ?? 1;
        $data['isDelete'] = 0;

        DailyExpence::create($data);

        return redirect()
            ->route('admin.daily-expences.index')
            ->with('success', 'Expense added successfully.');
    }

    /** Return one record as JSON for the edit modal */
    public function show($id)
    {
        $row = DailyExpence::alive()->findOrFail($id);
        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $row = DailyExpence::alive()->findOrFail($id);

        $data = $request->validate([
            'expence_type_id' => ['required','integer','min:1'],
            'amount'          => ['required','integer','min:0'],
            'expence_date'          => ['required'],
            'comment'         => ['nullable','string'],
            'iStatus'         => ['required', Rule::in([0,1])],
        ]);

        $row->update($data);

        // For AJAX edits
        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('admin.daily-expences.index')
            ->with('success', 'Expense updated successfully.');
    }

    /** Soft delete single */
    public function destroy($id)
    {
        $row = DailyExpence::alive()->findOrFail($id);
        $row->update(['isDelete' => 1]);

        return redirect()
            ->route('admin.daily-expences.index')
            ->with('success', 'Expense deleted.');
    }

    /** Bulk soft delete */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No items selected.');
        }

        DailyExpence::whereIn('expence_id', $ids)->update(['isDelete' => 1]);
        return back()->with('success', 'Selected expenses deleted.');
    }

    /** Quick status toggle (AJAX) */
    public function toggleStatus($id)
    {
        $row = DailyExpence::alive()->findOrFail($id);
        $row->iStatus = $row->iStatus ? 0 : 1;
        $row->save();

        return response()->json(['ok' => true, 'iStatus' => $row->iStatus]);
    }
}
