<p align="center">
    <img src="public/images/sdologo.png" width="200" alt="SDO Logo">
    <img src="public/images/depedlogo.png" width="200" alt="DepEd Logo">
</p>

<p align="center">
    <strong>SDO Seminar Management System</strong><br>
    A comprehensive web application for managing seminar registrations and attendance tracking for Schools Division Office (SDO)
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Version-0.8-blue" alt="Version">
    <img src="https://img.shields.io/badge/PHP-8.2%2B-blue" alt="PHP">
    <img src="https://img.shields.io/badge/Laravel-12.0-red" alt="Laravel">
    <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

## 📋 Overview

The SDO Seminar Management System is a feature-rich web application designed to streamline seminar organization, registration, and attendance tracking for educational institutions. Built with modern web technologies, it provides a complete solution for managing both single-day and multi-day seminars with comprehensive reporting capabilities.

The system supports three user types:
- **Administrators**: Full access to manage seminars, attendees, check-ins, and generate reports
- **Registered Attendees**: Access to personal dashboard, Universal QR code, and seminar history
- **Guest Users**: Public registration without account creation

### Recent Updates (v0.8)

- **Universal QR Check-In** – Registered attendees use one QR for all seminars; walk-in scan auto-registers and checks in
- **Attendee Panel** – Dashboard with Seminar History, Universal QR widget, and Seminars Attended stats
- **Account Linking** – Logged-in attendees who pre-register get `user_id` set; seminars appear in History and stats
- **Double Registration Prevention** – Logged-in attendees cannot register twice for the same seminar
- **Profile Completion Gate** – Universal QR code only appears when profile is complete (name, contact, school, PRC, signature)

## 🖼️ Application Preview

![SDO Landing Background](public/images/SdoLandingBg.png)

*Main landing page for seminar registration and management*

## ✨ Key Features

### 🎯 Seminar Management
- **Multi-day seminar support** with individual day tracking via `SeminarDay` model
- **Open and closed seminar types** with capacity management
- **Comprehensive seminar information** including venue, time, room details
- **Seminar status tracking** (active, ended, archived with soft deletes)
- **Slug-based URLs** for easy pre-registration access (8-character random hash)
- **Survey link tracking** with click analytics

### 📝 Registration System
- **Online registration form** with multi-step Livewire component (`RegisterAttendee`)
- **Digital signature capture** with security features via `SignatureSecurityService`
- **PRC license validation** for teaching personnel (7-digit numeric, future expiry date)
- **Comprehensive attendee data collection** (personal info, position, school/agency)
- **QR code-based ticketing system** with unique 16-character ticket hashes
- **Registration capacity management** with real-time availability checking
- **Guest and registered attendee support** – logged-in attendees get `user_id` linked for Seminar History and stats
- **Double registration prevention** for logged-in attendees (checks `user_id` + `seminar_id`)
- **Email uniqueness per seminar** (prevents duplicate registrations by email)

### 📊 Attendance Tracking
- **Check-in/check-out functionality** per seminar day via `AttendeeCheckIn` model
- **Multi-day attendance tracking** with individual day records
- **Real-time attendance monitoring** via Filament admin panel
- **Universal QR check-in** – registered attendees can walk in and scan their Universal QR (auto-register + check-in)
- **Dual lookup** – supports both Universal QR hash (`AttendeeProfile`) and per-seminar ticket hash (`Attendee`)
- **Walk-in registration** – scanning Universal QR at check-in creates `Attendee` record from `AttendeeProfile`
- **Attendance analytics** and reporting with multi-day statistics

### 📈 Reporting & Analytics
- **PDF report generation** for registration, attendance, and GNR attendance sheets
- **CPD-compliant formats** (Registration Sheet, Attendance Sheet) with PRC branding
- **GNR Attendance Sheet** for simplified name/sex/position/office/signature export
- **CSV export** for attendance data analysis
- **Analytics dashboard** with multi-day statistics and PDF reports
- **Signature images** in exports (no watermark overlay)
- **Per-day filtering** for multi-day seminars

### 👤 Attendee Panel (`/attendee`)
- **Attendee dashboard** with login authentication
- **Universal QR Code** – one QR for all seminars (16-character hash, shown when checking in)
- **Profile Completion Gate** – QR code only appears when profile is complete:
  - Full name (first, middle, last)
  - Contact info (personnel type, sex, mobile phone, position)
  - School/Office/Agency
  - PRC license (if teaching personnel) or explicit "no license"
  - Digital signature
- **Seminar History** – table of attended seminars with View modal (check-in/check-out times per day)
- **Seminars Attended** stats widget
- **Edit Profile** – update personal info and signature (used for walk-in check-ins)
- **My Profile** – view profile details
- **Email management** – attendees can update their email address

### 🛡️ Security & Administration (`/admin`)
- **Filament v3.2 admin panel** for modern UI/UX
- **Role-based access control** – `User` model with `role` field (`admin` or `attendee`)
- **Panel access control** – `User::canAccessPanel()` routes admins to `/admin`, attendees to `/attendee`
- **Signature security service** with hash validation and consent tracking
- **School management** for attendee school/office assignment
- **User management**:
  - **Create**: Account only (name, email, password, role); creates empty `AttendeeProfile` for attendees
  - **Edit**: Account + Attendee Profile (editable up to PRC license); signature read-only (attendee provides in dashboard)
  - **View**: Same fields, read-only
- **Seminar resources** – full CRUD for seminars, attendees, check-ins
- **Archived seminars** – soft-deleted seminars accessible via separate resource

## 🛠️ Technical Stack

### Backend
- **Framework**: Laravel 12.0 with PHP 8.2+
- **Database**: SQLite (default) or MySQL with Eloquent ORM
- **Admin Panel**: Filament v3.2 (dual panels: Admin and Attendee)
- **Queue System**: Laravel Queues (database driver) for background processing
- **PDF Generation**:
  - DomPDF (Barryvdh) for registration/attendance sheets
  - mPDF for advanced PDF features
  - PDFtk for form filling
- **Barcode/QR**: Milon/Barcode (DNS2D) for ticket QR codes

### Frontend
- **UI Framework**: TailwindCSS v4.0
- **JavaScript**: Vite 7.0 for asset compilation
- **Components**: Livewire for dynamic interactions (public registration form)
- **Icons**: Heroicons for consistent iconography
- **Admin UI**: Filament components and widgets

### Development Tools
- **Testing**: PHPUnit with custom test suites
- **Code Style**: Laravel Pint for formatting
- **Package Management**: Composer and npm
- **Logging**: Laravel Pail for real-time log viewing

## 📁 Project Structure

```
app/
├── Filament/
│   ├── Admin/                    # Admin panel resources and pages
│   │   ├── Pages/
│   │   │   ├── CheckInAttendee.php      # QR scanner check-in page
│   │   │   ├── CheckOutAttendee.php    # QR scanner check-out page
│   │   │   └── Auth/
│   │   │       └── AdminLogin.php      # Custom admin login
│   │   ├── Resources/
│   │   │   ├── SeminarResource.php     # Seminar CRUD
│   │   │   ├── UserResource.php        # User management
│   │   │   ├── SchoolResource.php      # School management
│   │   │   ├── AnalyticsResource.php   # Analytics dashboard
│   │   │   └── ArchivedSeminarResource.php
│   │   └── Widgets/
│   │       └── SeminarStatsWidget.php
│   └── Attendee/                 # Attendee panel pages
│       ├── Pages/
│       │   ├── Dashboard.php           # Attendee dashboard
│       │   ├── UniversalQR.php         # Universal QR display
│       │   ├── SeminarHistory.php      # Seminar history table
│       │   ├── EditProfile.php         # Profile editor
│       │   ├── ViewProfile.php         # Profile viewer
│       │   └── Auth/
│       │       ├── AttendeeLogin.php   # Custom attendee login
│       │       └── AttendeeEditProfile.php
│       └── Widgets/
│           ├── UniversalQRWidget.php   # Dashboard QR widget
│           └── AttendeeStatsWidget.php # Seminars attended count
├── Http/
│   └── Controllers/
│       └── RegistrationDetailsController.php  # PDF preview/download
├── Livewire/
│   └── RegisterAttendee.php     # Public registration form (multi-step)
├── Models/                       # Eloquent models
│   ├── User.php                  # Authentication (email, password, role)
│   ├── Attendee.php              # Registration data (user_id links to User)
│   ├── AttendeeProfile.php       # Universal profile for registered attendees
│   ├── Seminar.php               # Seminar management
│   ├── SeminarDay.php            # Multi-day tracking
│   ├── AttendeeCheckIn.php       # Attendance records (per day)
│   ├── School.php                # School/office reference
│   └── SurveyLinkClick.php       # Survey click tracking
├── Providers/
│   └── Filament/
│       ├── AdminPanelProvider.php    # Admin panel configuration
│       └── AttendeePanelProvider.php # Attendee panel configuration
└── Services/                    # Business logic services
    ├── SignatureSecurityService.php      # Signature processing & validation
    ├── RegistrationSheetPdfService.php   # Registration sheet PDF
    ├── MpdfRegistrationSheetService.php # mPDF-based registration sheet
    ├── AttendanceSheetPdfService.php     # Attendance sheet PDF
    ├── AttendanceCsvService.php         # CSV export
    ├── AnalyticsPdfService.php           # Analytics PDF report
    ├── AnalyticsCsvService.php           # Analytics CSV export
    ├── SeminarAnalyticsService.php       # Analytics calculations
    ├── SeminarQrCodeService.php         # Seminar registration QR
    ├── RegistrationDetailsPdfService.php # Registration details PDF
    ├── AttendanceSqlParser.php          # SQL dump parser for seeding
    └── PdfFormFillingService.php        # PDF form filling

config/
├── filament.php                 # Filament panel configuration
├── auth.php                     # Authentication guards
├── database.php                 # Database connections
├── mail.php                     # Email configuration
├── queue.php                    # Queue configuration
└── filesystems.php              # File storage configuration

database/
├── migrations/                  # Database migrations (see key migrations)
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 2026_01_22_061436_create_seminars_table.php
│   ├── 2026_01_22_061439_create_attendees_table.php
│   ├── 2026_02_02_093746_create_seminar_days_table.php
│   ├── 2026_02_02_093750_create_attendee_check_ins_table.php
│   ├── 2026_02_20_103836_create_schools_table.php
│   ├── 2026_02_20_103843_add_school_fields_to_attendees_table.php
│   ├── 2026_02_21_100000_add_role_to_users_table.php
│   ├── 2026_02_21_100001_create_attendee_profiles_table.php
│   └── 2026_02_21_100002_add_user_id_to_attendees_table.php
└── seeders/
    ├── DatabaseSeeder.php       # Main seeder
    ├── SchoolSeeder.php         # School reference data
    ├── SeminarSeeder.php        # Sample seminars
    ├── SeminarDataSeeder.php    # Seminars + MANCOM attendees
    ├── AttendeeSeeder.php       # Sample attendees
    ├── AnalyticsTestSeminarSeeder.php   # Test data for analytics
    ├── EnsureSeminarDaysSeeder.php     # Ensure Day 1 records exist
    └── TestUserSeeder.php       # Test users (admin@test.local, attendee@test.local)

resources/
├── views/
│   ├── welcome.blade.php        # Landing page
│   ├── livewire/
│   │   └── register-attendee.blade.php  # Registration form
│   ├── registration-success.blade.php   # Success page with QR
│   ├── pdf/                     # PDF templates
│   │   ├── registration-details.blade.php
│   │   └── ticket-pdf.blade.php
│   └── filament/                # Filament views
│       ├── admin/               # Admin panel views
│       └── attendee/            # Attendee panel views

routes/
└── web.php                      # Application routes
    ├── /                        # Landing page
    ├── /register/{slug}         # Public registration
    ├── /survey/{slug}            # Survey redirect with tracking
    ├── /registration/success/{ticket_hash}  # Success page
    ├── /admin/*                 # Admin panel (Filament)
    └── /attendee/*              # Attendee panel (Filament)
```

## 🗄️ Database Models & Relationships

### Core Models

**User** (`users` table)
- `id`, `name`, `email`, `password`, `role` (`admin` or `attendee`)
- Relationships:
  - `hasOne(AttendeeProfile)` – Universal profile for attendees
  - `hasMany(Attendee)` – Seminar registrations

**AttendeeProfile** (`attendee_profiles` table)
- `id`, `user_id` (unique), `universal_qr_hash` (16-char, unique)
- Profile fields: `personnel_type`, `first_name`, `middle_name`, `last_name`, `suffix`, `sex`, `school_id`, `school_office_agency`, `mobile_phone`, `position`, `prc_license_no`, `prc_license_expiry`, signature fields
- Relationships:
  - `belongsTo(User)` – Linked user account
  - `belongsTo(School)` – Optional school reference
- Methods:
  - `isComplete()` – Checks if profile has all required fields + signature
  - `findByUniversalQrHash($hash)` – Static lookup for check-in

**Attendee** (`attendees` table)
- `id`, `seminar_id`, `user_id` (nullable), `email`, `ticket_hash` (16-char, unique)
- Registration fields: same as `AttendeeProfile` plus `name` (computed)
- Relationships:
  - `belongsTo(Seminar)` – Seminar registration
  - `belongsTo(User)` – Optional linked user account
  - `belongsTo(School)` – Optional school reference
  - `hasMany(AttendeeCheckIn)` – Attendance records per day

**Seminar** (`seminars` table)
- `id`, `title`, `slug` (8-char, unique), `date`, `is_open`, `capacity`, `venue`, `topic`, `time`, `room`, `survey_form_url`, `is_multi_day`, `is_ended`, `deleted_at` (soft deletes)
- Relationships:
  - `hasMany(Attendee)` – Registered attendees
  - `hasMany(SeminarDay)` – Multi-day schedule
  - `hasMany(SurveyLinkClick)` – Survey click tracking

**SeminarDay** (`seminar_days` table)
- `id`, `seminar_id`, `day_number`, `date`, `start_time`, `venue`, `topic`, `room`
- Relationships:
  - `belongsTo(Seminar)` – Parent seminar
  - `hasMany(AttendeeCheckIn)` – Check-ins for this day

**AttendeeCheckIn** (`attendee_check_ins` table)
- `id`, `attendee_id`, `seminar_day_id`, `checked_in_at`, `checked_out_at`
- Relationships:
  - `belongsTo(Attendee)` – Attendee record
  - `belongsTo(SeminarDay)` – Specific day

**School** (`schools` table)
- `id`, `name`
- Relationships:
  - `hasMany(Attendee)` – Attendees from this school
  - `hasMany(AttendeeProfile)` – Profiles linked to this school

### Key Relationships

```
User (1) ──< (1) AttendeeProfile (universal profile)
User (1) ──< (*) Attendee (seminar registrations)
Seminar (1) ──< (*) Attendee
Seminar (1) ──< (*) SeminarDay
Attendee (1) ──< (*) AttendeeCheckIn
SeminarDay (1) ──< (*) AttendeeCheckIn
School (1) ──< (*) Attendee
School (1) ──< (*) AttendeeProfile
```

## 👥 User Roles & Flows

### Admin Role (`role = 'admin'`)
- **Access**: `/admin` panel only
- **Capabilities**:
  - Create/edit/delete seminars
  - Manage users (create admin/attendee accounts)
  - Manage schools
  - View all attendees and registrations
  - Check-in/check-out attendees via QR scanner
  - Generate PDF/CSV reports
  - View analytics dashboards
  - Archive seminars (soft delete)

### Attendee Role (`role = 'attendee'`)
- **Access**: `/attendee` panel only
- **Capabilities**:
  - View Universal QR code (when profile complete)
  - Edit profile (name, contact, school, signature)
  - View seminar history (seminars where `user_id` matches)
  - View attendance stats
  - Pre-register for seminars (via public form while logged in)
  - Update email address

**Registration Flow for Logged-in Attendees:**
1. Visit `/register/{slug}` while logged in
2. Form pre-fills with profile data (if available)
3. Submit registration → `Attendee` created with `user_id` set
4. Seminar appears in Seminar History
5. Can use Universal QR for walk-in check-in

**Walk-in Check-in Flow:**
1. Attendee scans Universal QR at venue
2. System looks up `AttendeeProfile` by `universal_qr_hash`
3. If `Attendee` record doesn't exist for this seminar, creates one from profile
4. Checks in attendee for current/selected day

### Guest Users (No Account)
- **Access**: Public registration form only
- **Flow**:
  1. Visit `/register/{slug}` (not logged in)
  2. Complete multi-step registration form
  3. Provide email (validated for uniqueness per seminar)
  4. Draw signature
  5. Receive ticket QR code (`ticket_hash`)
  6. Check in using ticket QR at venue
  7. No dashboard access (no account created)

## 🔐 Profile Completion Gate & Universal QR

The Universal QR code is only displayed when the `AttendeeProfile` is complete. The `isComplete()` method checks:

1. **Name**: `first_name`, `middle_name`, `last_name` must all be filled
2. **Contact**: `personnel_type`, `sex`, `mobile_phone`, `position` must be filled
3. **School**: Either `school_id` is set OR `school_office_agency`/`school_other` is filled
4. **PRC License**:
   - If `prc_license_no` is provided, `prc_license_expiry` must also be provided
   - If empty, considered valid (no license)
5. **Signature**: `signature_image` or `signature_upload_path` must exist

**UI Behavior:**
- Universal QR widget/page shows completion instructions if incomplete
- Link to Edit Profile page provided
- QR code only renders when `isComplete()` returns `true`

## 📧 Email Logic: User vs Attendee

The system uses email differently in two contexts:

### User Model (`users` table)
- **Purpose**: Authentication and account management
- **Usage**: Login credentials for `/admin` and `/attendee` panels
- **Uniqueness**: Enforced at database level (unique constraint)
- **Editable**: Attendees can update their email in Edit Profile
- **Admin Creation**: Admins create users with email + password + role

### Attendee Model (`attendees` table)
- **Purpose**: Registration data for seminars
- **Usage**: Contact information, uniqueness check per seminar
- **Uniqueness**: Enforced per seminar (same email can register for different seminars)
- **Validation**: Email format validation, uniqueness check in `RegisterAttendee` component
- **Relationship**: When `user_id` is set, `Attendee.email` may differ from `User.email`

**Key Points:**
- A `User` account email is for login only
- An `Attendee` email is for registration/contact
- When creating `Attendee` from `AttendeeProfile` (walk-in), `User.email` is used
- When logged-in attendee registers, `Attendee.email` comes from form (can differ from `User.email`)

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite (default) or MySQL/PostgreSQL
- Web server (Apache/Nginx) or PHP built-in server

### Quick Start

```bash
# Clone the repository
git clone <repository-url>
cd sdo-seminar-management-system

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env
# For SQLite (default):
# DB_CONNECTION=sqlite
# (database/database.sqlite will be created automatically)

# For MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed database (optional - adds sample data)
php artisan db:seed

# Build assets
npm run build

# Start development server
composer run dev
# This runs concurrently:
# - PHP server (http://localhost:8000)
# - Queue worker
# - Laravel Pail (logs)
# - Vite dev server (hot reload)
```

### Available Scripts

- `composer run setup` – Complete project setup (install, env, key, migrate, build)
- `composer run dev` – Development server with hot reload (server + queue + logs + vite)
- `composer run test` – Run PHPUnit test suite
- `npm run build` – Build production assets
- `npm run dev` – Start Vite dev server

### Environment Configuration

Key variables in `.env`:

```env
# Application
APP_NAME="SDO Seminar Management System"
APP_ENV=local
APP_KEY=                    # Generated by php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite        # or mysql, pgsql
DB_DATABASE=database/database.sqlite  # for SQLite
# DB_HOST=127.0.0.1         # for MySQL
# DB_PORT=3306
# DB_USERNAME=root
# DB_PASSWORD=

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database   # Uses database queue driver

# Mail (for notifications - currently not used but configured)
MAIL_MAILER=log             # Use 'smtp' for production
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Cache
CACHE_STORE=database
```

### Database Seeding

The seeder system includes:

- **SchoolSeeder** – School/office reference data (alphabetical list)
- **SeminarSeeder** – Sample seminars with multi-day support
- **SeminarDataSeeder** – Real seminars + MANCOM attendees (from SQL dump)
- **AttendeeSeeder** – Sample attendees (skips MANCOM and Division Workshop)
- **EnsureSeminarDaysSeeder** – Ensures all seminars have Day 1 records
- **AnalyticsTestSeminarSeeder** – Multi-day seminar with attendees and check-ins for analytics testing
- **TestUserSeeder** – Test accounts:
  - `admin@test.local` / `password` (admin role)
  - `attendee@test.local` / `password` (attendee role)
- **AttendanceSqlParser** – Parses `database/u266949284_sdoattendance.sql` for real attendee data (used by SeminarDataSeeder)

Run all seeders:
```bash
php artisan db:seed
```

Run specific seeder:
```bash
php artisan db:seed --class=TestUserSeeder
```

## 📚 Usage Documentation

### For Administrators

1. **Access Admin Panel**: Navigate to `/admin` and log in with admin credentials
2. **Create Seminars**:
   - Go to Seminars → Create
   - Fill in title, date, venue, capacity (or mark as open)
   - Enable multi-day if needed, add seminar days
   - Copy registration URL (`/register/{slug}`) to share
3. **Manage Users**:
   - Create admin or attendee accounts (account fields only)
   - For attendees, empty AttendeeProfile is auto-created
   - Edit to fill in profile up to PRC license (signature added by attendee)
4. **Check-in Attendees**:
   - Go to Seminars → View → Check In
   - Scan QR code (Universal QR or ticket hash)
   - Select day for multi-day seminars
   - Check out when attendee leaves
5. **Generate Reports**:
   - View Analytics for statistics
   - Export Registration Sheet (PDF)
   - Export Attendance Sheet (PDF)
   - Export GNR Attendance Sheet (PDF)
   - Export CSV for data analysis
6. **Archive Seminars**: Move ended seminars to Archived Seminars

### For Attendees

**Guest Registration (No Account):**
1. Visit seminar registration URL (`/register/{slug}`)
2. Complete Step 1: Personal info, email, school
3. Complete Step 2: Contact info, PRC license (if teaching), signature
4. Receive QR code ticket on success page
5. Check in/out at seminar venue using ticket QR

**Registered Attendees (With Account):**
1. **Login**: Navigate to `/attendee` and log in
2. **Complete Profile** (required for Universal QR):
   - Go to My Profile → Edit Profile
   - Fill all fields (name, contact, school, PRC if teaching)
   - Draw signature and capture
   - Save profile
3. **View Universal QR**:
   - Dashboard widget or My QR Code page
   - Show this QR when checking in at any seminar
4. **Pre-register for Seminars**:
   - Visit `/register/{slug}` while logged in
   - Form pre-fills with profile data
   - Submit → seminar appears in Seminar History
5. **Walk-in Check-in**:
   - Show Universal QR at venue
   - System auto-registers and checks you in
   - Seminar appears in History
6. **View Seminar History**:
   - See all attended seminars
   - Click View to see check-in/check-out times per day
7. **Update Profile**: Edit any profile fields or signature anytime

## 🔧 Important Configuration

### Filament Panels

Two separate Filament panels are configured:

**Admin Panel** (`/admin`):
- Path: `admin`
- Login: `App\Filament\Pages\Auth\AdminLogin`
- Access: Users with `role = 'admin'`
- Resources: Seminars, Users, Schools, Analytics, Archived Seminars
- Pages: Dashboard, Check In, Check Out

**Attendee Panel** (`/attendee`):
- Path: `attendee`
- Login: `App\Filament\Pages\Auth\AttendeeLogin`
- Access: Users with `role = 'attendee'`
- Pages: Dashboard, Universal QR, Seminar History, Edit Profile, View Profile
- Widgets: Universal QR Widget, Attendee Stats Widget

Panel access is controlled by `User::canAccessPanel()` method.

### Rate Limiting

Rate limiters are configured in `AppServiceProvider`:

- **Registration** (`throttle:registration`): 15 requests per minute per IP
- **Exports** (`throttle:exports`): 30 requests per minute per user (or IP if unauthenticated)

### File Storage

Signature images are stored in:
- `storage/app/signatures/` (local disk)
- Paths stored in `signature_upload_path` field
- Base64 images stored in `signature_image` field

### Queue Configuration

- Driver: `database` (uses `jobs` table)
- Run queue worker: `php artisan queue:work`
- Or use `composer run dev` which includes queue listener

### Session Configuration

- Driver: `database` (uses `sessions` table)
- Lifetime: 120 minutes
- Encrypted: false (can enable in production)

## 🤝 Contributing

Thank you for considering contributing to the SDO Seminar Management System! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`composer run test`)
5. Format code (`./vendor/bin/pint`)
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the SDO IT department
- Review the changelog for recent updates

---

<p align="center">
    <img src="public/images/BagongPilipinasLogo.png" width="150" alt="Bagong Pilipinas">
    <img src="public/images/Philippinequal.png" width="150" alt="Philippine Quality">
</p>

**Built with ❤️ for the Schools Division Office**

**Department of Education - Philippines**
