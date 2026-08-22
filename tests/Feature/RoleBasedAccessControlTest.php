<?php

namespace Tests\Feature;

use App\Filament\Pages\ReviewQueuePage;
use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\QuotationResource;
use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\UserResource;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $opsManager;
    protected User $salesExec;
    protected User $ceo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_ADMIN,
            'is_owner' => true,
        ]);

        $this->opsManager = User::create([
            'name'     => 'Ops Manager',
            'email'    => 'ops@test.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_OPERATIONS_MANAGER,
        ]);

        $this->salesExec = User::create([
            'name'     => 'Sales Exec',
            'email'    => 'sales@test.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_SALES_EXECUTIVE,
        ]);

        $this->ceo = User::create([
            'name'     => 'CEO User',
            'email'    => 'ceo@test.com',
            'password' => bcrypt('password'),
            'role'     => User::ROLE_CEO,
            'is_owner' => true,
        ]);
    }

    public function test_admin_has_full_permissions(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue($this->admin->isAdmin());
        $this->assertTrue($this->admin->canManageUsers());
        $this->assertTrue($this->admin->canVerifyDocuments());
        $this->assertTrue($this->admin->canConfigureLayouts());
        $this->assertTrue($this->admin->canManageCatalog());
        $this->assertTrue($this->admin->canCreateDocuments());
        $this->assertTrue($this->admin->canManageQuotations());
        $this->assertTrue($this->admin->canConvertToPO());
        $this->assertTrue($this->admin->canViewSalesReports());
        $this->assertTrue($this->admin->canManageInventory());
        $this->assertTrue($this->admin->canDeleteRecords());
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(ActivityLogResource::canViewAny());
        $this->assertTrue(ReviewQueuePage::canAccess());
    }

    public function test_operations_manager_permissions(): void
    {
        $this->actingAs($this->opsManager);

        $this->assertTrue($this->opsManager->isOperationsManager());
        $this->assertTrue($this->opsManager->canManageUsers());
        $this->assertTrue($this->opsManager->canVerifyDocuments());
        $this->assertTrue($this->opsManager->canConfigureLayouts());
        $this->assertTrue($this->opsManager->canManageCatalog());
        $this->assertTrue($this->opsManager->canCreateDocuments());
        $this->assertTrue($this->opsManager->canManageQuotations());
        $this->assertTrue($this->opsManager->canConvertToPO());
        $this->assertTrue($this->opsManager->canViewSalesReports());
        $this->assertTrue($this->opsManager->canManageInventory());
        $this->assertTrue($this->opsManager->canDeleteRecords());
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(ActivityLogResource::canViewAny());
        $this->assertTrue(ReviewQueuePage::canAccess());
    }

    public function test_sales_executive_permissions(): void
    {
        $this->actingAs($this->salesExec);

        $this->assertTrue($this->salesExec->isSalesExecutive());
        $this->assertFalse($this->salesExec->canManageUsers());
        $this->assertFalse($this->salesExec->canVerifyDocuments());
        $this->assertFalse($this->salesExec->canConfigureLayouts());
        $this->assertFalse($this->salesExec->canManageCatalog());
        $this->assertTrue($this->salesExec->canCreateDocuments());
        $this->assertTrue($this->salesExec->canManageQuotations());
        $this->assertTrue($this->salesExec->canConvertToPO());
        $this->assertFalse($this->salesExec->canViewSalesReports());
        $this->assertFalse($this->salesExec->canManageInventory());
        $this->assertFalse($this->salesExec->canDeleteRecords());
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(ActivityLogResource::canViewAny());
        $this->assertTrue(ReviewQueuePage::canAccess()); // Sales Executive can view queue in Viewing Mode
    }

    public function test_ceo_permissions(): void
    {
        $this->actingAs($this->ceo);

        $this->assertTrue($this->ceo->isCeo());
        $this->assertTrue($this->ceo->canManageUsers());
        $this->assertTrue($this->ceo->canVerifyDocuments());
        $this->assertTrue($this->ceo->canConfigureLayouts());
        $this->assertTrue($this->ceo->canManageCatalog());
        $this->assertTrue($this->ceo->canCreateDocuments());
        $this->assertTrue($this->ceo->canManageQuotations());
        $this->assertTrue($this->ceo->canConvertToPO());
        $this->assertTrue($this->ceo->canViewSalesReports());
        $this->assertTrue($this->ceo->canManageInventory());
        $this->assertTrue($this->ceo->canEditQuotationDocument());
        $this->assertTrue($this->ceo->canDeleteRecords());
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(ActivityLogResource::canViewAny());
        $this->assertTrue(ReviewQueuePage::canAccess());
    }
}
