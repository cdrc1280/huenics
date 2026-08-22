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
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = UserRole::Admin->value;
    public const ROLE_OPERATIONS_MANAGER = UserRole::OperationsManager->value;
    public const ROLE_SALES_EXECUTIVE = UserRole::SalesExecutive->value;
    public const ROLE_CEO = UserRole::Ceo->value;

    public static function getAvailableRoles(): array
    {
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

    // Permission capabilities
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canVerifyDocuments(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER]);
    }

    public function canConfigureLayouts(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER]);
    }

    public function canManageCatalog(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER]);
    }

    public function canCreateDocuments(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE]);
    }

    public function canEditTransactions(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER]);
    }

    public function canManageQuotations(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE]);
    }

    public function canConvertToPO(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE]);
    }

    public function canViewSalesReports(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_CEO]);
    }

    public function canManageInventory(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER]);
    }

    /**
     * Only Admin, Operations Manager, and Sales Executive can edit quotation documents.
     * CEO gets view-only access. PO documents are always read-only regardless.
     */
    public function canEditQuotationDocument(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_OPERATIONS_MANAGER, self::ROLE_SALES_EXECUTIVE]);
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
