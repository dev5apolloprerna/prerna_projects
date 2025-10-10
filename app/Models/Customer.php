<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer_master';
    protected $primaryKey = 'customer_id';
    public $timestamps = true; // uses created_at / updated_at

    protected $fillable = [
        'customer_name',
        'customer_mobile',
        'customer_email',
        'customer_address',
        'customer_type',
        'iStatus',
        'isDelete',
    ];
}
