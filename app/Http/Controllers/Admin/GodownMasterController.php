<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GodownMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GodownMasterController extends Controller
{
    // 1-page view with add (left) + list (right)
    public function index(Request $request)
    {
        $query = GodownMaster::notDeleted();

        // Search: by Name and Address
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('Name', 'like', "%{$s}%")
                  ->orWhere('godown_address', 'like', "%{$s}%");
            });
        }

        $godowns = $query->orderByDesc('godown_id')->paginate(10)->withQueryString();
        return view('admin.godown.index', compact('godowns'));
    }

    // Create (AJAX form on same page)
    public function store(Request $request)
    {
        $request->validate([
        'Name' => [
            'required','max:150',
            Rule::unique('godown_master', 'Name')
                ->where(fn($q) => $q->where('isDelete', 0)),
            ],
            'godown_address' => ['nullable','max:255'],
            'iStatus' => ['nullable','in:0,1'],
        ]);

        GodownMaster::create([
            'Name'           => $request->Name,
            'godown_address' => $request->godown_address,
            'iStatus'        => (int)$request->iStatus,
        ]);

        return redirect()->route('godown.index')->with('success', 'Godown added successfully.');
    }

    // Return JSON for modal edit
    public function show($id)
    {
        $row = GodownMaster::notDeleted()->findOrFail($id);
        return response()->json([
            'status' => true,
            'data'   => [
                'godown_id'      => $row->godown_id,
                'Name'           => $row->Name,
                'godown_address' => $row->godown_address,
                'iStatus'        => $row->iStatus,
            ],
        ]);
    }

    // Update (from modal)
    public function update(Request $request, $id)
    {
        $row = GodownMaster::notDeleted()->findOrFail($id);

        $request->validate([
        'Name' => [
            'required','max:150',
            Rule::unique('godown_master', 'Name')
                ->where(fn($q) => $q->where('isDelete', 0))
                ->ignore($id, 'godown_id'),
        ],
        'godown_address' => ['nullable','max:255'],
        'iStatus' => ['nullable','in:0,1'],
    ]);

        $row->update([
            'Name'           => $request->Name,
            'godown_address' => $request->godown_address,
            'iStatus'        => (int)$request->iStatus,
        ]);

        return response()->json(['status' => true, 'message' => 'Updated successfully.']);
    }

    // SOFT delete (set isDelete = 1)
    public function destroy($id)
    {
        $row = GodownMaster::notDeleted()->findOrFail($id);
        $row->isDelete = 1;
        $row->save();

        return response()->json(['status' => true, 'message' => 'Deleted successfully.']);
    }

    // Bulk soft delete
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (!count($ids)) {
            return response()->json(['status' => false, 'message' => 'No IDs selected.']);
        }

        GodownMaster::whereIn('godown_id', $ids)->update(['isDelete' => 1]);
        return response()->json(['status' => true, 'message' => 'Selected records deleted.']);
    }

    // Toggle Active/Inactive
    public function changeStatus($id)
    {
        $row = GodownMaster::notDeleted()->findOrFail($id);
        $row->iStatus = $row->iStatus == 1 ? 0 : 1;
        $row->save();

        return response()->json(['status' => true, 'new_status' => $row->iStatus]);
    }
}
