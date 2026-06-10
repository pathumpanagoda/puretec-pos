# 🏪 Ceylon POS v1.0.0
**Professional Point of Sale System** by **Nexfloit**

A full-featured, web-based POS system built with Laravel, Bootstrap & MySQL.

---

## ✨ Features

### 💳 Point of Sale
- Fast product search (name, SKU, barcode)
- Category filter grid with product images
- Cart management (add, remove, update qty)
- Customer search & selection
- Multiple payment methods (Cash, Card, Mobile, Bank, Credit, Gift Card)
- Quick numpad for cash entry
- Discount (%, fixed, coupon codes)
- Hold orders & resume later
- Print receipts
- Barcode scanner support

### 📦 Inventory
- Product management with variants
- Stock tracking with low-stock alerts
- Inventory movements log
- Manual stock adjustments
- Expiry date tracking
- Reorder level alerts

### 👥 People
- Customer management & groups
- Loyalty points system
- Supplier management
- Credit limit management

### 💰 Finance
- Purchase orders (full receiving workflow)
- Expense tracking by category
- Gift cards
- Coupon/discount management
- Cash register open/close

### 📊 Reports
- Sales report (daily/monthly)
- Profit & Loss
- Inventory valuation
- Top selling products
- Customer analytics
- Expense summary

### ⚙️ System
- Multi-user with role-based access
- Dark/light theme
- Responsive (mobile/tablet/desktop)
- PWA (installable as desktop/mobile app)
- Activity logging
- Store settings

---

## 🚀 Installation

### Requirements
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js & npm

### Steps

```bash
# 1. Extract & enter directory
cd ceylon-pos

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
DB_DATABASE=ceylon_pos
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

### 🌐 Access
- URL: `http://localhost:8000`
- Admin: `admin@ceylonpos.lk` / `admin123`
- Cashier: `cashier@ceylonpos.lk` / `cashier123`

---

## 🖥️ Desktop App (PWA)

To install as a desktop application:
1. Open `http://localhost:8000/launch.html` in Chrome/Edge
2. Click the install icon in the address bar
3. Click "Install" to add to desktop

---

## 👤 User Roles

| Role         | Access                                      |
|-------------|---------------------------------------------|
| super_admin | Full access to everything                   |
| admin       | All modules except super admin functions   |
| manager     | POS, Products, Reports (no settings)       |
| cashier     | POS, Orders, Customers only                |
| inventory   | Products, Inventory, Purchases             |
| viewer      | Dashboard & Reports only (read-only)       |

---

## 🏗️ Tech Stack

- **Backend**: PHP 8.1, Laravel 10
- **Frontend**: Bootstrap 5.3, Plus Jakarta Sans, Bootstrap Icons
- **Database**: MySQL 8.0
- **Charts**: Chart.js 4
- **Build**: Vite + Sass
- **PWA**: Web App Manifest + Service Worker

---

## 📁 Project Structure

```
ceylon-pos/
├── app/
│   ├── Http/Controllers/     # Controllers per module
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic (POSService, InventoryService, ReportService)
│   ├── Http/Middleware/      # Auth, Role, Permission middleware
│   └── Helpers/              # Global helper functions
├── database/
│   ├── migrations/           # All table migrations
│   └── seeders/              # Sample data seeder
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php    # Master layout with sidebar
│   │   ├── auth/login.blade.php     # Login page
│   │   ├── dashboard/               # Dashboard
│   │   ├── pos/                     # POS screen (main feature)
│   │   ├── products/                # Product CRUD
│   │   ├── customers/               # Customer management
│   │   ├── orders/                  # Order history & receipts
│   │   ├── reports/                 # Report views
│   │   └── settings/                # System settings
│   └── scss/                        # Sass source files
├── public/
│   ├── css/ceylon-pos.css   # Main stylesheet (blue/green theme)
│   ├── css/pos.css          # POS screen styles
│   ├── js/ceylon-pos.js     # Core JavaScript
│   ├── manifest.json        # PWA manifest
│   ├── sw.js                # Service worker
│   └── launch.html          # Desktop launcher
├── routes/web.php           # All application routes
└── config/ceylonpos.php     # App configuration
```

---

## 🔧 Key Keyboard Shortcuts (POS Screen)

| Key | Action           |
|-----|-----------------|
| F2  | Focus search     |
| F4  | Hold order       |
| F9  | Process payment  |
| Esc | Clear focus      |

---

© 2024 **Nexfloit** — All rights reserved.
Built with ❤️ for Sri Lankan businesses.
