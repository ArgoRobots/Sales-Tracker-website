# Argo Sales Tracker Website

## Introduction

This is the website for [Argo Sales Tracker](https://github.com/ArgoRobots/Sales-Tracker), a WinForms application designed to track the sales and purchases of products.

## Technologies used

- **HTML5 and CSS3** Structure and styling
- **jQuery**: Dynamic content loading and interactivity
- **PHP**: Backend license management
- **MySQL**: Database for storing license information

## Admin System

Backend administration system for managing license keys:

- Protected by password and two factor authentication
- License key generation and management
- Email system for sending license keys to customers
- License validation API endpoint

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
