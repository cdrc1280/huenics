# HUENICS INTERNAL SYSTEM
## Comprehensive Role-Based User Operations Manual
### Step-by-Step Guide: From Login to Logout
**Word Document**: [Huenics_System_User_Manual.docx](file:///c:/laragon/www/huenics/Huenics_System_Proposal/Huenics_System_User_Manual.docx)

---

## 1. Universal Access, Security & Login Procedure

### Step 1.1: Accessing the Portal
1. Open Google Chrome, Microsoft Edge, or Mozilla Firefox and navigate to the application URL:
   **`http://localhost:8000/admin`**
2. If unauthenticated, the system automatically redirects to the secure **Login Page (`/admin/login`)**.
3. Input your registered company email and password.
4. Click **"Sign in"**. Upon authentication, your role-specific dashboard will load.

### Step 1.2: Default Access Credentials
| Role | Email Address | Default Password | Primary Permission Scope |
| :--- | :--- | :--- | :--- |
| **Sales Executive** | `sales@huenics.com` | `password` | Create quotes, convert to PO, upload quotation PDFs (Viewing Mode), track own deliveries & sales quota |
| **Operations Manager** | `ops@huenics.com` | `password` | Upload PO/quote PDFs, verify extractions, audit VAT/math errors, commit ledger transactions, manage inventory & warranty |
| **Administrator** | `admin@huenics.com` | `password` | Full system CRUD, user administration, layout rules & master catalog |
| **CEO / Executive** | `ceo@huenics.com` | `password` | Executive dashboards, gross profit analytics, ledger audit oversight |

---

## 2. Role Manual: Sales Executive (Sales 1, 2, 3)

### Step 2.1: Creating a Customer Quotation
1. In the left navigation sidebar, click **"Sales & Quotations" ➔ "Quotations"**.
2. Click the top-right **"New Quotation"** button.
3. Fill in the Quotation Details:
   - **Quotation #**: Automatically generated sequentially (e.g. `QT-2026-0001`).
   - **Sales Agent**: Pre-filled and locked to your logged-in user account.
   - **Customer / Client**: Type the customer name (e.g. *Avida Land Corp.*).
   - **Project / Site**: Select the relevant project from the dropdown if applicable.
   - **Quotation Date**: Defaults to today's date.
   - **Valid Until**: Select an expiration date for the quotation estimate.
4. Add Line Items / Products:
   - Click **"+ Add Line Item"**.
   - Select a product from the catalog. Description, unit, selling price, and base cost will auto-fill.
   - Adjust the **Quantity** or **Unit Price (₱)**. Line Total and Estimated Gross Profit recompute in real-time.
5. Click **"Save"**.
   - The quotation is created in **Pending** status.
   - Your monthly quota quotation counter automatically increments by 1.

### Step 2.2: 1-Click Quotation ➔ Purchase Order (PO) Conversion
When the client confirms the order:
1. Locate your quotation in the Quotations list.
2. Click the blue **"Convert to PO"** button (shopping cart icon) on the row.
3. In the conversion modal:
   - Verify **Order Date** and input **Expected Delivery Date**.
   - Select the **Warranty Period** from the 3 fixed standard options:
     - **1 Year (1yr)** *(Standard Default — 12 Months)*
     - **2 Years (2yrs)** *(Extended Coverage — 24 Months)*
     - **6 Months** *(Short-term — 6 Months)*
   - Add any delivery notes or customer special requests.
4. Click **"Convert to PO"**.
   - A Purchase Order is generated (e.g. `PO-2026-0001`).
   - Standard 12% Philippine VAT is computed automatically:
     $$\text{Computed VAT} = \frac{\text{Order Amount}}{1.12} \times 0.12$$
   - The order amount is credited directly to your monthly sales quota.

### Step 2.3: Monitoring Deliveries & Quota Achievement
- Go to **"Sales & Quotations" ➔ "Purchase Orders"** to monitor delivery statuses (`Pending`, `In Transit`, `Delivered`, `Overdue`).
- Click **"Reports & Analytics" ➔ "Sales Dashboard"** to check your monthly quota achievement gauge (₱ achieved vs target) and win rate %.

### Step 2.4: Uploading Quotation PDFs (Contextual Upload & Viewing Mode)
1. To ingest an existing quotation PDF, go to **"Sales & Quotations" ➔ "Quotations"** and click the yellow **"Upload Quotation PDF"** button in the header action bar.
2. Select the quotation PDF file and optionally choose the Vendor / Project. The dynamic per-vendor template parser will automatically detect the vendor format and extract line items.
3. You will be redirected to the **Review Queue** in **Viewing Mode** to inspect extracted prices and arithmetic checks.

---

## 3. Role Manual: Operations Manager

### Step 3.1: Contextual Quotation & Purchase Order PDF Ingestion
1. **To upload a Purchase Order or Order Slip PDF**: Go to **"Sales & Quotations" ➔ "Purchase Orders"** and click **"Upload Purchase Order PDF"**.
2. **To upload a Quotation or Vendor Agreement PDF**: Go to **"Sales & Quotations" ➔ "Quotations"** and click **"Upload Quotation PDF"**.
3. The system executes:
   - **SHA-256 duplicate validation**: Prevents duplicate ingestion of identical files.
   - **Secure storage**: Vaults the file in protected private storage (`storage/app/private/`).
   - **Dynamic Per-Vendor Templates**: Extracts line items, vendor fields, and aliases according to vendor-specific layout rules.
   - **Automated Math & VAT Reconciliation**: Flags arithmetic discrepancies (.85 line item error) and 12% standard Philippine VAT deviations.

### Step 3.2: Review Queue & Split-Screen Verification
1. Go to **"Document Ingestion" ➔ "Review Queue"**. Inspect the KPI stat cards (Pending Review, Math & VAT Issues Flagged, Verified Ledger).
2. Click **"Verify & Reconcile"** on any flagged document.
3. In the split-screen verification workspace:
   - **Left Column**: Interactive PDF viewer.
   - **Right Column**: Editable form with red callout boxes highlighting discrepancies.
4. Correct any values directly and click **"Save Draft & Re-Calculate"**.
5. Click **"Approve & Commit Transaction"** to post the reconciled record to the master ledger.

### Step 3.3: Managing In-House Stock & Inventory
1. Click **"Inventory & Warehouse" ➔ "Inventory Dashboard"**.
2. Review stock-on-hand, reserved, available, and reorder points for Huenics-owned products.
3. Click **"Adjust Stock"** on any product to record adjustments (*Initial Stock*, *Purchase In*, *Adjustment Up*, *Adjustment Down*) with required reason notes.
4. Click **"Transaction History"** to audit timestamped inventory logs.

### Step 3.4: Marking Delivery & Automatic BOM Deduction
1. When goods are delivered to the customer, go to **"Sales & Quotations" ➔ "Purchase Orders"**.
2. Click **"Mark Delivered"** (green truck icon).
3. Enter the **Actual Delivery Date** and **Delivery Receipt # (DR#)**.
4. Confirm delivery. The system automatically:
   - **Deducts modular BOM sub-components** (COB, Driver, Heatsink) and main Huenics stock from inventory.
   - **Activates the warranty clock** anchored to the delivery date based on the chosen period (**1 Year (1yr)**, **2 Years (2yrs)**, or **6 Months**).
   - Sets warranty status to **Active**.

---

## 4. Role Manual: System Administrator

### Step 4.1: User Account & Role Management
1. Click **"System Administration" ➔ "Users"**.
2. Click **"New User"** to register team members and assign their role:
   - `Admin`, `Operations Manager`, `Sales Executive`, `CEO`.
3. Modify permissions or deactivate user accounts as needed.

### Step 4.2: Master Catalog & Modular BOM Configuration
1. Go to **"Catalog & Mapping" ➔ "Products"**.
2. Create or edit products with Product Codes, Canonical Names, Categories, Base Costs (₱), and Selling Prices (₱).
3. Toggle **"Huenics Proprietary Product"** to enable stock tracking.
4. Toggle **"Composite Modular BOM Product"** for products assembled from sub-components.

### Step 4.3: Audits & Master Records
- Inspect finalized transactions under **"Reconciliation & Records" ➔ "Transactions"**.
- Audit system security logs and user activities under **Audit Logs**.

---

## 5. Role Manual: CEO / Executive Oversight

### Step 5.1: Real-Time Sales & Profit Analytics
1. Navigate to **"Reports & Analytics" ➔ "Sales Dashboard"**.
2. Monitor Header KPI Stat Cards:
   - **Quotations This Month**: Active pipeline volume.
   - **POs Won / Converted**: Win rate % and converted deal count.
   - **Revenue & Realized Gross Profit**:
     $$\text{Gross Profit} = \text{Order Selling Price} - (\text{Base Cost} + \text{BOM Component Costs})$$
   - **Warranties Expiring Soon**: Active warranties expiring within 30 days.
   - **Overdue Deliveries**: Deliveries past expected date.
3. Review the **Sales Leaderboard** ranking sales agents by revenue achieved vs monthly targets.

### Step 5.2: Compliance & Liability Oversight
- Access Purchase Orders and Master Transactions in read-only mode to audit delivery receipts and active warranty clocks.
- Receive automated alerts for expiring warranties and inventory threshold levels.

---

## 6. Safe Logout & Session Security

1. Click your **Name / Profile Avatar** at the bottom-left of the navigation sidebar.
2. Click **"Sign out"**.
3. Your session is securely invalidated, redirecting to the login screen.

---

*Huenics Internal System — Confidential Operations Manual*
