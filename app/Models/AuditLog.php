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
    public const EVENT_DELIVERED = 'delivered';
    public const EVENT_FULFILLED = 'fulfilled';
    public const EVENT_DOCUMENTS_ATTACHED = 'documents_attached';
    public const EVENT_STOCK_DEDUCTED = 'stock_deducted';
    public const EVENT_STOCK_RESTORED = 'stock_restored';
    public const EVENT_STOCK_ADDED = 'stock_added';
    public const EVENT_STOCK_ADJUSTED = 'stock_adjusted';
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
    ): ?self {
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

        try {
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
        } catch (\Throwable $e) {
            // Fallback for legacy / unmigrated schemas
            try {
                return self::create([
                    'user_id' => $user?->id ?? auth()->id(),
                    'action' => $action ?: $event,
                    'auditable_type' => $auditable ? get_class($auditable) : null,
                    'auditable_id' => $auditable?->getKey(),
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'ip_address' => $ip,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $ex) {
                \Illuminate\Support\Facades\Log::warning('AuditLog creation failed: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Backward-compatible logging helper.
     */
    public static function log(string $action, Model $auditable, ?array $oldValue = null, ?array $newValue = null, ?User $user = null): ?self
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
     * Get clean module name label (e.g. 'Quotation', 'Product', 'Security').
     */
    public function getSubjectTypeLabelAttribute(): string
    {
        if (!$this->auditable_type) {
            return 'Authentication';
        }

        $base = class_basename($this->auditable_type);
        return match ($base) {
            'PurchaseOrder' => 'Purchase Order',
            'ProductAlias' => 'Product Alias',
            'DeliveryReceipt' => 'Delivery Receipt',
            'SalesInvoice' => 'Sales Invoice',
            'Transaction' => 'Ledger',
            'InventoryItem' => 'Inventory Item',
            'InventoryTransaction' => 'Inventory Transaction',
            default => $base,
        };
    }

    /**
     * Get clean identifier of the target subject.
     */
    public function getSubjectIdentifierAttribute(): string
    {
        if (!$this->auditable_type) {
            return $this->user ? $this->user->name : 'System';
        }

        if ($this->auditable) {
            $r = $this->auditable;
            if (isset($r->quotation_number)) return "#{$r->quotation_number}";
            if (isset($r->po_number)) return "#{$r->po_number}";
            if (isset($r->dr_number)) return "#{$r->dr_number}";
            if (isset($r->invoice_number)) return "#{$r->invoice_number}";
            if (isset($r->document_number)) return "#{$r->document_number}";
            if (isset($r->canonical_name)) {
                $sku = !empty($r->sku) ? " [{$r->sku}]" : (!empty($r->product_code) ? " [{$r->product_code}]" : '');
                return "{$r->canonical_name}{$sku}";
            }
            if (isset($r->name)) return $r->name;
            if (isset($r->transaction_code)) return "#{$r->transaction_code}";
        }

        return "#{$this->auditable_id}";
    }

    /**
     * Get clean, human-readable name of the subject.
     */
    public function getSubjectNameAttribute(): string
    {
        return "{$this->subject_type_label}: {$this->subject_identifier}";
    }

    /**
     * Simple device / browser parser.
     */
    public function getDeviceSummaryAttribute(): string
    {
        if (!$this->user_agent) {
            return 'API / System';
        }

        $ua = $this->user_agent;
        $platform = 'Unknown OS';
        if (str_contains($ua, 'Windows')) $platform = 'Windows';
        elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) $platform = 'macOS';
        elseif (str_contains($ua, 'Linux')) $platform = 'Linux';
        elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $platform = 'iOS';
        elseif (str_contains($ua, 'Android')) $platform = 'Android';

        $browser = 'Browser';
        if (str_contains($ua, 'Edg/')) $browser = 'Edge';
        elseif (str_contains($ua, 'Chrome/')) $browser = 'Chrome';
        elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome')) $browser = 'Safari';
        elseif (str_contains($ua, 'Firefox/')) $browser = 'Firefox';

        return "{$browser} on {$platform}";
    }
}
