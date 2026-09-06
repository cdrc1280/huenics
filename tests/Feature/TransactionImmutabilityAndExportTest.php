<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\TransactionResource;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use App\Services\TransactionExportService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionImmutabilityAndExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::where('email', 'admin@huenics.com')->first();

        $project = Project::create([
            'name' => 'Solar Power Project Alpha',
            'status' => 'active',
        ]);

        $vendor = Vendor::create([
            'name' => 'Acme Electrical Supply',
            'contact_person' => 'John Doe',
            'email' => 'vendor@acme.com',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-TRX-001',
            'sales_agent_id' => $this->admin->id,
            'customer_name' => 'Alpha Industrial',
            'order_amount' => 125000.50,
            'status' => PurchaseOrder::STATUS_PENDING,
            'order_date' => '2026-09-01',
        ]);

        $this->transaction = Transaction::create([
            'transaction_code' => 'TRX-20260906-TEST',
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'purchase_order_id' => $po->id,
            'final_amount' => 125000.50,
            'order_date' => '2026-09-01',
            'delivery_date' => '2026-09-10',
            'status' => 'pending_delivery',
            'is_completed' => false,
            'notes' => 'Urgent procurement for site Alpha',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_transaction_resource_cannot_be_edited(): void
    {
        $this->assertFalse(TransactionResource::canEdit($this->transaction));
    }

    public function test_transaction_resource_cannot_be_deleted(): void
    {
        $this->assertFalse(TransactionResource::canDelete($this->transaction));
    }

    public function test_transaction_resource_cannot_be_restored_or_force_deleted(): void
    {
        $this->assertFalse(TransactionResource::canRestore($this->transaction));
        $this->assertFalse(TransactionResource::canForceDelete($this->transaction));
    }

    public function test_transaction_resource_pages_do_not_contain_edit_route(): void
    {
        $pages = TransactionResource::getPages();

        $this->assertArrayNotHasKey('edit', $pages);
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
    }

    public function test_transaction_export_service_generates_valid_csv_with_utf8_bom(): void
    {
        $service = app(TransactionExportService::class);
        $csv = $service->exportCsv();

        $this->assertNotEmpty($csv);
        // Verify UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Verify CSV header columns
        $this->assertStringContainsString('Transaction Code', $csv);
        $this->assertStringContainsString('Final Amount (PHP)', $csv);
        $this->assertStringContainsString('Fulfillment Status', $csv);
        $this->assertStringContainsString('Notes', $csv);

        // Verify transaction data row
        $this->assertStringContainsString('TRX-20260906-TEST', $csv);
        $this->assertStringContainsString('Solar Power Project Alpha', $csv);
        $this->assertStringContainsString('Acme Electrical Supply', $csv);
        $this->assertStringContainsString('PO-2026-TRX-001', $csv);
        $this->assertStringContainsString('125000.50', $csv);
        $this->assertStringContainsString('Pending Delivery', $csv);
        $this->assertStringContainsString('Urgent procurement for site Alpha', $csv);
    }

    public function test_single_transaction_export_csv(): void
    {
        $service = app(TransactionExportService::class);
        $csv = $service->exportSingleTransactionCsv($this->transaction);

        $this->assertNotEmpty($csv);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('TRX-20260906-TEST', $csv);
        $this->assertStringContainsString('125000.50', $csv);
    }

    public function test_transactions_export_csv_web_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transactions.export-csv'));

        $response->assertSuccessful();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('huenics-transactions-', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_single_transaction_export_csv_web_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transactions.export-single-csv', ['transaction' => $this->transaction]));

        $response->assertSuccessful();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('TRX-20260906-TEST', (string) $response->headers->get('Content-Disposition'));
    }
}
