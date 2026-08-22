<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_RESTORED = 'restored';
    public const EVENT_FORCE_DELETED = 'force_deleted';
    public const EVENT_LOGIN = 'login';
    public const EVENT_LOGOUT = 'logout';
    public const EVENT_VERIFIED = 'verified';
    public const EVENT_CONVERTED = 'converted';
    public const EVENT_CUSTOM = 'custom';

    protected $fillable = [
        'user_id',
        'action',
        'event',
        'description',
        'auditable_type',
        'auditable_id',
        'old_value',
        'new_value',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Primary universal logging method.
     */
    public static function logActivity(
        string $description,
        ?Model $auditable = null,
        string $event = self::EVENT_CUSTOM,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?array $properties = null,
        ?User $user = null,
        ?string $action = null
    ): self {
        $ip = '127.0.0.1';
        $userAgent = null;

        try {
            if (function_exists('request') && request()) {
                $ip = request()->ip() ?: '127.0.0.1';
                $userAgent = request()->userAgent();
            }
        } catch (\Throwable $e) {
            // CLI or background context
        }

        return self::create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action ?: $event,
            'event' => $event,
            'description' => $description,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'properties' => $properties,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    /**
     * Backward-compatible logging helper.
     */
    public static function log(string $action, Model $auditable, ?array $oldValue = null, ?array $newValue = null, ?User $user = null): self
    {
        $description = ucwords(str_replace('_', ' ', $action)) . ' on ' . class_basename($auditable) . ' #' . $auditable->getKey();

        return self::logActivity(
            description: $description,
            auditable: $auditable,
            event: $action,
            oldValue: $oldValue,
            newValue: $newValue,
            user: $user,
            action: $action
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a clean, human-readable name of the subject.
     */
    public function getSubjectNameAttribute(): string
    {
        if (!$this->auditable_type) {
            return 'System / Auth';
        }

        $baseType = class_basename($this->auditable_type);
        $id = $this->auditable_id;

        if ($this->auditable) {
            $record = $this->auditable;
            if (isset($record->name)) {
                return "{$baseType}: {$record->name}";
            }
            if (isset($record->canonical_name)) {
                return "{$baseType}: {$record->canonical_name}";
            }
            if (isset($record->quotation_number)) {
                return "{$baseType}: {$record->quotation_number}";
            }
            if (isset($record->po_number)) {
                return "{$baseType}: {$record->po_number}";
            }
            if (isset($record->document_number)) {
                return "{$baseType}: {$record->document_number}";
            }
            if (isset($record->dr_number)) {
                return "{$baseType}: {$record->dr_number}";
            }
            if (isset($record->invoice_number)) {
                return "{$baseType}: {$record->invoice_number}";
            }
            if (isset($record->transaction_code)) {
                return "{$baseType}: {$record->transaction_code}";
            }
        }

        return "{$baseType} #{$id}";
    }
}
