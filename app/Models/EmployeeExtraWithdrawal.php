<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExtraWithdrawal extends Model
{
    use HasFactory;

    protected $table = 'employee_extra_withdrawal';
    protected $primaryKey = 'withdrawal_id';

    protected $fillable = [
        'emp_id',
        'withdrawal_date',
        'amount',
        'reason',
        'emi_amount',
        'remaining_amount',
        'remarks',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeMaster::class, 'emp_id', 'emp_id');
    }
}
