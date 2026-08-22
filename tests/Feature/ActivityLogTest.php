<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogResource;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesExec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@huenics.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'is_owner' => true,
        ]);

        $this->salesExec = User::create([
            'name' => 'Agent Smith',
            'email' => 'smith@huenics.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SALES_EXECUTIVE,
        ]);
    }

    public function test_model_creation_is_logged_automatically(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'canonical_name' => 'Heavy Duty Angle Valve 1/2"',
            'unit_default' => 'pcs',
            'default_price' => 450.00,
            'is_huenics_owned' => true,
        ]);

        $log = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->where('event', AuditLog::EVENT_CREATED)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertStringContainsString('Created Product', $log->description);
        $this->assertEquals('Heavy Duty Angle Valve 1/2"', $log->new_value['canonical_name']);
    }

    public function test_model_update_tracks_exact_attribute_diff(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'canonical_name' => 'GI Elbow 1"',
            'unit_default' => 'pcs',
            'default_price' => 120.00,
        ]);

        // Update the product
        $product->update([
            'default_price' => 145.50,
            'unit_default' => 'box',
        ]);

        $log = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->where('event', AuditLog::EVENT_UPDATED)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertArrayHasKey('default_price', $log->old_value);
        $this->assertArrayHasKey('default_price', $log->new_value);
        $this->assertEquals(145.50, $log->new_value['default_price']);
    }

    public function test_model_soft_delete_and_restore_are_logged(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'canonical_name' => 'Teflon Tape 3/4"',
            'unit_default' => 'rolls',
            'default_price' => 35.00,
        ]);

        // Delete
        $product->delete();

        $deleteLog = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->where('event', AuditLog::EVENT_DELETED)
            ->first();

        $this->assertNotNull($deleteLog);
        $this->assertStringContainsString('Deleted Product', $deleteLog->description);

        // Restore
        $product->restore();

        $restoreLog = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->where('event', AuditLog::EVENT_RESTORED)
            ->first();

        $this->assertNotNull($restoreLog);
        $this->assertStringContainsString('Restored Product', $restoreLog->description);
    }

    public function test_authentication_events_are_logged(): void
    {
        event(new Login('web', $this->salesExec, false));

        $loginLog = AuditLog::where('event', AuditLog::EVENT_LOGIN)
            ->where('user_id', $this->salesExec->id)
            ->first();

        $this->assertNotNull($loginLog);
        $this->assertStringContainsString('logged into the system', $loginLog->description);

        event(new Logout('web', $this->salesExec));

        $logoutLog = AuditLog::where('event', AuditLog::EVENT_LOGOUT)
            ->where('user_id', $this->salesExec->id)
            ->first();

        $this->assertNotNull($logoutLog);
        $this->assertStringContainsString('signed out', $logoutLog->description);
    }

    public function test_activity_log_resource_authorization(): void
    {
        $opsManager = User::create([
            'name' => 'Ops Boss',
            'email' => 'ops@huenics.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OPERATIONS_MANAGER,
        ]);

        $ceo = User::create([
            'name' => 'CEO Executive',
            'email' => 'ceo@huenics.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CEO,
        ]);

        $this->actingAs($this->admin);
        $this->assertTrue(ActivityLogResource::canViewAny());

        $this->actingAs($ceo);
        $this->assertTrue(ActivityLogResource::canViewAny());

        $this->actingAs($opsManager);
        $this->assertTrue(ActivityLogResource::canViewAny());

        $this->actingAs($this->salesExec);
        $this->assertFalse(ActivityLogResource::canViewAny());
    }
}
