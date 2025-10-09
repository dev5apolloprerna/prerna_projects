<?php
// app/Models/PaymentReceivedUser.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReceivedUser extends Model
{
    use HasFactory;

    protected $table = 'payment_received_user';
    protected $primaryKey = 'received_id';

    protected $fillable = [
        'name',
        'iStatus',
        'isDelete'
    ];

    // Scope to exclude deleted records
    public function scopeNotDeleted($q)
    {
        return $q->where('isDelete', 0);
    }
}
