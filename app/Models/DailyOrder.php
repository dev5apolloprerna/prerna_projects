<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyOrder extends Model
{
    protected $table = 'daily_order';
    protected $primaryKey = 'daily_order_id';

    protected $fillable = [
        'customer_id','customer_name','mobile','location',
        'rent_date','service_type','amount','iStatus','isDelete'
    ];

    protected $casts = [
        'rent_date' => 'date',
        'iStatus'   => 'integer',
        'isDelete'  => 'integer',
        'amount'    => 'integer',
    ];

    public function ledgers() {
        return $this->hasMany(DailyOrderLedger::class, 'daily_order_id', 'daily_order_id');
    }
}
