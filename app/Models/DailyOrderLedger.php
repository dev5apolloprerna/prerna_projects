<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyOrderLedger extends Model
{
    protected $table = 'daily_order_ledger';
    protected $primaryKey = 'ledger_id';
    public $timestamps = true; // created_at / updated_at

    protected $fillable = [
        'customer_id',
        'daily_order_id',
        'entry_date',
        'comment',
        'debit_bl',
        'credit_bl',
        'closing_bl',
        'iStatus',
        'isDelete',
    ];

    protected $casts = [
        'debit_bl'   => 'decimal:2',
        'credit_bl'  => 'decimal:2',
        'closing_bl' => 'decimal:2',
        'entry_date' => 'date',
    ];
}
