<p align="center">
  <img src="public/icons/cover logo png.png" alt="Puretec Logo" width="480">
</p>

<h1 align="center">Pure POS v1.0.0</h1>

<p align="center">
  <strong>Professional Point of Sale System</strong> by <strong>Puretec</strong><br>
  <em>The Art of Refined Technology</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 10">
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 5.3">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/License-Proprietary-C9A227?style=for-the-badge" alt="Proprietary">
</p>

<p align="center">
  A full-featured, multi-tenant, web-based POS system built for Sri Lankan retail businesses.<br>
  Manage sales, inventory, finances & customers — all from one beautiful dashboard.
</p>

---

## ✨ Features at a Glance

<table>
  <tr>
    <td width="50%">

### 💳 Point of Sale
- Fast product search (name, SKU, barcode)
- Category filter grid with product images
- Cart management (add, remove, update qty)
- Customer search & selection
- 6 payment methods — Cash, Card, Mobile, Bank, Credit, Gift Card
- Quick numpad for cash entry & change calculation
- Discount — %, fixed amount & coupon codes
- Gift card validation & redemption
- Hold orders & resume later
- Print receipts & invoices
- Barcode scanner support
- Customer-facing display screen
- Cash register open / close workflow
- Pending payment approvals

</td>
<td width="50%">

### 📦 Inventory & Products
- Full product CRUD with variants (size, color, etc.)
- Auto SKU generation & barcode generation
- Category & sub-category management with quick-add
- Stock tracking with low-stock & reorder-level alerts
- Inventory movements log
- Manual stock adjustments
- Expiry date tracking
- Bulk import via Excel / CSV (downloadable template)
- Bulk delete products
- Quick inline updates & status toggle
- Price tag printing
- Product image management

</td>
  </tr>
  <tr>
    <td>

### 👥 People Management
- **Customers** — CRUD, groups, loyalty points & tiers, purchase history
- **Customer Groups** — segmentation for targeted management
- **Suppliers** — CRUD with balance tracking, contact & address info

</td>
<td>

### 💰 Finance
- **Purchase Orders** — create, receive & track supplier orders
- **Expenses** — track by category with date range filtering
- **Income** — track non-sale income by category
- **Gift Cards** — issue, validate & redeem
- **Coupons / Discounts** — % & fixed-amount management
- **Cash Register** — open/close sessions with float tracking

</td>
  </tr>
</table>

---

## 📊 Reports Suite

Pure POS includes **13 comprehensive reports**, all exportable as **PDF** or **Excel**.

| Report | Description |
|--------|-------------|
| 📅 **Daily Summary** | Today's sales, orders, payments & trends |
| 💰 **Sales Report** | Revenue breakdown by date range with charts |
| 🖥️ **POS Report** | Register-level transaction analysis |
| 📈 **Profit & Loss** | Revenue vs. cost with margin analysis |
| 💸 **Cashflow** | Cash inflows, outflows & net position |
| 📦 **Inventory Valuation** | Stock value, cost & retail price analysis |
| 🔄 **Stock Movements** | All inventory adjustments & transfers |
| 🧾 **Expense Report** | Expense breakdown by category & period |
| 👥 **Customer Analytics** | Top customers, purchase patterns & loyalty |
| 🕐 **Register Sessions** | Session open/close history & totals |
| 📋 **Balance Sheet** | Assets, liabilities & equity overview |
| 📖 **Cash Book** | Daily cash transaction ledger |

---

## ⚙️ Settings & Configuration

| Category | What You Can Configure |
|----------|----------------------|
| 🏪 **Store** | Name, address, logo, currency, timezone |
| 🧾 **Receipt** | Header, footer, logo, layout customization |
| 📟 **Devices** | Barcode scanner, card reader, cash drawer, customer display |
| 💳 **POS** | Default payment method, quick keys, behavior |
| 💲 **Tax** | Tax rates, inclusive/exclusive settings |
| 📦 **Inventory** | Low stock thresholds, reorder levels |
| 🔔 **Notifications** | Alerts for stock, orders, etc. |
| 🎄 **Season Mode** | Seasonal theme configuration |
| 💾 **Backup** | Download backup, restore from file, schedule auto-backups |
| 🔄 **Data Reset** | Factory reset with confirmation |
| 👤 **Users** | Create, edit, assign roles directly from settings |

---

## 🔐 Authentication & Security

- 🔑 Secure session-based authentication
- 🔒 Password reset via security questions (no email required)
- 🛡️ Role-based access control (RBAC) with granular permissions
- 🏢 Multi-tenant data isolation (tenant scoping middleware)
- ⚠️ Tenant payment lock — restrict access on overdue accounts
- 👤 User profile management

---

## 🚀 Installation

### Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.1+ |
| MySQL | 8.0+ |
| Composer | Latest |
| Node.js & npm | Latest LTS |

### Quick Setup

```bash
# 1. Extract & enter directory
cd pure-pos

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
DB_DATABASE=pure_pos
DB_USERNAME=root
DB_PASSWORD=your_password

# 6. Run migrations & seed
php artisan migrate --seed

# 7. Create storage link
php artisan storage:link

# 8. Build assets
npm run build

# 9. Start server
php artisan serve
```

### 🌐 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@purepos.lk` | `admin123` |
| **Cashier** | `cashier@purepos.lk` | `cashier123` |

> Access the app at `http://localhost:8000`

---

## 🖥️ Desktop App (PWA)

Pure POS can be installed as a desktop or mobile app:

1. Open `http://localhost:8000/launch.html` in **Chrome** or **Edge**
2. Click the install icon (**⊕**) in the address bar
3. Click **"Install"** — done!

---

## 👤 User Roles

| Role | Access Level |
|------|-------------|
| `super_admin` | Full access to everything including platform admin panel |
| `admin` | All modules except super admin & platform functions |
| `manager` | POS, Products, Reports (no settings or user management) |
| `cashier` | POS, Orders, Customers only |
| `inventory` | Products, Inventory, Purchases, Suppliers |
| `viewer` | Dashboard & Reports only (read-only) |

---

## 🏗️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.1, Laravel 10 |
| **Frontend** | Bootstrap 5.3, Bootstrap Icons, Plus Jakarta Sans |
| **Database** | MySQL 8.0 |
| **Charts** | Chart.js 4 |
| **PDF Export** | DomPDF (barryvdh/laravel-dompdf) |
| **Excel Import/Export** | Maatwebsite Excel 3.1 |
| **Image Processing** | Intervention Image 2.7 |
| **Permissions** | Spatie Laravel Permission 6.0 |
| **Barcode Generation** | Picqer PHP Barcode Generator 2.2 |
| **Build Tool** | Vite |
| **PWA** | Web App Manifest + Service Worker |

---

## 📁 Project Structure

```
pure-pos/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # 16 controllers (POS, Products, Orders, Reports, etc.)
│   │   │   ├── Auth/             # AuthController, ForgotPasswordController
│   │   │   └── Nexfloit/         # Platform admin panel controllers
│   │   └── Middleware/           # Auth, Role, Permission, TenantScope, TenantLock
│   ├── Models/                   # 27 Eloquent models
│   ├── Services/                 # Business logic
│   │   ├── POSService.php        # Sale processing, payments, register
│   │   ├── InventoryService.php  # Stock adjustments, movement tracking
│   │   ├── ReportService.php     # Report data aggregation
│   │   └── TenantSetupService.php # Multi-tenant provisioning
│   └── Helpers/                  # Global helper functions
├── database/
│   ├── migrations/               # All table migrations
│   └── seeders/                  # Sample data seeders
├── resources/
│   └── views/
│       ├── layouts/              # Master layout with sidebar navigation
│       ├── auth/                 # Login & password reset pages
│       ├── dashboard/            # Dashboard with stats & charts
│       ├── pos/                  # POS terminal screen
│       ├── products/             # Product CRUD, import, price tags
│       ├── categories/           # Category management
│       ├── customers/            # Customer management
│       ├── suppliers/            # Supplier management
│       ├── orders/               # Order history, receipts, invoices
│       ├── purchases/            # Purchase order management
│       ├── inventory/            # Stock management
│       ├── expenses/             # Expense tracking
│       ├── incomes/              # Income tracking
│       ├── reports/              # 13 report views
│       ├── settings/             # System settings (all-in-one page)
│       ├── users/                # User management
│       ├── components/           # Reusable Blade components
│       └── emails/               # Email templates
├── public/
│   ├── css/
│   │   ├── pure-pos.css          # Main stylesheet (custom design system)
│   │   └── pos.css               # POS screen styles
│   ├── js/pure-pos.js            # Core JavaScript
│   ├── manifest.json             # PWA manifest
│   ├── sw.js                     # Service worker
│   └── launch.html               # Desktop PWA launcher
├── routes/
│   ├── web.php                   # Application routes
│   └── nexfloit.php              # Platform admin routes
└── config/purepos.php            # App configuration
```

---

## ⌨️ POS Keyboard Shortcuts

| Key | Action |
|-----|--------|
| `F2` | Focus search bar |
| `F4` | Hold current order |
| `F9` | Process payment |
| `Esc` | Clear focus / close modal |

---

## 📋 Routes Overview

| Module | Routes | Key Endpoints |
|--------|--------|--------------|
| **POS** | 17 | `/pos`, `/pos/sale`, `/pos/hold`, `/pos/register/open` |
| **Products** | 14 | CRUD + import, barcode, price-tags, bulk-delete |
| **Categories** | 4 | CRUD + quick-add, suggest-code |
| **Orders** | 6 | Index, show, edit, receipt, invoice, refund |
| **Purchases** | 4 | CRUD + receive |
| **Inventory** | 3 | Index, adjust, movements |
| **Expenses** | 5 | Full CRUD |
| **Incomes** | 6 | Full CRUD + category store |
| **Reports** | 13 | Daily, sales, POS, inventory, profit, cashflow, etc. |
| **Settings** | 14 | Store, receipt, devices, tax, backup, reset, etc. |
| **Users** | 5 | Full CRUD (except show) |
| **Auth** | 8 | Login, logout, password reset flow, profile |

---

## 📄 License

**Proprietary** — All rights reserved.

---

<p align="center">
  <img src="public/icons/cover logo png.png" alt="Puretec" width="200">
  <br><br>
  <strong>© 2024 Puretec</strong> — The Art of Refined Technology<br>
  Built with ❤️ for Sri Lankan businesses
</p>
