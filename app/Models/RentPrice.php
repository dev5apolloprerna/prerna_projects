<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentPrice extends Model
{
    protected $table = 'rent_prices';
    protected $primaryKey = 'rent_price_id';
    public $timestamps = true;

    protected $fillable = ['rent_type', 'amount', 'iStatus', 'isDelete'];

    protected $casts = [
        'amount'   => 'float',
        'iStatus'  => 'integer',
        'isDelete' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('iStatus', 1)->where('isDelete', 0);
    }

    public static function amountFor(string $rentType): ?float
    {
        return optional(static::active()->where('rent_type', $rentType)->first())->amount;
    }
}
