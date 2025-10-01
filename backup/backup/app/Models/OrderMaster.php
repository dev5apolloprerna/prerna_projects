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
    // 1) Resolve rate
    $rate = (int) ($this->rent_amount ?? 0);
    if ($rate <= 0) {
        $rate = (int) (RentPrice::where('rent_type', $this->rent_type)->value('amount') ?? 0);
    }
    $rate = max(0, $rate);

    // 2) DAILY vs MONTHLY
    $rtype   = strtolower(trim((string) $this->rent_type));
    $isDaily = preg_match('/\b(daily|per[\s\-_]?day|daywise|day\s*wise|day\-wise)\b/', $rtype) === 1
               || in_array($rtype, ['day','per day'], true);

    // 3) Date window (INCLUSIVE)
    $nowOrAsOf = ($asOf ?? now());
    $startDate = $this->rent_start_date
        ? Carbon::parse($this->rent_start_date)->startOfDay()
        : $nowOrAsOf->copy()->startOfDay();

    $endDate = $this->received_at
        ? Carbon::parse($this->received_at)->endOfDay()
        : $nowOrAsOf->copy()->endOfDay();

    if ($endDate->lt($startDate)) {
        $endDate = $startDate->copy()->endOfDay();
    }

    // Inclusive days: e.g. 24-09 → 30-09 = 7
    $daysInclusive = $startDate->diffInDays($endDate) + 1;

    // 4) Paid sum
    $paidSumAttr = 'payment_master_sum_paid_amount';
    $paidSum = (int) ($this->{$paidSumAttr} ?? $this->paymentMaster()->sum('paid_amount'));

    // 5) Totals
    if ($isDaily) {
        // DAILY: charge per inclusive day
        $daysUsed  = $daysInclusive;           // e.g., 7
        $base      = $rate;                    // Day 1
        $extraDays = max(0, $daysUsed - 1);    // e.g., 6
        $extra     = $rate * $extraDays;       // e.g., 6 * rate
        $total     = $rate * $daysUsed;        // e.g., 7 * rate
        $months    = 0;
    } else {
        // MONTHLY: no proration — months = ceil(days/30), min 1
        // e.g., 39 days -> ceil(39/30) = 2 months
        $months    = max(1, (int) ceil($daysInclusive / 30));
        $daysUsed  = $daysInclusive;           // info
        $base      = $rate;                    // first month
        $extraDays = max(0, $daysInclusive - 30); // info only
        $extra     = $rate * max(0, $months - 1);
        $total     = $rate * $months;
    }

    $unpaid = max(0, $total - $paidSum);

    return [
        'rent_basis' => $isDaily ? 'daily' : 'monthly',
        'base'       => $base,
        'extra'      => $extra,
        'total_due'  => $total,
        'paid_sum'   => $paidSum,
        'unpaid'     => $unpaid,
        'extra_days' => $extraDays,             // daily: extra chargeable days; monthly: >30d info
        'days_used'  => $daysUsed,              // always the inclusive calendar-day span
        'months'     => $isDaily ? 0 : $months, // monthly blocks we charge
    ];
}





        
}
