<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['application_id', 'user_id', 'action', 'from_status', 'to_status', 'comment'];
    public function user() { return $this->belongsTo(User::class); }
}
