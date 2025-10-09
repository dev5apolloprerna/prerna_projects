<?php
// app/Models/EmpSalary.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpSalary extends Model
{
    protected $table = 'emp_salary';
    protected $primaryKey = 'emp_salary_id';
    public $timestamps = false; // DB handles timestamps

    protected $fillable = [
        'emp_id', 'salary_date', 'last_date','daily_wages','salary_amount','mobile_recharge', 'iStatus', 'isDelete'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeMaster::class, 'emp_id', 'emp_id');
    }
}
