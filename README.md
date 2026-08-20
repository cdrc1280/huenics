# Huenics — AI Document Reconciliation & Procurement Management System

<p align="center">
  <strong>Intelligent Procurement, Multi-Engine OCR Extraction, 12% Philippine VAT Auditing, and 3-Way Financial Reconciliation Platform</strong>
</p>

---

## 🌟 Key Features

- **🚀 Native Windows & Cloud OCR Pipeline**:
  - High-precision optical character recognition utilizing Windows Media OCR (`winocr`) with automatic Tesseract fallback.
  - Image pre-processing with **2.5× Lanczos spatial upscaling**, **1.8× contrast enhancement**, and **2D bounding-box spatial clustering** for tabular line extraction.
  - Supports PDF documents and high-resolution images (`JPG`, `PNG`, `WEBP`, `TIFF`).

- **🧮 Mathematical & VAT Reconciliation Engine**:
  - Automatically verifies line calculations ($Qty \times Price = Total$), detecting the common `.85` pricing deviation and honoring Discounted vs Regular Unit Prices.
  - Validates **12% Philippine VAT** compliance against computed gross and subtotal figures.
  - Flags arithmetic anomalies and discrepancies in real-time.

- **🖥️ Interactive Split-Screen Verification Workspace**:
  - Side-by-side original document viewer (PDF/Image) paired with live editable data fields.
  - Real-time client-side synchronization and highlighting.
  - **Full Undo / Redo History** (`Ctrl+Z` / `Ctrl+Y`) and one-click **Re-Extract (AI/OCR)**.

- **📦 End-to-End Procurement & Sales Workflow**:
  - **Quotations / Agreements**: Draft, review, approve, and convert directly to Purchase Orders.
  - **Purchase Orders (PO)**: Read-only verified tracking with warranty clocks and delivery milestones.
  - **Delivery Receipts (DR)**: Generate printable A4 DRs with auto-deduction of inventory BOM components.
  - **Sales Invoices (SI)**: Create BIR-compliant sales invoices linked to parent transactions.
  - **3-Way Matching**: Cross-matches Quotations, Purchase Orders, and Order Slips into verified ledger transactions.

- **📄 Printable A4 Templates & PDF Export**:
  - Standardized enterprise PDF templates with official electronic signatures, peso currency formatting (`₱X,XXX.XX`), and clean typography.

---

## 🛠️ Technology Stack

| Component | Technology |
|---|---|
| **Framework** | [Laravel 11](https://laravel.com/) (PHP 8.2+) |
| **Admin Panel** | [Filament PHP](https://filamentphp.com/) |
| **Frontend** | [Livewire 3](https://livewire.laravel.com/), [Tailwind CSS](https://tailwindcss.com/), [Alpine.js](https://alpinejs.dev/) |
| **OCR Engine** | Python 3 (`winocr`, `Pillow`), Tesseract CLI |
| **PDF Generation** | [Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) |
| **Testing** | PHPUnit (SQLite In-Memory testing suite) |

---

## 📋 System Requirements

- **PHP**: `^8.2` (Extensions: `pdo`, `pdo_mysql`, `pdo_sqlite`, `fileinfo`, `gd`, `mbstring`, `openssl`, `curl`)
- **Composer**: `^2.0`
- **Node.js & NPM**: Node 18+ & NPM 9+
- **Database**: MySQL 8.0+ / MariaDB 10.4+
- **Python**: 3.10+ (for Windows Media OCR engine)

---

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/YOUR_USERNAME/huenics.git
cd huenics
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Frontend Dependencies & Build Assets
```bash
npm install
npm run build
```

### 4. Install Python OCR Dependencies
```bash
pip install winocr Pillow
```

### 5. Environment Configuration
Copy `.env.example` to `.env` and set your database credentials:
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database connection in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=huenics
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Run Migrations & Seeders
```bash
php artisan migrate --seed
php artisan storage:link
```

### 7. Run Automated Tests
Verify all 24 test suites pass in SQLite memory:
```bash
php -d extension=pdo_sqlite vendor/bin/phpunit
```

### 8. Start Local Development Server
```bash
php artisan serve
```
Access the admin portal at: `http://localhost:8000/admin`

---

## 👥 Default Roles & Credentials

The database seeder provisions the following accounts:

| Role | Email | Password | Access Scope |
|---|---|---|---|
| **Administrator / Owner** | `admin@huenics.com` | `password` | Full System Access, Ledger Commit |
| **Operations Manager** | `operations@huenics.com` | `password` | Review Queue, Verification Workspace |
| **Sales Executive** | `sales@huenics.com` | `password` | Quotations, Purchase Orders, DRs, SIs |

---

## 🧪 Automated Testing Policy

> **⚠️ Database Safety Rule**: Automated tests are strictly isolated to **SQLite `:memory:`** to protect live development/production databases:
```bash
php -d extension=pdo_sqlite vendor/bin/phpunit
```

---

## 📄 License
Proprietary — All rights reserved by Huenics.
