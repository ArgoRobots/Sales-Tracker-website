# Argo Sales Tracker Website
## Introduction
This is the website for [Argo Sales Tracker](https://github.com/ArgoRobots/Sales-Tracker), a WinForms application designed to track the sales and purchases of products. This website serves as a platform for users to download the software, purchase license keys, access documentation, and has an administrative system for managing licenses, user accounts, and analytics.

## Technologies used
### Frontend:
- **HTML5/CSS3**: Structure and styling
- **JavaScript/jQuery**: Interactive elements and dynamic content loading
- **Chart.js**: Data visualization for analytics dashboard

### Backend:
- **PHP**: Server-side processing
- **MySQL**: Database for storing licenses, user accounts, and analytics data
- **Two-factor authentication (TOTP)**: Enhanced security for admin access

## Core Features
### Public Website
- Product information and marketing pages
- Free version download
- License key purchase system
- Comprehensive documentation
- Support/contact system
- About us and legal information

### Admin System
- Secure admin dashboard with two-factor authentication
- License key generation and management
- User account administration
- Statistics tracking and analytics dashboard

## Security Features
- Password hashing and secure storage
- Two-factor authentication for admin access
- Session management and protection
- Rate limiting for login attempts
- Input sanitization and validation

## Installation
### Step 1: Install PHP
1. Download the latest version of PHP from [https://windows.php.net/download](https://windows.php.net/download).
2. Choose the "Non Thread Safe" `.zip` version for your system (x64 or x86).
3. Extract the contents to a folder, for example: `C:\php`
4. Add `C:\php` to your system's `PATH` environment variable:
   - Open Start menu and search for "Environment Variables"
   - Edit the `Path` variable and add `C:\php`
5. Open Command Prompt and run `php -v` to make sure PHP is installed correctly.

### Step 2: Install Composer
1. Download and install Composer from [https://getcomposer.org/](https://getcomposer.org/)
2. During installation, make sure it detects your `php.exe` from `C:\php`
3. Restart your computer to finish installing Composer

### Step 3: Set Up the Project
1. Create a folder for the project, e.g., `C:\ArgoSalesTracker`
2. Copy or unzip the project files into that folder
3. Open Command Prompt and navigate to that directory:

```bash
cd C:\ArgoSalesTracker
```

4. Run the following command to install PHP dependencies:

```bash
composer install
```

This will download all required dependencies into the `vendor/` folder.

> Note: The `vendor/` folder has been removed from the repository and is ignored via `.gitignore`. Always use `composer install` to set up dependencies.

## Publishing a new version of Argo Sales Tracker
1. Update the version in the files 'download.php' and 'update.xml' to the same version as the .exe file.
2. Upload the new .exe file to resources/downloads
