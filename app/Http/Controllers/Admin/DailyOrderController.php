<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\DailyOrder;
use App\Models\Customer;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // top of file

class DailyOrderController extends Controller
{

    public function __construct(private LedgerService $ledger) {}


        public function index(Request $request)
        {
            $customers = Customer::select('customer_id','customer_name','customer_mobile')
                ->orderBy('customer_name')
                ->get();

            // Add a subquery "paid_sum" = sum of credits tied to this order
            $rows = DailyOrder::query()
                ->where('isDelete', 0)
                ->select('daily_order.*')
                ->selectSub(function($q){
                    $q->from('daily_order_ledger as l')
                      ->selectRaw('COALESCE(SUM(l.credit_bl),0)')
                      ->whereColumn('l.daily_order_id','daily_order.daily_order_id')
                      ->where('l.isDelete', 0)
                      ->where('l.iStatus', 1);
                }, 'paid_sum')
                ->orderByDesc('daily_order_id')
                ->paginate(12);

            // Page totals (for the rows shown on current page)
            $coll = $rows->getCollection();
            $page_total_amount = (float) $coll->sum('amount');
            $page_total_paid   = (float) $coll->sum('paid_sum');
            $page_total_due    = $page_total_amount - $page_total_paid;

            return view('admin.daily_orders.index', compact(
                'customers', 'rows',
                'page_total_amount', 'page_total_paid', 'page_total_due'
            ));
        }

    public function create() {
        $customers = Customer::orderBy('customer_name')->get(['customer_id','customer_name','customer_mobile']);
        $recent = DailyOrder::latest('rent_date')->limit(8)->get();
        return view('admin.daily_orders.add-edit', compact('customers','recent'));
    }

    public function edit($id) {
        $order = DailyOrder::findOrFail($id);
        $customers = Customer::orderBy('customer_name')->get(['customer_id','customer_name','customer_mobile']);
        $recent = DailyOrder::latest('rent_date')->limit(8)->get();
        return view('admin.daily_orders.add-edit', compact('order','customers','recent'));
    }

 public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        if ($validated['customer_type'] === 'retail') {
            $customerId   = 0;
            $customerName = $validated['customer_name'];
            $mobile       = $validated['mobile'];
        } else {
            $customerId = (int) $request->input('customer_id');
            $cust       = Customer::find($customerId);
            $customerName = $request->input('customer_name') ?: ($cust->customer_name ?? '');
            $mobile       = $request->input('mobile') ?: ($cust->customer_mobile ?? '');
        }

        DB::transaction(function () use ($request, $customerId, $customerName, $mobile) {
            $order = DailyOrder::create([
                'customer_id'   => $customerId,
                'customer_name' => $customerName,
                'mobile'        => $mobile,
                'location'      => $request->input('location'),
                'rent_date'     => $request->input('rent_date'),
                'service_type'  => $request->input('service_type'),
                'amount'        => (float)$request->input('amount'),
                'iStatus'       => (int)$request->input('iStatus', 1),
                'isDelete'      => 0,
            ]);

            // Ledger: add a DEBIT for this order
            $this->ledger->addDebitForOrder($order, 'Order debit');
        });

        return redirect()->route('daily-orders.index')->with('success','Order saved.');
    }

    public function update(Request $request, DailyOrder $daily_order)
    {
        $validated = $this->validatePayload($request);

        if ($validated['customer_type'] === 'retail') {
            $customerId   = 0;
            $customerName = $validated['customer_name'];
            $mobile       = $validated['mobile'];
        } else {
            $customerId = (int) $request->input('customer_id');
            $cust       = Customer::find($customerId);
            $customerName = $request->input('customer_name') ?: ($cust->customer_name ?? '');
            $mobile       = $request->input('mobile') ?: ($cust->customer_mobile ?? '');
        }

        DB::transaction(function () use ($request, $daily_order, $customerId, $customerName, $mobile) {
            $oldAmount = (float) $daily_order->amount;
            $newAmount = (float) $request->input('amount');

            $daily_order->update([
                'customer_id'   => $customerId,
                'customer_name' => $customerName,
                'mobile'        => $mobile,
                'location'      => $request->input('location'),
                'rent_date'     => $request->input('rent_date'),
                'service_type'  => $request->input('service_type'),
                'amount'        => $newAmount,
                'iStatus'       => (int) $request->input('iStatus', 1),
            ]);

            // Ledger: adjust by delta if amount changed
            $delta = $newAmount - $oldAmount; // +ve => extra DEBIT; -ve => CREDIT
            if (abs($delta) > 0.0001) {
                // NOTE: Your LedgerService adjustForOrderDelta signature is int $delta.
                // If you want paise precision, change it to float in the service.
                $this->ledger->adjustForOrderDelta($daily_order, (int) round($delta));
            }
        });

        return redirect()->route('daily-orders.index')->with('success','Order updated.');
    }

    public function destroy(DailyOrder $daily_order)
    {
        DB::transaction(function () use ($daily_order) {
            // Ledger: reverse full order amount as CREDIT
            $this->ledger->reverseOrder($daily_order, 'Order reversed');
            $daily_order->update(['isDelete' => 1]);
        });

        return redirect()->route('daily-orders.index')->with('success','Order deleted.');
    }

    /**
     * NEW: Receive a payment (credit) against an order (partial or full).
     */
    public function receivePayment(Request $request, DailyOrder $daily_order)
    {
        $request->validate([
            'amount'     => ['required','numeric','min:0.01'],
            'entry_date' => ['nullable','date'],
            'comment'    => ['nullable','string','max:255'],
        ]);

        $amount  = (float) $request->input('amount');
        $comment = $request->input('comment', 'Payment received');
        $date    = $request->input('entry_date'); // nullable -> defaults to today in service

        $this->ledger->addCreditPayment(
            customerId:   (int) $daily_order->customer_id,
            amount:       $amount,
            comment:      $comment ?: 'Payment received',
            entryDate:    $date,
            dailyOrderId: $daily_order->daily_order_id
        );

        return redirect()->route('daily-orders.index')->with('success','Payment recorded.');
    }

    protected function validatePayload(Request $request): array
    {
        $rules = [
            'customer_type' => ['required', Rule::in(['recurring','retail'])],
            'customer_id'   => ['nullable','integer','required_if:customer_type,recurring'],
            'customer_name' => ['required_if:customer_type,retail','nullable','string','max:100'],
            'mobile'        => ['required_if:customer_type,retail','nullable','regex:/^[0-9]{10,15}$/'],
            'location'      => ['required','string','max:255'],
            'rent_date'     => ['required','date'],
            'service_type'  => ['required','string','max:100'],
            'amount'        => ['required','numeric','min:0'],
            'iStatus'       => ['nullable','integer','in:0,1'],
        ];

        $messages = [
            'customer_id.required_if'   => 'Please select a Recurring customer.',
            'customer_name.required_if' => 'Customer name is required for Retail.',
            'mobile.required_if'        => 'Mobile is required for Retail.',
            'mobile.regex'              => 'Mobile must be 10–15 digits.',
        ];

        return $request->validate($rules, $messages);
    }

}
