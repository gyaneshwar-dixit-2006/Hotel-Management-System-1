# 🏨 Grand Lux Hotel & Suites - Hotel Management System

A modernized, high-performance, and feature-rich **Hotel Management System** built with **PHP 8+**, **MySQL**, **JavaScript (ES6+)**, and **Bootstrap**.

This project has been upgraded to be **100% fully functional**, secure, and infused with **modern 2026 standout features** like an **AI Virtual Concierge Assistant**, **Interactive Room Visualizer**, **Contactless QR Check-in Passes**, **Digital Invoices**, **In-Room Dining Kitchen Dispatch**, and an **Executive Operations Dashboard**.

---

## 🌟 Unique Standout Features

### 1. 🤖 AI Virtual Concierge Assistant
- An interactive chatbot located in the bottom-right corner of the website.
- Understands natural inquiries in both **English and Hindi/Hinglish** (e.g., *"check in time kya hai?"*, *"best room for couples"*, *"room service menu"*).
- Recommends suites with direct "Book Now" shortcuts and answers queries about amenities, dining, and policies.

### 2. 🛏️ Interactive Suites Visualizer & Direct Booking
- Dynamic suites catalog (`Single`, `Double`, `Deluxe`, `Penthouse`) with high-resolution imagery, sq.ft size, bed configuration, guest capacity, and amenities pills.
- One-click booking with pre-filled room types, automated night count calculation, and dynamic tariffs in INR (₹).

### 3. 🎫 Contactless QR Code Boarding Pass & Smart Keycard
- Every reservation generates a verified **Digital Boarding Pass** featuring a dynamic QR code (powered by `QRCode.js`).
- Guests can scan their keycard at reception for instant express check-in or print it directly.

### 4. 📄 Printable Official Invoices & Receipts
- Itemized guest invoices featuring room tariffs, SGST/CGST tax breakdowns, booking references, guest information, and a 1-click **Print / Save as PDF** action.

### 5. 🍽️ In-Room Dining & Kitchen Order Dispatch
- Comprehensive gourmet culinary menu categorized into Breakfast, Main Course, Beverages, and Desserts.
- Guests can select quantities, specify cooking instructions (e.g. less spice, extra cutlery), and order directly to their suite.
- Kitchen staff & Admins can track orders in real-time and transition statuses from `Placed` &rarr; `Preparing` &rarr; `Delivered`.

### 6. ⭐ Verified Guest Reviews & Ratings
- Public aggregate rating counter (4.9/5 stars) and verified stay testimonials.
- Authenticated guests can submit ratings and reviews through an interactive modal.

### 7. ⚡ Executive Operations Command Center (`admin.php`)
- **Strict Role-Based Access Control** (unauthorized users are immediately redirected).
- **Metric KPI Cards**: Live Total Revenue, Total Reservations (Pending / Confirmed / Cancelled), Suite Occupancy, and Customer Count.
- **Manage Reservations**: Filter bookings by status, bulk confirm/cancel, or single-click status updates (`Confirmed`, `Checked In`, `Checked Out`, `Cancelled`).
- **Rooms Inventory Manager**: Monitor and switch suite availability (`Available`, `Occupied`, `Maintenance`).
- **Kitchen Dispatch**: Manage live food orders.
- **Visual Analytics**: Interactive `Chart.js` graphs depicting reservations by room category and status distribution.

---

## 🔑 Demo Access Credentials

| Role | Email | Password | Access Level |
|---|---|---|---|
| 👑 **Administrator** | `admin@hotel.com` | `admin123` | Full Operations Dashboard (`admin.php`) |
| 👑 **Administrator (Alt)** | `admin@admin.com` | `admin123` | Full Operations Dashboard (`admin.php`) |
| 🏨 **Guest Customer** | `guest@hotel.com` | `guest123` | Guest Portal, Booking, QR Keycard, Room Service |

*Tip: The Sign-In page (`sign-in.php`) includes **1-Click Demo Fill buttons** to test both accounts instantly.*

---

## 🛠️ Quick Setup & Running

### Requirements
- **PHP 8.0+** with `pdo_mysql` extension enabled
- **MySQL 5.7+ / 8.0+** or MariaDB

### Steps
1. **Database Setup**:
   - Ensure your MySQL service is running.
   - Run the initial database import and migrations:
     ```bash
     mysql -u root -p hotel < hotel.sql
     mysql -u root -p hotel < db/migrate.sql
     php -c php.ini db/seed_demo_accounts.php
     ```

2. **Start the Web Application**:
   - **Windows 1-Click**: Double-click `start.bat`
   - **Or via Terminal**:
     ```bash
     php -c php.ini -S 127.0.0.1:8000
     ```

3. **Open in Browser**:
   - **Guest Website**: [http://127.0.0.1:8000/index.php](http://127.0.0.1:8000/index.php)
   - **Sign In**: [http://127.0.0.1:8000/sign-in.php](http://127.0.0.1:8000/sign-in.php)
   - **Admin Operations**: [http://127.0.0.1:8000/admin.php](http://127.0.0.1:8000/admin.php)

---

## 🐛 Core Issues Identified & Fixed in Original Code

1. **Authentication Logic Flaw**:
   - `CustomerHandler::isPasswordMatchWithEmail()` returned string literals (`'Password is valid!'` or `'Invalid password.'`). In PHP, non-empty strings are truthy, causing invalid passwords to pass validation or fail silently. Refactored to return true booleans and safely handle unregistered emails without runtime fatal errors.
2. **Missing `pricing` Table**:
   - `BookingReservationDAO.php` inserted into a `pricing` table that was completely absent from `hotel.sql`. Created and migrated the table with backward-compatible seed data.
3. **Admin Security Bypass**:
   - `admin.php` contained hardcoded session variables (`$_SESSION["username"] = "admin"; $_SESSION["isAdmin"] = 1;`), making any direct visitor an admin. Replaced with strict session authentication.
4. **PHP 8+ Array Key Warnings**:
   - `index.php` accessed `$isAdmin[1]` which threw `Undefined array key 1` warnings on PHP 8+ when normal users logged in, rendering reservation modals unopenable. Fixed across all handlers.
5. **Cookie Dependencies on Server State**:
   - `process_update_profile.php` and `index.php` relied on client cookies (`is_admin`) to authorize profile updates and view bookings. Migrated authorization checks to secure server sessions.
6. **Broken `sign-in.php`**:
   - Replaced a corrupted external script that connected to a nonexistent `hotel_management` database with a modern, glassmorphic login screen supporting 1-click demo fills.

---

## 📁 Project Architecture

```
Hotel-Management-System/
├── app/
│   ├── admin/
│   │   └── manage_reservation.php   # Admin action controller
│   ├── api/
│   │   ├── admin_ops.php            # Analytics & suite status API
│   │   ├── concierge.php            # AI Virtual Concierge Chatbot API
│   │   ├── reviews.php              # Reviews submission & listing API
│   │   └── room_service.php         # Food ordering & kitchen dispatch API
│   ├── dao/                         # Data Access Objects (CustomerDAO, BookingDetailDAO, etc.)
│   ├── handlers/                    # Business Logic Handlers
│   ├── models/                      # OOP Data Models (Customer, Booking, Reservation, Pricing)
│   ├── DB.php                       # Resilient PDO Database Singleton with fallbacks
│   └── Util.php                     # Sanitization, alerts, and formatting utilities
├── css/
│   └── main.css                     # Luxury hotel custom theme & animations
├── db/
│   ├── migrate.sql                  # Schema migration for pricing, rooms, orders, reviews
│   └── seed_demo_accounts.php       # Demo accounts seeder
├── js/
│   ├── admin.js                     # Admin Operations controller
│   ├── form-submission.js           # AJAX form dispatcher
│   ├── multiStepsRsvn.js            # Multi-step booking calculator & wizard
│   └── utilityFunctions.js          # Client-side utility functions
├── admin.php                        # Executive Operations Dashboard
├── hotel.sql                        # Base SQL schema
├── index.php                        # Luxury Homepage & Guest Portal
├── php.ini                          # Optimized local PHP configuration
├── register.php                     # Account registration page
├── sign-in.php                      # Modern authentication page with 1-click demo fill
└── start.bat                        # 1-click startup batch script
```

---
*Created with ❤️ for hospitality excellence.*
