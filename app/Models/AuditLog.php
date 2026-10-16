<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'before_data',
        'after_data',
        'reason',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convenient static helper to record audit trail
     */
    public static function record(string $action, $entity = null, ?array $before = null, ?array $after = null, ?string $reason = null): self
    {
        $entityType = null;
        $entityId = null;

        if ($entity instanceof Model) {
            $entityType = get_class($entity);
            $entityId = $entity->getKey();
        } elseif (is_string($entity)) {
            $entityType = $entity;
        }

        return self::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_data' => $before,
            'after_data' => $after,
            'reason' => $reason,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
