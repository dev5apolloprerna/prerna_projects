<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tanker;
use App\Models\GodownMaster;
use Illuminate\Validation\Rule;

class TankerController extends Controller
{
    public function index(Request $request)
    {
        $query = Tanker::with('godown')->where('isDelete', 0);

        if ($request->tanker_name) {
            $query->where('tanker_name', 'LIKE', '%' . $request->tanker_name . '%');
        }

        if ($request->tanker_code) {
            $query->where('tanker_code', 'LIKE', '%' . $request->tanker_code . '%');
        }

        $tankers = $query->orderBy('tanker_id', 'DESC')->paginate(10);
        $godown=GodownMaster::where('isDelete',0)->where('iStatus',1)->get();

        return view('admin.tanker.index', compact('tankers','godown'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanker_name' => [
                'required','max:150',
                Rule::unique('tanker_master', 'tanker_name')
                    ->where(fn($q) => $q->where('isDelete', 0)),
            ],
            'tanker_code' => ['required','max:150'], // make unique if you want
            'godown_id' => ['required','max:150'], // make unique if you want
            'status'      => 'required|in:0,1',
        ]);

        $slug = \App\Models\Tanker::makeUniqueSlug($request->input('tanker_name'));

            Tanker::create([
                    'tanker_name' => $request->input('tanker_name'),
                    'tanker_code' => $request->input('tanker_code'),
                    'godown_id' => $request->input('godown_id'),
                    'status'      => (int)$request->input('status'),
                    'slug'        => $slug,
                ]);
        return redirect()->route('tanker.index')->with('success', 'Tanker added successfully.');
    }

    public function edit($id)
    {
        $tanker = Tanker::findOrFail($id);
        return response()->json($tanker);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanker_name' => [
                'required','max:150',
                Rule::unique('tanker_master', 'tanker_name')
                    ->where(fn($q) => $q->where('isDelete', 0))
                    ->ignore($id, 'tanker_id'),
            ],
            'tanker_code' => ['required','max:150'], // make unique if you want
            'godown_id' => ['required','max:150'], // make unique if you want
            'status'      => 'required|in:0,1',
        ]);

        $tanker = \App\Models\Tanker::findOrFail($id);

        // If name changed or slug empty, refresh slug (keeps it unique among non-deleted)
        $slug = $tanker->slug;
        if ($tanker->tanker_name !== $request->input('tanker_name') || empty($slug)) {
            $slug = \App\Models\Tanker::makeUniqueSlug($request->input('tanker_name'), $tanker->tanker_id);
        }

        $tanker->update([
            'tanker_name' => $request->input('tanker_name'),
            'tanker_code' => $request->input('tanker_code'),
            'godown_id' => $request->input('godown_id'),
            'status'      => (int)$request->input('status'),
            'slug'        => $slug,
        ]);

        return redirect()->route('tanker.index')->with('success', 'Tanker updated successfully.');
    }
    public function inGodown(Request $request)
    {
        $query =Tanker::query()
            ->where(['status' => 0, 'iStatus' => 1, 'isDelete' => 0]);

        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('tanker_name', 'like', "%{$search}%")
                ->orWhere('tanker_code', 'like', "%{$search}%")
                ->orWhere('tanker_location', 'like', "%{$search}%");
            });
        }

        $tankers = $query->with('order','godown')->orderBy('tanker_name')->paginate(20)->withQueryString();

        return view('admin.tanker.in_godown', compact('tankers'));
    }


    public function delete(Request $request)
    {
        $tanker = Tanker::findOrFail($request->id);

        if ($tanker->getConnection()->getSchemaBuilder()->hasColumn('tanker_master', 'isDelete')) {
            $tanker->update(['isDelete' => 1]);
        } else {
            $tanker->delete();
        }

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        if ($request->ids) {
            foreach ($request->ids as $id) {
                $tanker = Tanker::find($id);
                if ($tanker) {
                    if ($tanker->getConnection()->getSchemaBuilder()->hasColumn('tanker_master', 'isDelete')) {
                        $tanker->update(['isDelete' => 1]);
                    } else {
                        $tanker->delete();
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }
     public function names(Request $request)
    {
        $statusParam = $request->query('status'); // 'in' or 'out'
        $statusVal = $statusParam === 'out' ? 1 : 0; // 0 = In Godown, 1 = On Rent (adjust if reversed)

        $items = Tanker::when(isset($statusVal), fn($q) => $q->where('status', $statusVal))
            ->orderBy('tanker_name')
            ->get(['tanker_id','tanker_name','status']);

        $title = $statusVal === 1 ? 'On Rent Tankers' : 'In Godown Tankers';

        return response()->json([
            'title' => $title,
            'items' => $items->map(fn($t) => [
                'id'   => $t->tanker_id,
                'name' => $t->tanker_name,
            ])->values()
        ]);
    }
}
