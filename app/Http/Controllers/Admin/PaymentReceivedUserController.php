<?php
// app/Http/Controllers/Admin/PaymentReceivedUserController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentReceivedUser;
use Illuminate\Validation\Rule;

class PaymentReceivedUserController extends Controller
{
    public function index(Request $request)
    {
        $q = PaymentReceivedUser::notDeleted();

        if ($request->filled('search')) {
            $q->where('name', 'like', "%{$request->search}%");
        }

        $users = $q->orderByDesc('received_id')->paginate(10)->withQueryString();
        return view('admin.payment_received_user.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:payment_received_user,name,NULL,received_id,isDelete,0'
        ]);

        PaymentReceivedUser::create([
            'name' => $validated['name'],
            'iStatus' => $request->iStatus ?? 0
        ]);

        return back()->with('success', 'Payment received user added successfully.');
    }

    public function edit($id)
    {
        $data = PaymentReceivedUser::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $user = PaymentReceivedUser::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required','string','max:100',
                Rule::unique('payment_received_user', 'name')->ignore($user->received_id, 'received_id')->where(fn($q)=>$q->where('isDelete',0))
            ],
        ]);

        $user->update([
            'name' => $validated['name'],
            'iStatus' => $request->iStatus ?? 0,
        ]);

        return back()->with('success', 'Record updated successfully.');
    }

    public function destroy(Request $request)
    {
        $ids = $request->ids ?? [];
        if (count($ids)) {
            PaymentReceivedUser::whereIn('received_id', $ids)->update(['isDelete' => 1]);
        }
        return back()->with('success', 'Selected records deleted.');
    }

    public function toggleStatus($id)
    {
        $user = PaymentReceivedUser::findOrFail($id);
        $user->iStatus = $user->iStatus ? 0 : 1;
        $user->save();
        return back()->with('success', 'Status updated.');
    }
}
