<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorMaster;
use Illuminate\Http\Request;

class VendorMasterController extends Controller
{
    // Listing with search & pagination
    public function index(Request $request)
    {
        $q = VendorMaster::query();

        // search across multiple columns
        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function($x) use ($s) {
                $x->where('vendor_name', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('mobile', 'like', "%{$s}%");
            });
        }

        // optional filter: ?status=1/0
        if ($request->filled('status') && in_array($request->status, ['0','1'])) {
            $q->where('iStatus', (int)$request->status);
        }

        $vendors = $q->orderByDesc('vendor_id')->paginate(10)->withQueryString();
        return view('admin.vendor.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendor.add-edit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_name'   => 'required|string|max:150',
            'contact_person'=> 'nullable|string|max:150',
            'email'         => 'nullable|email|max:150|unique:vendor_master,email',
            'mobile'        => 'nullable|string|max:20|unique:vendor_master,mobile',
            'address'       => 'nullable|string|max:255',
            'gst_number'    => 'nullable|string|max:50',
            'iStatus'       => 'required|in:0,1',
        ]);

        VendorMaster::create([
            'vendor_name'    => $request->vendor_name,
            'contact_person' => $request->contact_person,
            'email'          => $request->email,
            'mobile'         => $request->mobile,
            'address'        => $request->address,
            'gst_number'     => $request->gst_number,
            'iStatus'        => (int)$request->iStatus,
        ]);

        return redirect()->route('vendor.index')->with('success', 'Vendor added successfully.');
    }

    public function edit($id)
    {
        $vendor = VendorMaster::findOrFail($id);
        return view('admin.vendor.add-edit', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $vendor = VendorMaster::findOrFail($id);

        $request->validate([
            'vendor_name'   => 'required|string|max:150',
            'contact_person'=> 'nullable|string|max:150',
            'email'         => 'nullable|email|max:150|unique:vendor_master,email,' . $vendor->vendor_id . ',vendor_id',
            'mobile'        => 'nullable|string|max:20|unique:vendor_master,mobile,' . $vendor->vendor_id . ',vendor_id',
            'address'       => 'nullable|string|max:255',
            'gst_number'    => 'nullable|string|max:50',
            'iStatus'       => 'required|in:0,1',
        ]);

        $vendor->update([
            'vendor_name'    => $request->vendor_name,
            'contact_person' => $request->contact_person,
            'email'          => $request->email,
            'mobile'         => $request->mobile,
            'address'        => $request->address,
            'gst_number'     => $request->gst_number,
            'iStatus'        => (int)$request->iStatus,
        ]);

        return redirect()->route('vendor.index')->with('success', 'Vendor updated successfully.');
    }

    // HARD delete (unique indexes exist)
    public function destroy($id)
    {
        $vendor = VendorMaster::findOrFail($id);
        $vendor->delete();

        return response()->json(['status' => true, 'message' => 'Vendor deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (count($ids)) {
            VendorMaster::whereIn('vendor_id', $ids)->delete(); // hard delete
        }
        return response()->json(['status' => true, 'message' => 'Selected vendors deleted successfully.']);
    }

    public function changeStatus($id)
    {
        $vendor = VendorMaster::findOrFail($id);
        $vendor->iStatus = $vendor->iStatus ? 0 : 1;
        $vendor->save();

        return response()->json(['status' => true, 'new_status' => $vendor->iStatus]);
    }
}
