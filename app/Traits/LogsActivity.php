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
                $description = 'Created ' . class_basename($model) . ' ' . static::getActivitySubjectIdentifier($model);
                AuditLog::logActivity(
                    description: $description,
                    auditable: $model,
                    event: AuditLog::EVENT_CREATED,
                    oldValue: null,
                    newValue: static::sanitizeAuditAttributes($model->getAttributes()),
                    action: 'created'
                );
            }
        });

        static::updated(function (Model $model) {
            if (static::shouldLogActivity('updated')) {
                $dirty = $model->getDirty();
                // Filter out timestamps and ignored attributes
                $ignored = array_merge($model->getHidden(), ['updated_at', 'remember_token']);
                $filteredDirty = array_diff_key($dirty, array_flip($ignored));

                if (!empty($filteredDirty)) {
                    $original = array_intersect_key($model->getOriginal(), $filteredDirty);
                    $description = 'Updated ' . class_basename($model) . ' ' . static::getActivitySubjectIdentifier($model) . ' (' . implode(', ', array_keys($filteredDirty)) . ')';
                    
                    AuditLog::logActivity(
                        description: $description,
                        auditable: $model,
                        event: AuditLog::EVENT_UPDATED,
                        oldValue: static::sanitizeAuditAttributes($original),
                        newValue: static::sanitizeAuditAttributes($filteredDirty),
                        action: 'updated'
                    );
                }
            }
        });

        static::deleted(function (Model $model) {
            if (static::shouldLogActivity('deleted')) {
                $event = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                    ? AuditLog::EVENT_FORCE_DELETED
                    : AuditLog::EVENT_DELETED;

                $prefix = $event === AuditLog::EVENT_FORCE_DELETED ? 'Permanently deleted ' : 'Deleted ';
                $description = $prefix . class_basename($model) . ' ' . static::getActivitySubjectIdentifier($model);

                AuditLog::logActivity(
                    description: $description,
                    auditable: $model,
                    event: $event,
                    oldValue: static::sanitizeAuditAttributes($model->getOriginal() ?: $model->getAttributes()),
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
                        newValue: static::sanitizeAuditAttributes($model->getAttributes()),
                        action: 'restored'
                    );
                }
            });
        }
    }

    protected static function shouldLogActivity(string $event): bool
    {
        return true;
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

        return "#{$model->getKey()}";
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
