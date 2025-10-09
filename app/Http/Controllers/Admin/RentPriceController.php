<?php
// app/Http/Controllers/Admin/RentPriceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentPrice;
use Illuminate\Http\Request;

class RentPriceController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        $rentPrices = RentPrice::when($q, function ($query) use ($q) {
                $query->where('rent_type', 'like', "%{$q}%");
            })
            ->orderBy('rent_price_id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.rent_prices.index', compact('rentPrices', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rent_type' => ['required', 'string', 'max:100'],
            'amount'    => ['required', 'numeric', 'min:0'],
            'iStatus'   => ['required', 'in:0,1'],
        ]);

        RentPrice::create($data + ['isDelete' => 0]);

        return redirect()
            ->route('rent-prices.index')
            ->with('success', 'Rent price added.');
    }

    public function update(Request $request, RentPrice $rent_price)
    {
        $data = $request->validate([
            'rent_type' => ['required', 'string', 'max:100'],
            'amount'    => ['required', 'numeric', 'min:0'],
            'iStatus'   => ['required', 'in:0,1'],
        ]);

        $rent_price->update($data);

        return redirect()
            ->route('rent-prices.index')
            ->with('success', 'Rent price updated.');
    }

    public function destroy(RentPrice $rent_price)
    {
        // hard delete (since table uses MyISAM). If you want soft-delete style, just set isDelete=1 instead.
        $rent_price->delete();

        return redirect()
            ->route('rent-prices.index')
            ->with('success', 'Rent price deleted.');
    }
    public function getRentPrice(Request $request)
    {
        $rentType = (string) $request->query('rent_type', '');
        if ($rentType === '') {
            return response()->json(['ok' => false, 'message' => 'rent_type required'], 422);
        }

        $amount = RentPrice::where('iStatus',1)
                  ->where('isDelete',0)
                  ->where('rent_type',$rentType)
                  ->value('amount');

        if ($amount === null) {
            return response()->json(['ok' => false, 'message' => 'No price found'], 404);
        }

        return response()->json(['ok' => true, 'amount' => (float)$amount]);
    }
}
