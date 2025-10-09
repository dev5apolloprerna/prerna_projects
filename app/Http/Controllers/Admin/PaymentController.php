<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderMaster;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id'    => ['required','integer','exists:order_master,order_id'],
            'paid_amount' => ['required','numeric','min:1'],
            'payment_date' => ['required','date'],
            'payment_received_by' => ['required','integer','exists:payment_received_user,received_id'], // if linked
        ]);

        /** @var OrderMaster $order */
        $order = OrderMaster::findOrFail($data['order_id']);
        $snap  = $order->dueSnapshot(); // base (from rent_amount/fallback) + extra

        $newPaid      = (int) $data['paid_amount'];
        $unpaidBefore = (int) $snap['unpaid'];

        if ($newPaid > $unpaidBefore) {
            return back()->with('error', 'Paid amount cannot exceed current unpaid.');
        }

        return DB::transaction(function () use ($order, $snap, $newPaid, $request) {
            $newUnpaid = $snap['unpaid'] - $newPaid;

            OrderPayment::create([
                'customer_id'         => $order->customer_id,
                'order_id'            => $order->order_id,
                'total_amount'        => $snap['total_due'], // snapshot total at payment time
                'paid_amount'         => $newPaid,
                'unpaid_amount'       => $newUnpaid,
                'payment_date'        => $request->payment_date,
                'payment_received_by' => $request->payment_received_by,
                'iStatus'             => 1,
                'isDelete'            => 0,
            ]);

            return back()->with('success', 'Payment recorded.');
        });
    }

    public function history($orderId)
    {
        $order = OrderMaster::findOrFail($orderId);

        $payments = OrderPayment::where('order_id', $orderId)
            ->orderBy('payment_id', 'asc')
            ->get();

        $snap = $order->dueSnapshot(); // base, extra, total_due, paid_sum, unpaid, extra_days

        return view('admin.payments._history', compact('order', 'payments', 'snap'));
    }
}
