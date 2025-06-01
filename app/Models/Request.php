<?php
// app/Models/Request.php  
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'type', 'status', 
        'user_id', 'department_id', 'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function overtimeRecord()
{
    return $this->hasOne(OvertimeRecord::class);
}
}