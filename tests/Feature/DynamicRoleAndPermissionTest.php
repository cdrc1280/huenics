<?php

namespace Tests\Feature;

use App\Filament\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicRoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_default_role_access_accounts_seeded_accurately(): void
    {
        $sales = User::where('email', 'sales@huenics.com')->first();
        $ops = User::where('email', 'ops@huenics.com')->first();
        $admin = User::where('email', 'admin@huenics.com')->first();
        $ceo = User::where('email', 'ceo@huenics.com')->first();

        $this->assertNotNull($sales);
        $this->assertEquals(User::ROLE_SALES_EXECUTIVE, $sales->role);
        $this->assertTrue($sales->hasPermission('create_quotes'));
        $this->assertTrue($sales->hasPermission('convert_to_po'));
        $this->assertTrue($sales->hasPermission('select_warranty'));
        $this->assertTrue($sales->hasPermission('track_personal_quota'));
        $this->assertFalse($sales->hasPermission('manage_users'));
        $this->assertFalse($sales->hasPermission('view_sales_reports'));

        $this->assertNotNull($ops);
        $this->assertEquals(User::ROLE_OPERATIONS_MANAGER, $ops->role);
        $this->assertTrue($ops->hasPermission('verify_vendor_documents'));
        $this->assertTrue($ops->hasPermission('reconcile_math_vat'));
        $this->assertTrue($ops->hasPermission('manage_inventory_stock'));
        $this->assertTrue($ops->hasPermission('mark_delivery'));

        $this->assertNotNull($admin);
        $this->assertEquals(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->hasPermission('manage_roles_permissions'));
        $this->assertTrue($admin->hasPermission('manage_users'));
        $this->assertTrue($admin->hasPermission('manage_product_catalog'));

        $this->assertNotNull($ceo);
        $this->assertEquals(User::ROLE_CEO, $ceo->role);
        $this->assertTrue($ceo->hasPermission('view_sales_reports'));
        $this->assertTrue($ceo->hasPermission('view_leaderboards_quotas'));
        $this->assertTrue($ceo->hasPermission('view_profit_analytics'));
        $this->assertTrue($ceo->hasPermission('view_audit_trails'));
    }

    public function test_only_ceo_and_admin_can_access_role_resource(): void
    {
        $admin = User::where('email', 'admin@huenics.com')->first();
        $ceo = User::where('email', 'ceo@huenics.com')->first();
        $ops = User::where('email', 'ops@huenics.com')->first();
        $sales = User::where('email', 'sales@huenics.com')->first();

        $this->actingAs($admin);
        $this->assertTrue(RoleResource::canViewAny());
        $this->assertTrue(RoleResource::shouldRegisterNavigation());

        $this->actingAs($ceo);
        $this->assertTrue(RoleResource::canViewAny());
        $this->assertTrue(RoleResource::shouldRegisterNavigation());

        $this->actingAs($ops);
        $this->assertFalse(RoleResource::canViewAny());
        $this->assertFalse(RoleResource::shouldRegisterNavigation());

        $this->actingAs($sales);
        $this->assertFalse(RoleResource::canViewAny());
        $this->assertFalse(RoleResource::shouldRegisterNavigation());
    }

    public function test_dynamically_modifying_role_permissions_updates_user_access(): void
    {
        $salesRole = Role::where('slug', 'sales_executive')->first();
        $salesUser = User::where('email', 'sales@huenics.com')->first();

        $this->assertFalse($salesUser->hasPermission('view_sales_reports'));

        // Admin dynamically grants "view_sales_reports" to Sales Executive role
        $viewReportsPerm = Permission::where('slug', 'view_sales_reports')->first();
        $salesRole->permissions()->attach($viewReportsPerm->id);

        // Reload user relation
        $salesUser->refresh();
        $this->assertTrue($salesUser->hasPermission('view_sales_reports'));

        // Admin revokes "create_quotes" from Sales Executive role
        $createQuotesPerm = Permission::where('slug', 'create_quotes')->first();
        $salesRole->permissions()->detach($createQuotesPerm->id);

        $salesUser->refresh();
        $this->assertFalse($salesUser->hasPermission('create_quotes'));
    }

    public function test_custom_role_creation_and_assignment(): void
    {
        $customRole = Role::create([
            'name' => 'Junior Estimator',
            'slug' => 'junior_estimator',
            'description' => 'Drafts quotes only without converting to PO',
            'is_system' => false,
        ]);

        $createQuotePerm = Permission::where('slug', 'create_quotes')->first();
        $customRole->permissions()->attach([$createQuotePerm->id]);

        $staffUser = User::create([
            'name' => 'Junior Staff',
            'email' => 'junior@huenics.com',
            'password' => bcrypt('password'),
            'role' => $customRole->slug,
            'role_id' => $customRole->id,
            'is_owner' => false,
        ]);

        $this->assertTrue($staffUser->hasPermission('create_quotes'));
        $this->assertFalse($staffUser->hasPermission('convert_to_po'));
        $this->assertFalse($staffUser->hasPermission('manage_users'));
    }
}
