# AquaFlow — Multi-Branch Water Can Ordering, Inventory & Logistics Platform

An enterprise-grade, multi-tenant water can logistics, empty-container circulation, and doorstep distribution monolith built with **Laravel 12**, **MySQL / MariaDB**, **Tailwind CSS**, and **Alpine.js**.

Designed specifically for shared cPanel hosting environments with **Zero Node.js dependency** on production servers.

---

## Key Modules & Capabilities

### 1. Public Storefront & Customer Self-Service Portal
- **Interactive Water Can Catalog**: Real-time quantity selector with instant cart calculation and direct "Buy Now" flow.
- **Smart Date & Slot Scheduling**: Auto-selection of open delivery windows with evening cutoff logic.
- **Doorstep Delivery Tracking**: Real-time 5-stage visual tracker with unique 4-digit Delivery Verification OTP.
- **Empty-Can Passbook**: Transparent 1:1 container exchange audit tracking cans held, delivered, and returned.
- **Financial Passbook & Ledger**: Debit/credit statement with online dues settlement (Razorpay / UPI simulation).
- **Automated Subscriptions**: Recurring daily, alternate-day, and weekly delivery schedules with pause/resume controls.

### 2. Staff Driver Mobile PWA Portal
- **High-Contrast Dark Mode Runsheet**: Sequential stops with mobile tap-to-call and Google Maps directions.
- **Stop Completion with OTP**: Physical bottle exchange verification (full cans handed over, empties received, cash collected).
- **Cash in Hand Log**: Live driver cash balance tracking with hub deposit reconciliation prompts.

### 3. Admin Operations & Management Console
- **Role-Based Access Control (RBAC)**: Fine-grained permissions across Superadmin, Branch Admin, Hub Staff, and Customer.
- **Inventory & Warehouse Hubs**: Multi-branch stock tracking (Filled, Empty, Damaged, Cycle Cleaning) and inter-hub transfers.
- **Procurement & Goods Receipt Notes (GRN)**: Supplier liability and automated stock valuation.
- **Delivery Fleet & Dispatch**: Route planning, trip manifests, partner logistics, and slot capacity management.
- **Executive Analytics & P&L Statement**: Real-time Gross Margin, COGS, operating expenses, and container shrinkage tracking.
- **System & Branch Settings**: Branch metadata, tax configuration, delivery thresholds, and cache/storage maintenance.

---

## Technology Stack

- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MariaDB 10.4+ / MySQL 8.0+
- **Frontend Engine**: Blade Components, Tailwind CSS (Zero-Node CDN Engine), Alpine.js, jQuery
- **Architecture**: Monolithic architecture with strict multi-ledger segregation (`customer_ledger`, `can_movements`, `supplier_ledger`)

---

## Local Setup & Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/Arunkumaar1996/osGlobalWaterCanSuppler.git
   cd osGlobalWaterCanSuppler
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install --optimize-autoloader
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `.env` with your database credentials.

4. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Link Storage**:
   ```bash
   php artisan storage:link
   ```

6. **Start Local Development Server**:
   ```bash
   php artisan serve
   ```

---

## Running the Automated Test Suite

The platform includes a test suite covering unit, feature, RBAC, PWA, and end-to-end workflows:

```bash
php artisan test
```

All 146 tests (677 assertions) run against in-memory SQLite with zero external dependencies.

---

## Default Demo Credentials

| Role | Mobile / Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Superadmin** | `9876543210` / `admin@aquaflow.com` | `password` | Complete system access |
| **Branch Admin** | `9876543211` / `branch@aquaflow.com` | `password` | Branch operational control |
| **Staff Driver** | `9876543212` / `driver@aquaflow.com` | `password` | Mobile PWA runsheet & cash log |
| **Customer** | `9876543213` / `customer@aquaflow.com` | `password` | Storefront & self-service portal |

---

## License

This software is proprietary and confidential. All rights reserved.
