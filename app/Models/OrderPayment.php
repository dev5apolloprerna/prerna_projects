<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = 'order_payment_master';
    protected $primaryKey = 'payment_id';
    public $timestamps = true; // uses created_at, updated_at

    protected $fillable = [
        'customer_id', 'order_id', 'total_amount', 'paid_amount', 'unpaid_amount', 'payment_received_by', 'payment_date' 
    ];
public function order()
{
    return $this->belongsTo(OrderMaster::class, 'order_id', 'order_id');
}

}
