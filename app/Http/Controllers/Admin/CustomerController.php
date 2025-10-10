<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::where('isDelete', 0);

        if ($request->customer_name) {
            $query->where('customer_name', 'LIKE', '%' . $request->customer_name . '%');
        }

        if ($request->customer_mobile) {
            $query->where('customer_mobile', 'LIKE', '%' . $request->customer_mobile . '%');
        }

        $customers = $query->orderBy('customer_id', 'DESC')->paginate(10);

        return view('admin.customer.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customer.add-edit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'customer_mobile' => 'required',
            'customer_email' => 'nullable|email',
            'customer_address' => 'required',
            'customer_type' => 'required',
        ]);

        Customer::create($request->only(['customer_name', 'customer_mobile', 'customer_email', 'customer_address','customer_type']));

        return redirect()->route('customer.index')->with('success', 'Customer added successfully.');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customer.add-edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required',
            'customer_mobile' => 'required',
            'customer_email' => 'nullable|email',
            'customer_address' => 'required',
            'customer_type' => 'required',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->only(['customer_name', 'customer_mobile', 'customer_email', 'customer_address','customer_type']));

        return redirect()->route('customer.index')->with('success', 'Customer updated successfully.');
    }

    public function delete(Request $request)
    {
        $customer = Customer::findOrFail($request->id);

        if ($customer->getConnection()->getSchemaBuilder()->hasColumn('customer_master', 'isDelete')) {
            $customer->update(['isDelete' => 1]);
        } else {
            $customer->delete();
        }

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        if ($request->ids) {
            foreach ($request->ids as $id) {
                $customer = Customer::find($id);
                if ($customer) {
                    if ($customer->getConnection()->getSchemaBuilder()->hasColumn('customer_master', 'isDelete')) {
                        $customer->update(['isDelete' => 1]);
                    } else {
                        $customer->delete();
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
