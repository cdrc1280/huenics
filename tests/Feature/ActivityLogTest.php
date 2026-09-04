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

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-0099',
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Acme Corporation',
            'quotation_date' => now()->toDateString(),
            'total_amount' => 15000.00,
            'status' => Quotation::STATUS_PENDING,
        ]);

        $log = AuditLog::where('auditable_type', Quotation::class)
            ->where('auditable_id', $quotation->id)
            ->where('event', AuditLog::EVENT_CREATED)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertStringContainsString('Created Quotation', $log->description);
        $this->assertEquals('Acme Corporation', $log->new_value['customer_name']);
    }

    public function test_model_update_tracks_exact_attribute_diff(): void
    {
        $this->actingAs($this->admin);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-0100',
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Initial Client',
            'quotation_date' => now()->toDateString(),
            'total_amount' => 10000.00,
            'status' => Quotation::STATUS_PENDING,
        ]);

        // Update the quotation
        $quotation->update([
            'customer_name' => 'Updated Client Corp',
            'total_amount' => 12500.00,
        ]);

        $log = AuditLog::where('auditable_type', Quotation::class)
            ->where('auditable_id', $quotation->id)
            ->where('event', AuditLog::EVENT_UPDATED)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertArrayHasKey('customer_name', $log->old_value);
        $this->assertArrayHasKey('customer_name', $log->new_value);
        $this->assertEquals('Updated Client Corp', $log->new_value['customer_name']);
    }

    public function test_model_soft_delete_and_restore_are_logged(): void
    {
        $this->actingAs($this->admin);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-0101',
            'sales_agent_id' => $this->salesExec->id,
            'customer_name' => 'Client To Delete',
            'quotation_date' => now()->toDateString(),
            'total_amount' => 5000.00,
            'status' => Quotation::STATUS_PENDING,
        ]);

        // Delete
        $quotation->delete();

        $deleteLog = AuditLog::where('auditable_type', Quotation::class)
            ->where('auditable_id', $quotation->id)
            ->where('event', AuditLog::EVENT_DELETED)
            ->first();

        $this->assertNotNull($deleteLog);
        $this->assertStringContainsString('Deleted Quotation', $deleteLog->description);

        // Restore
        $quotation->restore();

        $restoreLog = AuditLog::where('auditable_type', Quotation::class)
            ->where('auditable_id', $quotation->id)
            ->where('event', AuditLog::EVENT_RESTORED)
            ->first();

        $this->assertNotNull($restoreLog);
        $this->assertStringContainsString('Restored Quotation', $restoreLog->description);
    }

    public function test_non_transaction_models_do_not_bloat_activity_logs(): void
    {
        $this->actingAs($this->admin);

        $product = Product::create([
            'canonical_name' => 'Excluded Product From Audit',
            'unit_default' => 'pcs',
            'default_price' => 200.00,
            'is_huenics_owned' => true,
        ]);

        $log = AuditLog::where('auditable_type', Product::class)
            ->where('auditable_id', $product->id)
            ->first();

        // Non-transaction model creation must not produce audit logs
        $this->assertNull($log);
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
