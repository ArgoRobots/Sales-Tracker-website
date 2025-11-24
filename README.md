# Argo Books Website

## Introduction

This is the website for [Argo Books](https://github.com/ArgoRobots/Sales-Tracker), a WinForms application designed to track the sales and purchases of products. This website serves as a platform for users to download the software, purchase license keys, access documentation, and has an administrative system for managing licenses, user accounts, and analytics.

You can view the live website here: https://argorobots.com/

## Technologies Used

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
- Community page for feature requests and bug reports
- Support/contact system
- About us and legal information

### Admin System

- Secure admin dashboard with two-factor authentication
- License key generation and management
- User account administration
- Statistics tracking and analytics dashboard

## Installation

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html). Download version 8.2.12 or higher
2. Install XAMPP (default location: `C:\xampp`)
3. Open XAMPP Control Panel and start Apache and MySQL
4. Add PHP to your system PATH:
   - Open Start menu and search for "Environment Variables"
   - Edit the `Path` variable and add `C:\xampp\php`
5. Open Command Prompt and run `php -v` to verify PHP is installed

### Step 2: Install Composer

1. Download and install Composer from [https://getcomposer.org/](https://getcomposer.org/)
2. During installation, make sure it detects your `php.exe` from `C:\xampp\php`
3. Restart your computer to finish installing Composer
4. Open Command Prompt and run `composer -V` to verify Composer is installed

### Step 3: Set Up the Project

1. Place the project files directly in XAMPP's `htdocs` directory: `C:\xampp\htdocs\sales-tracker-website`
   - The folder name will become part of your URL (e.g., folder `sales-tracker-website` → URL `localhost/sales-tracker-website`)
   - Avoid spaces in the folder name
2. Open Command Prompt and navigate to that directory:

```bash
cd C:\xampp\htdocs\sales-tracker-website
```

3. Run the following command to install PHP dependencies:

```bash
composer install
```

This will download all required dependencies into the `vendor/` folder.

### Step 4: Set Up the Database

You need to create a MySQL database and import the schema.

**What is phpMyAdmin?** phpMyAdmin is a web-based tool that comes with XAMPP/WAMP/MAMP that lets you manage MySQL databases through a visual interface.

1. **Open phpMyAdmin**:
   - Open your web browser
   - Go to: `http://localhost/phpmyadmin`
   - You should see the phpMyAdmin interface

2. **Create the Database**:
   - Click on **"New"** in the top Left
   - In the "Create database" section, type: `sales_tracker`
   - Click **"Create"**

3. **Import the Schema**:
   - Click on the **"sales_tracker"** database name in the left sidebar (it should now appear in the list)
   - Click on the **"Import"** tab at the top
   - Click **"Choose File"** button
   - Navigate to your sales-tracker-website folder and select: `mysql_schema.sql`
   - Scroll down and click **"Import"** button at the bottom
   - You should see a success message and 7 tables created

4. **Verify the Import**:
   - Click on the **"Structure"** tab
   - You should see all the tables


### Step 5: Set Up Local Email Sending

When running the Argo Books website locally on XAMPP, PHP's `mail()` function won't work without a mail server. You can set up a fake SMTP server that catches all emails locally and displays them in a web interface using MailHog. You can find the instructions to set this up [In this document](https://github.com/ArgoRobots/Argo-Books-website/blob/main/read-me/Local%20email%20setup.md).

## Running Locally

1. Open XAMPP Control Panel and start Apache and MySQL
2. Navigate to `http://localhost/sales-tracker-website` in your browser (adjust the folder name if different)
3. The website should now be running locally

## Publishing a new version of Argo Books
1. Create a new folder in `resources/downloads/versions` named whatever the version number is
1. Upload the new .exe and the language folder to this new directory
2. Update the version number in `update.xml`
3. Add the new version to whats-new/index.php