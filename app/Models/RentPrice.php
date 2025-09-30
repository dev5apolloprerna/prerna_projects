<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentPrice extends Model
{
    protected $table = 'rent_prices';
    protected $primaryKey = 'rent_price_id';
    public $timestamps = false;

    protected $fillable = ['rent_type', 'amount', 'iStatus', 'isDelete'];
}
