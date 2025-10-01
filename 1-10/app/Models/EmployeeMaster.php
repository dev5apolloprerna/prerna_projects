<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeMaster extends Model
{
    protected $table      = 'employee_master';
    protected $primaryKey = 'emp_id';
    public $timestamps    = true; // uses created_at/updated_at

    protected $fillable = [
        'name',
        'designation',
        'mobile',
        'address',
        'iStatus',
    ];
    
}
