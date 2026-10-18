<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Application extends Model
{
    protected $fillable = ['reference', 'user_id', 'service_id', 'officer_id', 'status', 'urgency', 'data', 'remarks',
        'fee_amount', 'paid_at', 'decided_at', 'certificate_code'];

    public const STATUSES = [
        'submitted' => ['Submitted', 'blue'],
        'under_review' => ['Under Review', 'amber'],
        'revision_requested' => ['Revision Requested', 'orange'],
        'pending_payment' => ['Pending Payment', 'purple'],
        'approved' => ['Approved', 'green'],
        'rejected' => ['Rejected', 'red'],
    ];

    protected function casts(): array
    {
        return ['data' => 'array', 'paid_at' => 'datetime', 'decided_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function officer() { return $this->belongsTo(User::class, 'officer_id'); }
    public function service() { return $this->belongsTo(Service::class); }
    public function files() { return $this->hasMany(ApplicationFile::class); }
    public function logs() { return $this->hasMany(AuditLog::class)->orderBy('id'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status][1] ?? 'blue'; }

    public function log(string $action, ?string $from, ?string $to, ?int $userId, ?string $comment = null): void
    {
        $this->logs()->create([
            'action' => $action, 'from_status' => $from, 'to_status' => $to,
            'user_id' => $userId, 'comment' => $comment,
        ]);
    }

    /** Final approval: stamps decision time and issues a verifiable certificate code. */
    public function markApproved(): void
    {
        $this->status = 'approved';
        $this->decided_at = now();
        $this->certificate_code = $this->certificate_code ?: strtoupper(Str::random(12));
        $this->save();
    }
}
