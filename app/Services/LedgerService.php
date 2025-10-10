<?php

namespace App\Services;

use App\Models\DailyOrder;
use App\Models\DailyOrderLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class LedgerService
{
    /**
     * Add a DEBIT entry for an order (customer owes money).
     */
    public function addDebitForOrder(DailyOrder $order, string $comment = 'Order debit'): DailyOrderLedger
    {
        return $this->append($order->customer_id, [
            'daily_order_id' => $order->daily_order_id,
            'entry_date'     => Carbon::parse($order->rent_date)->toDateString(),
            'comment'        => $comment,
            'debit_bl'       => number_format($order->amount, 2, '.', ''),
            'credit_bl'      => 0,
            'iStatus'        => 1,
            'isDelete'       => 0,
        ]);
    }

    /**
     * Add a CREDIT entry (payment received).
     */
    public function addCreditPayment(
        int $customerId,
        float $amount,
        string $comment = 'Payment received',
        ?string $entryDate = null,
        ?int $dailyOrderId = null
    ): DailyOrderLedger {
        return $this->append($customerId, [
            'daily_order_id' => $dailyOrderId,
            'entry_date'     => $entryDate ? Carbon::parse($entryDate)->toDateString() : now()->toDateString(),
            'comment'        => $comment,
            'debit_bl'       => 0,
            'credit_bl'      => number_format($amount, 2, '.', ''),
            'iStatus'        => 1,
            'isDelete'       => 0,
        ]);
    }

    /**
     * Adjust by delta (used when order amount changes).
     * Positive delta -> more DEBIT; Negative delta -> CREDIT (reduce liability).
     */
    public function adjustForOrderDelta(DailyOrder $order, int $delta): ?DailyOrderLedger
    {
        if ($delta === 0) return null;

        if ($delta > 0) {
            return $this->append($order->customer_id, [
                'daily_order_id' => $order->daily_order_id,
                'entry_date'     => now()->toDateString(),
                'comment'        => 'Order amount increased',
                'debit_bl'       => number_format($delta, 2, '.', ''),
                'credit_bl'      => 0,
                'iStatus'        => 1,
                'isDelete'       => 0,
            ]);
        }

        // delta < 0 -> credit
        return $this->append($order->customer_id, [
            'daily_order_id' => $order->daily_order_id,
            'entry_date'     => now()->toDateString(),
            'comment'        => 'Order amount decreased',
            'debit_bl'       => 0,
            'credit_bl'      => number_format(abs($delta), 2, '.', ''),
            'iStatus'        => 1,
            'isDelete'       => 0,
        ]);
    }

    /**
     * Mark order reversal on delete (credit back full amount).
     */
    public function reverseOrder(DailyOrder $order, string $comment = 'Order reversed'): DailyOrderLedger
    {
        return $this->append($order->customer_id, [
            'daily_order_id' => $order->daily_order_id,
            'entry_date'     => now()->toDateString(),
            'comment'        => $comment,
            'debit_bl'       => 0,
            'credit_bl'      => number_format($order->amount, 2, '.', ''),
            'iStatus'        => 1,
            'isDelete'       => 0,
        ]);
    }

    /**
     * Core appender with running balance (uses SELECT ... FOR UPDATE).
     */
    protected function append(int $customerId, array $payload): DailyOrderLedger
    {
        return DB::transaction(function () use ($customerId, $payload) {
            // Lock the last row for this customer to compute a safe running balance
            $last = DailyOrderLedger::where('customer_id', $customerId)
                ->orderByDesc('entry_date')
                ->orderByDesc('ledger_id')
                ->lockForUpdate()
                ->first();

            $prevClosing = $last ? (float)$last->closing_bl : 0.0;

            $debit  = (float)($payload['debit_bl']  ?? 0);
            $credit = (float)($payload['credit_bl'] ?? 0);

            $closing = $prevClosing + $debit - $credit;

            $row = new DailyOrderLedger(array_merge($payload, [
                'customer_id' => $customerId,
                'closing_bl'  => number_format($closing, 2, '.', ''),
            ]));

            $row->save();

            return $row;
        });
    }
}
