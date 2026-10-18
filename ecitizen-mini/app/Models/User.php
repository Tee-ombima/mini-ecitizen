<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'national_id', 'phone', 'role', 'department_id', 'otp_code', 'otp_expires_at'];
    protected $hidden = ['password', 'remember_token', 'otp_code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function applications(): HasMany { return $this->hasMany(Application::class); }

    public function home(): string
    {
        return match ($this->role) {
            'admin' => route('admin.dashboard'),
            'officer' => route('officer.queue'),
            default => route('citizen.dashboard'),
        };
    }
}
