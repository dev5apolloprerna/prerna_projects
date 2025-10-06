<?php
// app/Models/EmpAttendance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpAttendance extends Model
{
    protected $table = 'emp_attendance_master';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;

    protected $fillable = [
        'emp_id', 'attendance_date', 'leave_reason','status', 'enter_by', 'iStatus', 'isDelete'
    ];
     public function employee()
    {
        // Adjust table/columns if your Employee model differs
        return $this->belongsTo(EmployeeMaster::class, 'emp_id', 'emp_id');
    }
}
