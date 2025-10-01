<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorMaster extends Model
{
    protected $table      = 'vendor_master';
    protected $primaryKey = 'vendor_id';
    public $timestamps    = true;

    protected $fillable = [
        'vendor_name',
        'contact_person',
        'email',
        'mobile',
        'address',
        'gst_number',
        'iStatus',
    ];
}
