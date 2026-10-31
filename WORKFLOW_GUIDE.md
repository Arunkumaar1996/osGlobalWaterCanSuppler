# AquaFlow Platform — End-to-End Business & Technical Workflow Manual

This document details the operational, financial, and logistical workflows of the **AquaFlow Water Can Ordering, Delivery Tracking, Inventory & Business Management Platform**.

---

## 1. System Actors & Roles Matrix

| Role Slug | Title | Key Responsibilities | Portal Access |
| :--- | :--- | :--- | :--- |
| `customer` | Residential / Commercial Customer | Orders water cans, manages recurring subscriptions, tracks deliveries, views financial passbook, settles dues online, tracks empty can container balances. | `/customer/*` |
| `delivery-staff` | In-House Delivery Driver | Operates mobile PWA runsheet, navigates stops, collects empty cans, collects COD cash, executes 4-digit OTP doorstep verification. | `/staff/*` |
| `delivery-supervisor`| Logistics Dispatcher | Schedules delivery trips, manages runsheets, allocates vehicles and 3rd-party partners, oversees slot capacity locks. | `/admin/delivery/*` |
| `inventory-manager` | Warehouse Operations Lead | Ingests full stock from suppliers, manages bottle washing/cleaning queues, logs damaged containers, initiates inter-hub transfers. | `/admin/inventory/*` |
| `order-manager` | Sourcing & Fulfillment Lead | Allocates order fulfillment between warehouse and supplier dropship, generates dispatch manifests and packing slips. | `/admin/orders/*`, `/admin/manifests/*` |
| `accounts-manager` | Finance & Ledger Accountant | Reviews customer ledgers, enforces B2B credit limits, records manual collections, tracks supplier payables, logs operating expenses. | `/admin/accounts/*`, `/admin/expenses/*` |
| `branch-manager` | Branch General Manager | Branch-level executive analytics, staff assignments, local customer approvals, inventory oversight. | Full Branch Scope |
| `super-admin` | Business Owner / Super Admin | Multi-branch consolidated P&L, global settings, supplier contracts, system maintenance, role delegation. | Full System Scope |

---

## 2. Public Storefront & Customer Onboarding Workflow

```mermaid
flowchart TD
    Guest([Visitor Lands on Homepage]) --> CheckPincode{Enter Pincode}
    CheckPincode -- "Not Serviced" --> UnservicedNotice[Display Delivery Coming Soon & Collect Contact Lead]
    CheckPincode -- "Serviced Hub Found" --> BranchContext[Set Active Branch Session & Display Products]
    BranchContext --> SelectProducts[Add Water Cans to Cart]
    SelectProducts --> CartView[View Cart & Adjust Quantities]
    CartView --> AuthCheck{Logged In?}
    AuthCheck -- "No" --> RegisterOrLogin[Customer Login / Mobile Registration]
    AuthCheck -- "Yes" --> CheckoutPortal[Proceed to Checkout]
```

### Process Steps:
1. **Pincode Verification**: Visitor enters their 6-digit postal code. The AJAX endpoint (`POST /check-pincode`) queries `delivery_pincodes` linked to `delivery_zones` and `branches`.
   - If serviceable: Returns branch name and delivery turnaround time (e.g. "Delivered within 2 hours by Central Chennai Hub").
   - If unserviceable: Offers a lead inquiry form saving data into `contact_inquiries`.
2. **Catalog Browsing**: Real-time listing of available water cans (20L Premium, 10L Dispenser, Bisleri/Kinley 20L, etc.) with selling price, tax rate, and empty-can deposit requirement flags.
3. **Registration & KYC**: Customers register with Mobile Number, Name, Email, Password, and Delivery Address (House/Flat, Street, Area, Landmark, Pincode).
   - Residential customers: Instant activation with ₹500 standard credit limit.
   - Commercial/Corporate B2B customers: Created with company GSTIN and business name, queued for Credit Limit approval.

---

## 3. Order Placement & Slot Capacity Workflow

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant W as Web Application
    participant CS as CartService
    participant SS as DeliverySlotService
    participant DB as MariaDB Database

    C->>W: Navigate to /checkout
    W->>CS: Retrieve active cart & calculate litres
    W->>SS: Request available slots for chosen date
    SS->>DB: Query delivery_slots & delivery_slot_capacity
    Note over SS,DB: Checks cutoff lead time, max orders, and max litres capacity
    SS-->>W: Return open, unblocked time windows
    C->>W: Select Address, Delivery Slot & Payment Mode (COD or Pay Later)
    W->>DB: Begin DB Transaction
    W->>DB: Insert into `orders` (Status: confirmed, fulfillment: unfulfilled, delivery: pending)
    W->>DB: Insert into `order_items` (product snapshots, can_type_id)
    W->>DB: Update `delivery_slot_capacity` (booked_orders_count++, booked_litres += L)
    alt Pay Later Chosen
        W->>DB: Check customer outstanding_balance + total <= credit_limit
        W->>DB: Insert debit entry into `customer_ledger`
        W->>DB: Update `customer_profiles.outstanding_balance`
    end
    W->>DB: Clear active session cart
    W->>DB: Commit Transaction
    W-->>C: Redirect to Order Confirmation page with Live Status Tracker
```

---

## 4. Recurring Subscriptions Lifecycle Workflow

```mermaid
flowchart TD
    CustomerSub[Customer Configures Subscription] --> CadenceChoice{Select Cadence}
    CadenceChoice -->|Every Day| Daily[daily]
    CadenceChoice -->|Every 2nd Day| AltDays[alternate_days]
    CadenceChoice -->|Weekly| Weekly[weekly]
    CadenceChoice -->|Specific Weekdays| CustomDays[custom_days e.g. Mon, Wed, Fri]
    
    Daily --> SetPlan[Set Start Date, Slot, Address & Payment Method]
    AltDays --> SetPlan
    Weekly --> SetPlan
    CustomDays --> SetPlan

    SetPlan --> SaveSub[(Save to `subscriptions` with SUB-YYYY-XXXX)]

    subgraph DailyCron ["Automated Daily Cron (00:05 AM)"]
        CronTrigger[php artisan subscriptions:generate] --> QueryDue[Find Active Subscriptions with next_delivery_date <= Target Date]
        QueryDue --> GenerateOrder[Auto-create Order with ORD-YYYYMMDD-XXXX]
        GenerateOrder --> AdvanceDate[Calculate Next Cycle Date via SubscriptionService]
        AdvanceDate --> UpdateSubStatus[(Update next_delivery_date & last_generated_date)]
    end

    SaveSub -.-> DailyCron

    subgraph CustomerControls ["Customer Self-Service Lifecycle"]
        PauseAction[Pause Plan] --> SetPaused[Status: paused - Orders Halted]
        ResumeAction[Resume Plan] --> RecalculateNext[Advance next_delivery_date to today/future]
        CancelAction[Cancel Plan] --> SetCancelled[Status: cancelled]
    end
```

---

## 5. Split-Fulfillment Engine & Sourcing Allocation

AquaFlow supports **Hybrid Sourcing**: An order can be fulfilled entirely from branch warehouse stock, directly dropshipped from an external supplier, or split between both.

```mermaid
flowchart LR
    ConfirmedOrder[Order: Confirmed & Unfulfilled] --> SourcingDecision{Fulfillment Strategy}
    
    SourcingDecision -->|Stock Available in Warehouse| WarehouseFulfill[Warehouse Allocation]
    SourcingDecision -->|Direct Supplier Supply| SupplierDropship[Supplier Direct Sourcing]
    SourcingDecision -->|Partial Stock Split| SplitAllocation[Split Sourcing]

    WarehouseFulfill --> ReserveStock[(Move Warehouse Stock: full_stock -> reserved_stock)]
    SupplierDropship --> POItem[Link to Supplier Purchase Order]
    SplitAllocation --> ReserveStock
    SplitAllocation --> POItem

    ReserveStock --> ManifestQueue[Add to Dispatch Manifest Queue]
    POItem --> ManifestQueue

    ManifestQueue --> DispatchAction[Dispatch Manifest Executed]
    DispatchAction --> DeductPhysical[(Deduct reserved_stock from warehouse_stocks)]
    DispatchAction --> HandoverFleet[Handover to Delivery Runsheet]
```

### Database Impact:
- Table `order_fulfillments`: Records `fulfillment_type` (`warehouse` vs `supplier`), `quantity`, and status (`pending`, `packed`, `dispatched`).
- Table `dispatch_manifests`: Batches multiple fulfillments under sequential manifest `MAN-YYYYMMDD-XXXX` with vehicle number, driver name, and item checklist.

---

## 6. Multi-Category Inventory & Bottle Life-Cycle

Water can containers undergo a continuous multi-state physical rotation:

```mermaid
stateDiagram-v2
    [*] --> Ingestion: Supplier GRN Delivery (New Full Cans)
    Ingestion --> FullStock: Accepted & QC Passed
    
    FullStock --> ReservedStock: Allocated to Customer Order
    ReservedStock --> FullStock: Order Cancelled
    ReservedStock --> InTransit: Dispatched on Delivery Runsheet
    
    InTransit --> CustomerCirculation: Delivered to Customer
    CustomerCirculation --> EmptyReturned: Empty Can Handed to Driver at Doorstep
    CustomerCirculation --> MissingDamaged: Lost or Broken by Customer (Penalty Billed)
    
    EmptyReturned --> UnderCleaning: Brought to Warehouse Washing Bay
    UnderCleaning --> FullStock: High-Pressure Sanitized & Refilled
    UnderCleaning --> DamagedStock: Cracked / Failed Pressure Test (Scrapped)
    
    DamagedStock --> [*]
    MissingDamaged --> [*]
```

### Stock Balance Rules:
- Physical Warehouse Total = `full_stock + empty_stock + under_cleaning + damaged_stock`.
- Available to Sell = `full_stock - reserved_stock`.
- Reorder Trigger: When `(full_stock - reserved_stock) <= reorder_level`, an automated alert badge appears on the Admin Inventory console.

---

## 7. Supplier Procurement & Accounts Payable (AP)

```mermaid
sequenceDiagram
    autonumber
    actor A as Procurement Manager
    participant S as Supplier
    participant App as Procurement Engine
    participant DB as Database (Inventory & Ledgers)

    A->>App: Create Purchase Order (PO-YYYY-XXXX) with products & quantities
    App-->>S: Transmit PO
    S-->>App: Water Cans Delivered to Hub
    A->>App: Create Goods Receipt Note (GRN) with Inspection
    Note over A,App: Records quantity_accepted vs quantity_damaged
    App->>DB: Increment Warehouse `full_stock` by quantity_accepted
    App->>DB: Ingest damaged units into `damaged_stock`
    App->>DB: Log entry in `inventory_movements` (type: supplier_receipt)
    App->>DB: Post credit liability in `supplier_ledger` (Total = accepted * unit_cost)
    App->>DB: Update `suppliers.outstanding_balance`
    A->>App: Record Supplier Payment (Cash / Bank Transfer)
    App->>DB: Post debit entry in `supplier_ledger`
    App->>DB: Reduce `suppliers.outstanding_balance`
```

---

## 8. Delivery Dispatch, Runsheet & Doorstep Verification

```mermaid
flowchart TD
    ScheduleTrips[Select Delivery Date & Slot] --> FilterOrders[Fetch Confirmed Orders Waiting for Dispatch]
    FilterOrders --> CreateRunsheet[Create Delivery Trip: TRIP-YYYYMMDD-XXXX]
    CreateRunsheet --> AssignDriver{Select Fleet Type}
    
    AssignDriver -->|In-House Driver| StaffDriver[Staff Profile & Vehicle Number]
    AssignDriver -->|3rd-Party Fleet| PartnerFleet[Delivery Partner e.g. Porter / Shadowfax]

    StaffDriver --> GenerateStops[Generate Sequential Stops & Compute 4-Digit OTPs]
    PartnerFleet --> GenerateStops

    GenerateStops --> DispatchTrip[Mark Trip: in_transit | Orders: out_for_delivery]

    subgraph DoorstepHandover ["Mobile PWA Doorstep Execution"]
        DriverArrives[Driver Arrives at Customer Address] --> RequestOTP[Request 4-Digit PIN from Customer]
        RequestOTP --> VerifyOTP{Enter OTP in Staff PWA}
        
        VerifyOTP -- "Invalid PIN" --> OTPError[Reject Delivery Handover]
        VerifyOTP -- "Valid PIN" --> HandoverSuccess[Record Delivered Cans]
        
        HandoverSuccess --> CollectEmpties[Input Number of Empty Cans Returned]
        HandoverSuccess --> CollectCOD[Collect Cash Amount if COD]
        
        CollectEmpties --> CompleteStop[Mark Stop: delivered | Order: delivered]
        CompleteStop --> UpdateCanBalance[(Update customer_can_balances & can_movements)]
        CompleteStop --> RecordCashPayment[(Insert into payments & credit ledger)]
    end

    DispatchTrip --> DoorstepHandover
```

---

## 9. Empty-Can Exchange Management & Financial Segregation

AquaFlow enforces strict segregation between **physical container units** and **customer financial currency**:

| Dimension | Physical Container Ledger (`can_movements`) | Financial Accounts Ledger (`customer_ledger`) |
| :--- | :--- | :--- |
| **Unit of Measure** | Integer bottle count (units) | INR Currency (₹) |
| **Primary Table** | `can_movements` & `customer_can_balances` | `customer_ledger` & `customer_profiles` |
| **Normal Delivery** | Full Delivered (+2), Empty Returned (-2) &rarr; Net change: 0 | Cash paid (+₹160), Order charge (-₹160) &rarr; Net change: 0 |
| **Fewer Empties Returned** | Full Delivered (+2), Empty Returned (-0) &rarr; Customer holding increases (+2) | Financial balance unchanged (customer owes bottle, not money yet) |
| **Lost / Broken Can Penalty** | Movement logged: `lost_by_customer` (-1 can holding) | Automated debit entry: ₹150 penalty billed to financial ledger |

---

## 10. Financial Accounts & Razorpay Integration Workflow

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant App as Customer Web Portal
    participant RZP as Razorpay Payment Gateway
    participant WH as Webhook Endpoint (/webhooks/razorpay)
    participant DB as MariaDB Database

    C->>App: Click "Pay Dues Online" or "Pay Order"
    App->>RZP: Create Razorpay Order (amount, currency INR, receipt)
    RZP-->>App: Return razorpay_order_id
    App-->>C: Open Razorpay Checkout Modal (UPI, Cards, Netbanking)
    C->>RZP: Authorize Payment
    
    par Front-End Verification
        RZP-->>App: Submit razorpay_payment_id & signature
        App->>App: Verify HMAC-SHA256 Signature
        App->>DB: Record Payment (status: successful)
        App->>DB: Insert credit entry in `customer_ledger`
        App->>DB: Deduct `customer_profiles.outstanding_balance`
    and Back-End Webhook (Idempotent Safety Net)
        RZP->>WH: POST event payment.captured
        WH->>WH: Verify Webhook Secret Signature
        WH->>DB: Check if payment already processed
        alt Not Processed
            WH->>DB: Record Payment & Credit Ledger
        else Already Captured
            WH-->>RZP: Return HTTP 200 (Ignore Duplicate)
        end
    end
```

---

## 11. Operating Expenses & Business Analytics (P&L) Workflow

```mermaid
flowchart TD
    LogExpense[Log Expense: Fuel, Rent, Salaries, Power, Consumables] --> SaveExpense[(Save to `expenses` with EXP-YYYY-XXXX)]

    subgraph PLCalculations ["P&L Generation Engine (AnalyticsService)"]
        IncomeCalc["1. Total Operating Income = Water Sales + Delivery Fees + Lost Can Penalties"]
        COGSCalc["2. Sourcing COGS = Accepted Supplier Units * Unit Cost (from GRN)"]
        GrossProfit["3. Gross Trading Profit = Total Operating Income - Sourcing COGS"]
        ExpenseSum["4. Total Operating Expenses = Sum of Filtered Expenses by Category"]
        NetProfit["5. Net Operating Profit = Gross Trading Profit - Total Operating Expenses"]
    end

    SaveExpense -.-> PLCalculations

    PLCalculations --> OutputReports{Reports Presentation}
    OutputReports --> PnLView[Printable P&L Statement with window.print]
    OutputReports --> ShrinkageView[Container Fleet & Shrinkage Report]
    OutputReports --> FleetEffView[Driver Completion & Runsheet Scorecards]
    OutputReports --> CSVExports[Native Streaming CSV Exports: Orders, Expenses, Dues]
```

---

## 12. Progressive Web App (PWA) Offline Workflow

```mermaid
flowchart TD
    UserDevice[User Device / Browser] --> ServiceWorker{Service Worker (sw.js)}

    ServiceWorker --> CheckRoute{Route Type}

    CheckRoute -->|Static Asset: CSS, JS, Images| CacheFirst[Cache-First Strategy]
    CacheFirst --> CacheHit{Asset in Cache?}
    CacheHit -- "Yes" --> ServeCache[Serve Immediately from Cache]
    CacheHit -- "No" --> FetchNet[Fetch from Network & Store in Cache]

    CheckRoute -->|Navigation: HTML Page| NetworkFirst[Network-First Strategy]
    NetworkFirst --> TryNetwork{Internet Connected?}
    TryNetwork -- "Connected" --> ServeFreshPage[Render Fresh Dynamic Blade View]
    TryNetwork -- "Offline / Network Fail" --> ServeOfflinePage[Serve Pre-cached /offline Fallback Page]

    CheckRoute -->|Sensitive: Checkout, Payments, Admin| NetworkOnly[Direct Network Only (Never Cached)]
```

---

## 13. Shared Hosting Deployment & Maintenance Workflow

```mermaid
flowchart LR
    subgraph Repo ["Git / Local Workspace"]
        BuildZeroNode["Pre-Compiled Assets in public/assets/ (Zero Node.js)"]
    end

    subgraph cPanel ["cPanel / Shared Hosting Environment"]
        DeployCore["Place Code in /home/user/aquaflow_core/"]
        DeployPublic["Place public/ contents into public_html/"]
        ConfigEnv["Configure .env (DB, Razorpay, Mail, App URL)"]
        ConfigPerms["Set Permissions: chmod -R 775 storage bootstrap/cache"]
        CronSetup["cPanel Cron: * * * * * php artisan schedule:run"]
    end

    BuildZeroNode --> DeployCore
    DeployCore --> DeployPublic
    DeployPublic --> ConfigEnv
    ConfigEnv --> ConfigPerms
    ConfigPerms --> CronSetup
```

### Zero-SSH Maintenance Actions:
Administrators without terminal access can manage production tasks directly from the Admin Console:
- **Storage Symlink Creation**: `POST /admin/utilities/storage-link` executes `Artisan::call('storage:link')`.
- **Cache Invalidation**: `POST /admin/utilities/optimize-clear` executes `Artisan::call('optimize:clear')`.
- **Manual Subscription Runsheet Generation**: `POST /admin/subscriptions/generate-manual` triggers batch dispatch generation for any given date.
