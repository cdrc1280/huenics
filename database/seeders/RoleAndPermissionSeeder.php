<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Standard Permissions Grouped by Module
        $permissionsData = [
            // Quotations & Sales
            [
                'name' => 'Create Quotations',
                'slug' => 'create_quotes',
                'group' => 'Quotations & Sales',
                'description' => 'Draft new quotations, upload customer RFQs, and build line items.',
            ],
            [
                'name' => 'Convert Quote to PO',
                'slug' => 'convert_to_po',
                'group' => 'Quotations & Sales',
                'description' => 'Convert approved sales quotations into active purchase orders.',
            ],
            [
                'name' => 'Manage Quotations',
                'slug' => 'manage_quotations',
                'group' => 'Quotations & Sales',
                'description' => 'View, edit, send, and manage status of sales quotations.',
            ],
            [
                'name' => 'Edit Quotation Documents',
                'slug' => 'edit_quotation_documents',
                'group' => 'Quotations & Sales',
                'description' => 'Edit extracted line items, terms, and prices in review queue.',
            ],
            [
                'name' => 'Select Warranty Period',
                'slug' => 'select_warranty',
                'group' => 'Quotations & Sales',
                'description' => 'Select warranty duration (1yr, 2yrs, 6mo, no warranty) for purchase orders.',
            ],
            [
                'name' => 'Track Personal Sales Quota',
                'slug' => 'track_personal_quota',
                'group' => 'Quotations & Sales',
                'description' => 'Monitor personal sales performance, targets, and monthly quota progress.',
            ],

            // Operations & Verification
            [
                'name' => 'Verify Vendor Documents',
                'slug' => 'verify_vendor_documents',
                'group' => 'Operations & Verification',
                'description' => 'Review ingested vendor PDFs, verify OCR extraction, and approve documents.',
            ],
            [
                'name' => 'Reconcile Math & VAT Errors',
                'slug' => 'reconcile_math_vat',
                'group' => 'Operations & Verification',
                'description' => 'Reconcile 12% VAT calculations, rounding differences, and line item subtotals.',
            ],
            [
                'name' => 'Manage Stock & Inventory',
                'slug' => 'manage_inventory_stock',
                'group' => 'Operations & Verification',
                'description' => 'Adjust warehouse stock levels, record scrap, and track inventory movements.',
            ],
            [
                'name' => 'Mark Delivery & Upload DR/SI',
                'slug' => 'mark_delivery',
                'group' => 'Operations & Verification',
                'description' => 'Upload delivery receipts, sales invoices, and mark purchase orders delivered.',
            ],
            [
                'name' => 'Edit Financial Transactions',
                'slug' => 'edit_transactions',
                'group' => 'Operations & Verification',
                'description' => 'Record and adjust customer payments, downpayments, and receivables.',
            ],

            // Catalog & Dynamic Parser
            [
                'name' => 'Manage Product Catalog',
                'slug' => 'manage_product_catalog',
                'group' => 'Catalog & Templates',
                'description' => 'Create and modify products, categories, SKU pricing, and unit costs.',
            ],
            [
                'name' => 'Configure Vendor Dynamic Layouts',
                'slug' => 'configure_vendor_layouts',
                'group' => 'Catalog & Templates',
                'description' => 'Configure vendor document parsing rules, header coordinates, and field mappings.',
            ],

            // Executive & Analytics
            [
                'name' => 'View Sales Dashboard & Reports',
                'slug' => 'view_sales_reports',
                'group' => 'Executive & Analytics',
                'description' => 'Access executive sales KPIs, conversion rates, and revenue dashboards.',
            ],
            [
                'name' => 'View Sales Leaderboard & Quotas',
                'slug' => 'view_leaderboards_quotas',
                'group' => 'Executive & Analytics',
                'description' => 'View company-wide sales rep leaderboards and monthly quota tracking.',
            ],
            [
                'name' => 'View Gross Profit Analytics',
                'slug' => 'view_profit_analytics',
                'group' => 'Executive & Analytics',
                'description' => 'View product margin analytics, revenue vs costs, and gross profit breakdown.',
            ],
            [
                'name' => 'View System Audit Trails',
                'slug' => 'view_audit_trails',
                'group' => 'Executive & Analytics',
                'description' => 'Inspect chronological activity logs, user logins, and data modifications.',
            ],

            // System Administration
            [
                'name' => 'Manage User Accounts',
                'slug' => 'manage_users',
                'group' => 'System Administration',
                'description' => 'Create, edit, reset passwords, and manage staff user accounts.',
            ],
            [
                'name' => 'Manage Dynamic Roles & Permissions',
                'slug' => 'manage_roles_permissions',
                'group' => 'System Administration',
                'description' => 'Create and configure system roles and assign granular module permissions.',
            ],
            [
                'name' => 'Delete System Records',
                'slug' => 'delete_records',
                'group' => 'System Administration',
                'description' => 'Soft delete or force delete quotations, orders, and master data.',
            ],
        ];

        $permissionModels = [];
        foreach ($permissionsData as $pData) {
            $permissionModels[$pData['slug']] = Permission::updateOrCreate(
                ['slug' => $pData['slug']],
                [
                    'name' => $pData['name'],
                    'group' => $pData['group'],
                    'description' => $pData['description'],
                ]
            );
        }

        // 2. Default Core Roles Matching the System Specification Table
        $rolesData = [
            'sales_executive' => [
                'name' => 'Sales Executive',
                'description' => 'Front-line sales team responsible for quoting, customer PO processing, warranty assignment, and quota tracking.',
                'permissions' => [
                    'create_quotes',
                    'convert_to_po',
                    'manage_quotations',
                    'edit_quotation_documents',
                    'select_warranty',
                    'track_personal_quota',
                ],
            ],
            'operations_manager' => [
                'name' => 'Operations Manager',
                'description' => 'Fulfillment and inventory management, vendor document verification, VAT reconciliation, and delivery coordination.',
                'permissions' => [
                    'verify_vendor_documents',
                    'reconcile_math_vat',
                    'manage_inventory_stock',
                    'mark_delivery',
                    'manage_product_catalog',
                    'edit_transactions',
                    'create_quotes',
                    'edit_quotation_documents',
                ],
            ],
            'admin' => [
                'name' => 'Administrator',
                'description' => 'Full administrative access across users, dynamic vendor parser configurations, master catalog, and all system modules.',
                'permissions' => array_keys($permissionModels), // Full access
            ],
            'ceo' => [
                'name' => 'CEO / Executive',
                'description' => 'Executive leadership with access to sales leaderboards, monthly quota progress, gross profit analytics, and audit trails.',
                'permissions' => [
                    'view_sales_reports',
                    'view_leaderboards_quotas',
                    'view_profit_analytics',
                    'view_audit_trails',
                    'manage_roles_permissions',
                    'manage_users',
                    'verify_vendor_documents',
                    'create_quotes',
                    'manage_quotations',
                    'convert_to_po',
                    'select_warranty',
                    'edit_transactions',
                    'edit_quotation_documents',
                ],
            ],
        ];

        $roleModels = [];
        foreach ($rolesData as $slug => $rData) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $rData['name'],
                    'description' => $rData['description'],
                    'is_system' => true,
                ]
            );

            // Sync permissions
            $permissionIds = [];
            foreach ($rData['permissions'] as $pSlug) {
                if (isset($permissionModels[$pSlug])) {
                    $permissionIds[] = $permissionModels[$pSlug]->id;
                }
            }
            $role->permissions()->sync($permissionIds);
            $roleModels[$slug] = $role;
        }

        // 3. Ensure Default Role Access Accounts Exist with matching role and role_id
        $defaultAccounts = [
            [
                'email' => 'sales@huenics.com',
                'name' => 'Carlos Reyes (Sales Executive)',
                'role' => User::ROLE_SALES_EXECUTIVE,
                'role_id' => $roleModels['sales_executive']->id,
                'is_owner' => false,
            ],
            [
                'email' => 'ops@huenics.com',
                'name' => 'Maria Santos (Operations Manager)',
                'role' => User::ROLE_OPERATIONS_MANAGER,
                'role_id' => $roleModels['operations_manager']->id,
                'is_owner' => false,
            ],
            [
                'email' => 'admin@huenics.com',
                'name' => 'System Administrator',
                'role' => User::ROLE_ADMIN,
                'role_id' => $roleModels['admin']->id,
                'is_owner' => true,
            ],
            [
                'email' => 'ceo@huenics.com',
                'name' => 'Chief Executive Officer (CEO)',
                'role' => User::ROLE_CEO,
                'role_id' => $roleModels['ceo']->id,
                'is_owner' => true,
            ],
        ];

        foreach ($defaultAccounts as $acc) {
            User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'password' => Hash::make('password'),
                    'role' => $acc['role'],
                    'role_id' => $acc['role_id'],
                    'is_owner' => $acc['is_owner'],
                ]
            );
        }
    }
}
