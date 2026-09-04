<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            if (static::shouldLogActivity('created')) {
                $description = static::buildCreatedSummary($model);
                $cleanAttributes = static::extractMeaningfulAttributes($model);

                AuditLog::logActivity(
                    description: $description,
                    auditable: $model,
                    event: AuditLog::EVENT_CREATED,
                    oldValue: null,
                    newValue: $cleanAttributes,
                    action: 'created'
                );
            }
        });

        static::updated(function (Model $model) {
            if (static::shouldLogActivity('updated')) {
                $dirty = $model->getDirty();
                $ignored = array_merge($model->getHidden(), [
                    'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'
                ]);
                $filteredDirty = array_diff_key($dirty, array_flip($ignored));

                // Only log if actual values changed
                $changes = [];
                $original = [];
                foreach ($filteredDirty as $key => $newVal) {
                    $oldVal = $model->getOriginal($key);
                    if ($oldVal != $newVal) {
                        $original[$key] = $oldVal;
                        $changes[$key] = $newVal;
                    }
                }

                if (!empty($changes)) {
                    $description = static::buildUpdatedSummary($model, $original, $changes);

                    AuditLog::logActivity(
                        description: $description,
                        auditable: $model,
                        event: AuditLog::EVENT_UPDATED,
                        oldValue: static::sanitizeAuditAttributes($original),
                        newValue: static::sanitizeAuditAttributes($changes),
                        action: 'updated'
                    );
                }
            }
        });

        static::deleted(function (Model $model) {
            if (static::shouldLogActivity('deleted')) {
                $isForce = method_exists($model, 'isForceDeleting') && $model->isForceDeleting();
                $event = $isForce ? AuditLog::EVENT_FORCE_DELETED : AuditLog::EVENT_DELETED;
                $actionName = $isForce ? 'Permanently deleted' : 'Deleted';
                $description = "{$actionName} " . class_basename($model) . ' ' . static::getActivitySubjectIdentifier($model);

                AuditLog::logActivity(
                    description: $description,
                    auditable: $model,
                    event: $event,
                    oldValue: static::extractMeaningfulAttributes($model),
                    newValue: null,
                    action: $event
                );
            }
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                if (static::shouldLogActivity('restored')) {
                    $description = 'Restored ' . class_basename($model) . ' ' . static::getActivitySubjectIdentifier($model);
                    AuditLog::logActivity(
                        description: $description,
                        auditable: $model,
                        event: AuditLog::EVENT_RESTORED,
                        oldValue: null,
                        newValue: static::extractMeaningfulAttributes($model),
                        action: 'restored'
                    );
                }
            });
        }
    }

    protected static function shouldLogActivity(string $event): bool
    {
        // Restrict logging strictly to commercial transaction models to prevent database bloat
        $allowedTransactionModels = [
            \App\Models\Transaction::class,
            \App\Models\PurchaseOrder::class,
            \App\Models\Quotation::class,
            \App\Models\SalesInvoice::class,
            \App\Models\DeliveryReceipt::class,
        ];

        return in_array(static::class, $allowedTransactionModels, true);
    }

    protected static function getActivitySubjectIdentifier(Model $model): string
    {
        if (isset($model->quotation_number)) {
            return "#{$model->quotation_number}";
        }
        if (isset($model->po_number)) {
            return "#{$model->po_number}";
        }
        if (isset($model->dr_number)) {
            return "#{$model->dr_number}";
        }
        if (isset($model->si_number)) {
            return "#{$model->si_number}";
        }
        if (isset($model->invoice_number)) {
            return "#{$model->invoice_number}";
        }
        if (isset($model->document_number)) {
            return "#{$model->document_number}";
        }
        if (isset($model->name)) {
            return "\"{$model->name}\"";
        }
        if (isset($model->canonical_name)) {
            return "\"{$model->canonical_name}\"";
        }
        if (isset($model->transaction_code)) {
            return "#{$model->transaction_code}";
        }
        if ($model instanceof \App\Models\InventoryTransaction) {
            $prodName = $model->inventoryItem?->product?->canonical_name ?? "Item #{$model->inventory_item_id}";
            return "({$model->transaction_type}: {$model->quantity} {$prodName})";
        }

        return "#{$model->getKey()}";
    }

    protected static function buildCreatedSummary(Model $model): string
    {
        $base = class_basename($model);
        $ident = static::getActivitySubjectIdentifier($model);

        if (isset($model->total_amount)) {
            $formatted = number_format((float)$model->total_amount, 2);
            return "Created {$base} {$ident} (Total: ₱{$formatted})";
        }
        if (isset($model->order_amount)) {
            $formatted = number_format((float)$model->order_amount, 2);
            return "Created {$base} {$ident} (Amount: ₱{$formatted})";
        }
        if (isset($model->final_amount)) {
            $formatted = number_format((float)$model->final_amount, 2);
            return "Created {$base} {$ident} (Final: ₱{$formatted})";
        }
        if (isset($model->default_price)) {
            $formatted = number_format((float)$model->default_price, 2);
            return "Created {$base} {$ident} at ₱{$formatted}";
        }
        if (isset($model->role)) {
            return "Created user account {$ident} as {$model->role}";
        }

        return "Created {$base} {$ident}";
    }

    protected static function buildUpdatedSummary(Model $model, array $old, array $new): string
    {
        $base = class_basename($model);
        $ident = static::getActivitySubjectIdentifier($model);
        $fieldCount = count($new);

        // Highlight common high-impact business field changes
        $highlights = [];
        if (isset($new['is_completed']) && (bool)$new['is_completed'] !== (bool)($old['is_completed'] ?? false)) {
            $highlights[] = ((bool)$new['is_completed']) ? "Fulfilled & Completed" : "Reopened";
        }
        if (isset($new['is_inventory_deducted']) && (bool)$new['is_inventory_deducted'] !== (bool)($old['is_inventory_deducted'] ?? false)) {
            $highlights[] = ((bool)$new['is_inventory_deducted']) ? "Stock Deducted" : "Stock Restored";
        }
        if (isset($new['status'])) {
            $oldSt = ucwords(str_replace('_', ' ', (string)($old['status'] ?? 'None')));
            $newSt = ucwords(str_replace('_', ' ', (string)$new['status']));
            $highlights[] = "Status: {$oldSt} → {$newSt}";
        }
        if (isset($new['delivery_status'])) {
            $oldSt = ucwords(str_replace('_', ' ', (string)($old['delivery_status'] ?? 'None')));
            $newSt = ucwords(str_replace('_', ' ', (string)$new['delivery_status']));
            $highlights[] = "Delivery: {$oldSt} → {$newSt}";
        }
        if (isset($new['payment_status'])) {
            $oldSt = ucwords(str_replace('_', ' ', (string)($old['payment_status'] ?? 'None')));
            $newSt = ucwords(str_replace('_', ' ', (string)$new['payment_status']));
            $highlights[] = "Payment: {$oldSt} → {$newSt}";
        }
        if (isset($new['order_amount'])) {
            $oldVal = number_format((float)($old['order_amount'] ?? 0), 2);
            $newVal = number_format((float)$new['order_amount'], 2);
            $highlights[] = "PO Amount: ₱{$oldVal} → ₱{$newVal}";
        }
        if (isset($new['final_amount'])) {
            $oldVal = number_format((float)($old['final_amount'] ?? 0), 2);
            $newVal = number_format((float)$new['final_amount'], 2);
            $highlights[] = "Final Amount: ₱{$oldVal} → ₱{$newVal}";
        }
        if (isset($new['total_amount'])) {
            $oldVal = number_format((float)($old['total_amount'] ?? 0), 2);
            $newVal = number_format((float)$new['total_amount'], 2);
            $highlights[] = "Amount: ₱{$oldVal} → ₱{$newVal}";
        }
        if (isset($new['default_price'])) {
            $oldVal = number_format((float)($old['default_price'] ?? 0), 2);
            $newVal = number_format((float)$new['default_price'], 2);
            $highlights[] = "Price: ₱{$oldVal} → ₱{$newVal}";
        }
        if (isset($new['role'])) {
            $highlights[] = "Role: " . ($old['role'] ?? 'None') . " → {$new['role']}";
        }

        if (!empty($highlights)) {
            $extra = ($fieldCount > count($highlights)) ? ' (+' . ($fieldCount - count($highlights)) . ' other fields)' : '';
            return "Updated {$base} {$ident} — " . implode(', ', $highlights) . $extra;
        }

        $fieldNames = implode(', ', array_map(fn($f) => ucwords(str_replace('_', ' ', $f)), array_keys($new)));
        return "Updated {$base} {$ident} ({$fieldNames})";
    }

    protected static function extractMeaningfulAttributes(Model $model): array
    {
        $raw = $model->getAttributes();
        $ignored = array_merge($model->getHidden(), [
            'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'
        ]);

        $filtered = array_diff_key($raw, array_flip($ignored));

        // Strip null or empty string values for cleaner storage
        $meaningful = array_filter($filtered, fn($v) => $v !== null && $v !== '');

        return static::sanitizeAuditAttributes($meaningful);
    }

    protected static function sanitizeAuditAttributes(array $attributes): array
    {
        $sensitive = ['password', 'remember_token', 'token', 'secret'];
        foreach ($sensitive as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = '********';
            }
        }
        return $attributes;
    }
}
