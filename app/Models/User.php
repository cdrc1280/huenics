<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes, \App\Traits\LogsActivity;

    public const ROLE_ADMIN = UserRole::Admin->value;
    public const ROLE_OPERATIONS_MANAGER = UserRole::OperationsManager->value;
    public const ROLE_SALES_EXECUTIVE = UserRole::SalesExecutive->value;
    public const ROLE_CEO = UserRole::Ceo->value;

    public static function getAvailableRoles(): array
    {
        try {
            $roles = \App\Models\Role::pluck('name', 'slug')->toArray();
            if (!empty($roles)) {
                return $roles;
            }
        } catch (\Throwable $e) {
            // Fallback to defaults
        }

        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_OPERATIONS_MANAGER => 'Operations Manager',
            self::ROLE_SALES_EXECUTIVE => 'Sales Executive',
            self::ROLE_CEO => 'CEO / Executive',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'is_owner',
        'e_signature_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_owner' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (User $user) {
            if (!empty($user->role)) {
                try {
                    $roleRecord = \App\Models\Role::where('slug', $user->role)->first();
                    if ($roleRecord) {
                        $user->role_id = $roleRecord->id;
                    }
                } catch (\Throwable $e) {
                    // ignore if table not migrated
                }
            } elseif (!empty($user->role_id)) {
                try {
                    $roleRecord = \App\Models\Role::find($user->role_id);
                    if ($roleRecord) {
                        $user->role = $roleRecord->slug;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // Role check helpers
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOperationsManager(): bool
    {
        return $this->role === self::ROLE_OPERATIONS_MANAGER;
    }

    public function isSalesExecutive(): bool
    {
        return $this->role === self::ROLE_SALES_EXECUTIVE;
    }

    public function isCeo(): bool
    {
        return $this->role === self::ROLE_CEO;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function roleRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    /**
     * Check if user has a specific dynamic permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Business owner or Admin has full system access
        if ($this->is_owner || $this->isAdmin()) {
            return true;
        }

        // Check assigned Role relation permissions
        $role = $this->roleRelation;
        if (!$role && $this->role) {
            try {
                $role = \App\Models\Role::where('slug', $this->role)->first();
            } catch (\Throwable $e) {
                $role = null;
            }
        }

        if ($role && $role->relationLoaded('permissions')) {
            return $role->permissions->contains('slug', $permissionSlug);
        } elseif ($role) {
            try {
                return $role->permissions()->where('slug', $permissionSlug)->exists();
            } catch (\Throwable $e) {
                // fall back
            }
        }

        return $this->defaultPermissionCheck($permissionSlug);
    }

    /**
     * Fallback default permissions matrix based on the default core roles.
     */
    protected function defaultPermissionCheck(string $slug): bool
    {
        return match ($slug) {
            'manage_roles', 'manage_roles_permissions' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_CEO]),
            'manage_users' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_CEO, self::ROLE_OPERATIONS_MANAGER]),
            'verify_vendor_documents', 'verify_documents' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]),
            'reconcile_math_vat' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]),
            'configure_vendor_layouts', 'configure_layouts' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]),
            'manage_product_catalog', 'manage_catalog' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]),
            'create_quotes', 'create_documents' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE, self::ROLE_CEO]),
            'edit_transactions' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]),
            'manage_quotations' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE, self::ROLE_CEO]),
            'convert_to_po' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE, self::ROLE_CEO]),
            'select_warranty' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE, self::ROLE_CEO]),
            'track_personal_quota' => true,
            'view_sales_reports', 'view_leaderboards_quotas', 'view_profit_analytics' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]),
            'manage_inventory_stock', 'manage_inventory', 'mark_delivery' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]),
            'edit_quotation_documents' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE, self::ROLE_CEO]),
            'delete_records' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_CEO, self::ROLE_OPERATIONS_MANAGER]),
            'view_audit_trails', 'view_activity_logs' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_CEO, self::ROLE_OPERATIONS_MANAGER]),
            default => false,
        };
    }

    // Permission capabilities
    public function canConfigureRoles(): bool
    {
        return $this->isAdmin() || $this->isCeo() || $this->hasPermission('manage_roles_permissions');
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('manage_users');
    }

    public function canVerifyDocuments(): bool
    {
        return $this->hasPermission('verify_vendor_documents');
    }

    public function canConfigureLayouts(): bool
    {
        return $this->hasPermission('configure_vendor_layouts');
    }

    public function canManageCatalog(): bool
    {
        return $this->hasPermission('manage_product_catalog');
    }

    public function canCreateDocuments(): bool
    {
        return $this->hasPermission('create_quotes');
    }

    public function canEditTransactions(): bool
    {
        return $this->hasPermission('edit_transactions');
    }

    public function canManageQuotations(): bool
    {
        return $this->hasPermission('manage_quotations');
    }

    public function canConvertToPO(): bool
    {
        return $this->hasPermission('convert_to_po');
    }

    public function canViewSalesReports(): bool
    {
        return $this->hasPermission('view_sales_reports');
    }

    public function canManageInventory(): bool
    {
        return $this->hasPermission('manage_inventory_stock');
    }

    /**
     * Admin, CEO, Operations Manager, and Sales Executive can edit quotation documents.
     */
    public function canEditQuotationDocument(): bool
    {
        return $this->hasPermission('edit_quotation_documents');
    }

    public function canDeleteRecords(): bool
    {
        return $this->hasPermission('delete_records');
    }

    public function canViewActivityLogs(): bool
    {
        return $this->hasPermission('view_audit_trails');
    }

    /**
     * Get the URL for the user's e-signature image.
     */
    public function getESignatureUrl(): ?string
    {
        if (!$this->e_signature_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->url($this->e_signature_path);
    }

    /**
     * Get the absolute filesystem path for the user's e-signature image.
     */
    public function getESignatureAbsolutePath(): ?string
    {
        if (!$this->e_signature_path) {
            return null;
        }

        $candidates = [
            storage_path('app/private/' . $this->e_signature_path),
            storage_path('app/' . $this->e_signature_path),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    // Relationships
    public function quotations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Quotation::class, 'sales_agent_id');
    }


    public function purchaseOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PurchaseOrder::class, 'sales_agent_id');
    }

    public function salesQuotas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SalesQuota::class);
    }
}
