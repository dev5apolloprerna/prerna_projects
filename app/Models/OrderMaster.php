<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderMaster extends Model
{
    protected $table      = 'order_master';
    protected $primaryKey = 'order_id';
    public $timestamps    = true;

    protected $fillable = [
        'order_type',
        'customer_id',
        'tanker_id',
        'rent_type',
        'rent_start_date',
        'advance_amount',
        'rent_amount',
        'reference_name',
        'reference_mobile_no',
        'reference_address',
        'tanker_location',
        'iStatus',
    ];

    protected $casts = [
        'rent_start_date' => 'datetime',
        'received_at'     => 'datetime',
    ];


    // Scope hide soft-deleted rows
    public function scopeNotDeleted($q) { return $q->where('isDelete', 0); }

    // Relations (optional if you want eager loading in listing)
    public function customer() { return $this->belongsTo(Customer::class, 'customer_id', 'customer_id'); }
    public function tanker()   { return $this->belongsTo(Tanker::class, 'tanker_id', 'tanker_id'); }
    public function paymentMaster() { return $this->hasOne(OrderPayment::class, 'order_id', 'order_id'); }

   public function dueSnapshot(?Carbon $asOf = null): array
    {
        // 1) Rate: explicit order.rent_amount; fallback to master price
        $rate = (int) ($this->rent_amount ?? 0);
        if ($rate <= 0) {
            $rate = (int) (\App\Models\RentPrice::where('rent_type', $this->rent_type)->value('amount') ?? 0);
        }
        $rate = max(0, $rate);

        // 2) DAILY vs MONTHLY
        $rtype   = strtolower(trim((string) $this->rent_type));
        $isDaily = preg_match('/\b(daily|per[\s\-_]?day|daywise|day\s*wise|day\-wise)\b/', $rtype) === 1
                   || in_array($rtype, ['day','per day'], true);

        // 3) Start/End window (freeze at received_at if set; otherwise use "now" or caller's asOf)
        $start = $this->rent_start_date ? Carbon::parse($this->rent_start_date) : ($asOf ?? now());
        $end   = $this->received_at      ? Carbon::parse($this->received_at)     : ($asOf ?? now());

        // Guard: if somehow end < start, treat as 1 day
        $daysInclusive = max(1, $start->copy()->startOfDay()->diffInDays($end->copy()->endOfDay()) + 1);

        // 4) Paid sum (prefer eager withSum alias if loaded)
        // withSum('paymentMaster','paid_amount') => attribute: payment_master_sum_paid_amount
        $paidSumAttr = 'payment_master_sum_paid_amount';
        $paidSum = (int) ($this->{$paidSumAttr} ?? $this->paymentMaster()->sum('paid_amount'));

        // 5) Totals
        if ($isDaily) {
            // Base is 1 day; extra is every additional day
            $base      = $rate;
            $extraDays = max(0, $daysInclusive - 1);
            $extra     = $rate * $extraDays;
            $total     = $rate * $daysInclusive;
            $daysUsed  = $daysInclusive;
        } else {
            // Base covers first 30 days; extra is per-day after 30
            $base      = $rate;
            $extraDays = max(0, $daysInclusive - 30);
            $perDay    = (int) ceil($base / 30); // adjust if you store a separate per-day rate
            $extra     = $perDay * $extraDays;
            $total     = $base + $extra;
            $daysUsed  = $daysInclusive; // optional info
        }

        $unpaid = max(0, $total - $paidSum);

        return [
            'rent_basis' => $isDaily ? 'daily' : 'monthly',
            'base'       => $base,
            'extra'      => $extra,
            'total_due'  => $total,
            'paid_sum'   => $paidSum,
            'unpaid'     => $unpaid,
            'extra_days' => $extraDays,
            'days_used'  => $daysUsed,
        ];
    }

        
}
