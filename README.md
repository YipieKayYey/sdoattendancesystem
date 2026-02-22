<p align="center">
    <img src="public/images/sdologo.png" width="200" alt="SDO Logo">
    <img src="public/images/depedlogo.png" width="200" alt="DepEd Logo">
</p>

<p align="center">
    <strong>SDO Seminar Management System</strong><br>
    A comprehensive web application for managing seminar registrations and attendance tracking for Schools Division Office (SDO)
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Version-0.7-blue" alt="Version">
    <img src="https://img.shields.io/badge/PHP-8.2%2B-blue" alt="PHP">
    <img src="https://img.shields.io/badge/Laravel-12.0-red" alt="Laravel">
    <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

## 📋 Overview

The SDO Seminar Management System is a feature-rich web application designed to streamline seminar organization, registration, and attendance tracking for educational institutions. Built with modern web technologies, it provides a complete solution for managing both single-day and multi-day seminars with comprehensive reporting capabilities.

## 🖼️ Application Preview

![SDO Landing Background](public/images/SdoLandingBg.png)

*Main landing page for seminar registration and management*

## ✨ Key Features

### 🎯 Seminar Management
- **Multi-day seminar support** with individual day tracking
- **Open and closed seminar types** with capacity management
- **Comprehensive seminar information** including venue, time, room details
- **Seminar status tracking** (active, ended, archived)
- **Slug-based URLs** for easy pre-registration access

### 📝 Registration System
- **Online registration form** with multi-step process
- **Digital signature capture** with security features
- **PRC license validation** for teaching personnel
- **Comprehensive attendee data collection** (personal info, position, school/agency)
- **QR code-based ticketing system** with unique ticket hashes
- **Registration capacity management** with real-time availability

### 📊 Attendance Tracking
- **Check-in/check-out functionality** per seminar day
- **Multi-day attendance tracking** with individual day records
- **Real-time attendance monitoring** via Filament admin panel
- **Attendance analytics** and reporting

### 📈 Reporting & Analytics
- **PDF report generation** for registration, attendance, and GNR attendance sheets
- **CPD-compliant formats** (Registration Sheet, Attendance Sheet) with PRC branding
- **GNR Attendance Sheet** for simplified name/sex/position/office/signature export
- **CSV export** for attendance data analysis
- **Analytics dashboard** with multi-day statistics and PDF reports
- **Signature images** in exports (no watermark overlay)

### 🛡️ Security & Administration
- **Filament v3.2 admin panel** for modern UI/UX
- **Signature security service** with hash validation and consent tracking
- **Role-based access control** with authentication middleware
- **School management** for attendee school/office assignment

## 🛠️ Technical Stack

### Backend
- **Framework**: Laravel 12.0 with PHP 8.2+
- **Database**: SQLite (default) or MySQL with Eloquent ORM
- **Admin Panel**: Filament v3.2
- **Queue System**: Laravel Queues for background processing

### Frontend
- **UI Framework**: TailwindCSS v4.0
- **JavaScript**: Vite 7.0 for asset compilation
- **Components**: Livewire for dynamic interactions
- **Icons**: Heroicons for consistent iconography

### PDF & Document Processing
- **PDF Generation**: DomPDF (Barryvdh)
- **Barcode/QR**: Milon/Barcode (DNS2D) for ticket QR codes

### Development Tools
- **Testing**: PHPUnit with custom test suites
- **Code Style**: Laravel Pint for formatting
- **Package Management**: Composer and npm

## 📁 Project Structure

```
app/
├── Models/                 # Eloquent models
│   ├── Seminar.php         # Seminar management
│   ├── Attendee.php        # Registration data
│   ├── SeminarDay.php      # Multi-day tracking
│   ├── AttendeeCheckIn.php # Attendance records
│   └── School.php          # School/office reference
├── Services/               # Business logic services
│   ├── SignatureSecurityService.php
│   ├── RegistrationSheetPdfService.php
│   ├── AttendanceSheetPdfService.php
│   ├── AttendanceSqlParser.php    # SQL dump parser for seeding
│   ├── AnalyticsPdfService.php
│   ├── AnalyticsCsvService.php
│   └── SeminarAnalyticsService.php
├── Filament/Admin/         # Admin panel resources
├── Livewire/              # Dynamic components
└── Http/Controllers/      # HTTP controllers
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and npm
- SQLite (or other supported database)

### Quick Start
```bash
# Clone the repository
git clone <repository-url>
cd sdo-seminar-management-system

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed   # Optional: seed seminars, schools, sample data

# Build assets
npm run build

# Start development server
composer run dev
```

### Available Scripts
- `composer run setup` - Complete project setup
- `composer run dev` - Development server with hot reload
- `composer run test` - Run test suite

## 📚 Usage Documentation

### For Administrators
1. Access admin panel at `/admin`
2. Create and manage seminars through Filament interface
3. Monitor registrations and attendance in real-time
4. Export reports in PDF or CSV format
5. Manage multi-day seminar schedules

### For Attendees
1. Visit seminar registration URL (slug-based)
2. Complete multi-step registration form
3. Provide digital signature for consent
4. Receive QR code ticket for check-in
5. Check in/out at seminar venue

## 🔧 Configuration

### Environment Variables
Key configuration options in `.env`:
- Database connection settings
- Mail configuration for notifications
- File system settings for uploads
- Application URL and timezone

### Seeding
- **SeminarDataSeeder** – Seminars, MANCOM attendees, Division Workshop (from SQL dump)
- **AttendanceSqlParser** – Parses `database/u266949284_sdoattendance.sql` for real attendee data
- **SchoolSeeder** – School/office reference data
- **AttendeeSeeder** – Sample attendees (skips MANCOM and Division Workshop)

## 🤝 Contributing

Thank you for considering contributing to the SDO Seminar Management System! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

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
