# Dictionary API - Drupal Backend

A Drupal 10 backend that fetches word definitions from [DictionaryAPI.dev](https://dictionaryapi.dev/) and exposes them via JSON:API endpoints.

---

## 🚀 Quick Start (5 Minutes)

**Prerequisites:** PHP 8.1+, Composer 2.x ([verify](#prerequisites))

### Step 1: Install
```bash
git clone <repository-url>
cd my_drupal_site
composer install
```

### Step 2: Setup Drupal
```bash
# Copy settings file
copy web\sites\default\default.settings.php web\sites\default\settings.php

# Install Drupal with MySQL (XAMPP setup - recommended)
# First, create database in phpMyAdmin: CREATE DATABASE drupal_dictionary;
.\vendor\bin\drush site:install standard --db-url=mysql://root:@localhost/drupal_dictionary --account-name=admin --account-pass=admin --site-name="Dictionary API" -y

# Alternative: SQLite (no XAMPP needed)
# .\vendor\bin\drush site:install standard --db-url=sqlite://sites/default/files/.sqlite --account-name=admin --account-pass=admin --site-name="Dictionary API" -y

# Enable Dictionary module
.\vendor\bin\drush pm:enable dictionary_import -y
.\vendor\bin\drush cr
```

### Step 3: Start Server
```bash
cd web
php -S localhost:8081 .ht.router.php
```

**Note:** Port 8081 is used to avoid conflicts with XAMPP Apache (port 8080). You can use any available port by changing the number in the command above.

### Step 4: Import & Test
```bash
# Open new terminal in project root (E:\my_drupal_site\)
.\drush diw hello
.\drush diw world

# Visit API endpoint
# Browser: http://localhost:8081/jsonapi/node/dictionary_entry
```

**That's it!** Your API is running at `http://localhost:8081/jsonapi/node/dictionary_entry`

---

## Features

- Custom Drupal module for importing dictionary entries
- JSON:API endpoints for RESTful data access
- Drush CLI commands for word management
- Automatic word normalization and duplicate prevention
- 7 PHPUnit tests with full coverage

## Tech Stack

- **Drupal 10.3+** - CMS & API framework
- **PHP 8.1+** - Server language  
- **Composer 2.x** - Dependency manager
- **Drush 13.6+** - CLI tool
- **PHPUnit 9.6** - Testing framework
- **MySQL** - Database (via XAMPP with phpMyAdmin)
- **XAMPP** - Local development environment (Apache + MySQL + phpMyAdmin)

## Prerequisites

1. **XAMPP** - [Download](https://www.apachefriends.org/download.html) (includes PHP 8.1+, MySQL, phpMyAdmin)
2. **Composer 2.x** - [Download](https://getcomposer.org/download/)
3. **PHP Extensions** (included in XAMPP): `curl`, `gd`, `json`, `mbstring`, `xml`, `pdo`

**Setup XAMPP:**
- Install XAMPP and start MySQL service from XAMPP Control Panel
- Access phpMyAdmin at `http://localhost/phpmyadmin`
- Create database: `drupal_dictionary` (or use SQL: `CREATE DATABASE drupal_dictionary;`)

**Verify installations:**
```bash
php -v
composer --version
```

## Detailed Installation

### 1. Clone & Install Dependencies

```bash
git clone <repository-url>
cd my_drupal_site
composer install
```

### 2. Configure Settings

```bash
# Copy settings file
copy web\sites\default\default.settings.php web\sites\default\settings.php
```

### 3. Install Drupal

**Using MySQL (XAMPP - Recommended):**
```bash
# Step 1: Create database in phpMyAdmin (http://localhost/phpmyadmin)
# Database name: drupal_dictionary
# Or run SQL query: CREATE DATABASE drupal_dictionary;

# Step 2: Install Drupal with MySQL connection
.\vendor\bin\drush site:install standard --db-url=mysql://root:@localhost/drupal_dictionary --account-name=admin --account-pass=admin --site-name="Dictionary API" -y
```

**Alternative - Using SQLite (no XAMPP needed):**
```bash
.\vendor\bin\drush site:install standard --db-url=sqlite://sites/default/files/.sqlite --account-name=admin --account-pass=admin --site-name="Dictionary API" -y
```

### 4. Enable Dictionary Module

```bash
.\vendor\bin\drush pm:enable dictionary_import -y
.\vendor\bin\drush cr
```

## Running Locally

**PHP Built-in Server:**
```bash
cd web
php -S localhost:8081 .ht.router.php
```

**Why Port 8081?** XAMPP Apache typically uses port 8080, so we use 8081 to avoid conflicts. You can use any available port (8082, 8083, etc.) by changing the number above.

Access at:
- Site: http://localhost:8081
- Admin: http://localhost:8081/user/login (admin/admin)
- API: http://localhost:8081/jsonapi/node/dictionary_entry
- phpMyAdmin: http://localhost/phpmyadmin (via XAMPP Apache)

**Stop server:** Press `Ctrl+C`

## Local Development Setup

This project uses the following local setup:

### Database: MySQL via XAMPP
- **XAMPP** provides Apache, MySQL, and phpMyAdmin
- **MySQL** database running on port 3306 (default)
- **phpMyAdmin** accessible at `http://localhost/phpmyadmin`
- **Database name:** `drupal_dictionary`

### Web Server: PHP Built-in Server
- **Port 8081** used to avoid conflict with XAMPP Apache (port 8080)
- Drupal site accessible at `http://localhost:8081`
- API endpoints at `http://localhost:8081/jsonapi/node/dictionary_entry`

### Architecture
```
XAMPP (Port 80/3306)          PHP Server (Port 8081)
├── Apache (80)               ├── Drupal 10
├── MySQL (3306)         ←────┤ Database Connection
└── phpMyAdmin (/phpmyadmin)  └── JSON:API Endpoints
```

**Why this setup?**
- XAMPP provides robust MySQL database with phpMyAdmin GUI
- PHP built-in server (port 8081) serves Drupal without Apache conflicts
- Easy database management through phpMyAdmin interface

## Usage

### Import Words with Drush

**⚠️ Run all commands from project root:** `E:\my_drupal_site\`

**Method 1: Using drush launcher (shortest):**
```bash
cd E:\my_drupal_site
.\drush diw hello
```

**Method 2: Using vendor path:**
```bash
cd E:\my_drupal_site
.\vendor\bin\drush diw hello
```

**Method 3: Full command name:**
```bash
.\drush dictionary-import:word hello
```

### Examples

**Import single word:**
```bash
.\drush diw victory
```

**Import multiple words:**
```bash
.\drush diw hello
.\drush diw world
.\drush diw cursor
```

**Update existing word:**
```bash
.\drush diw hello
# Updates the existing entry instead of creating duplicate
```

**Word normalization (automatic lowercase):**
```bash
.\drush diw Hello    # Saved as "hello"
.\drush diw WORLD    # Saved as "world"
```

**Error examples:**
```bash
.\drush diw asdfqwerzxcv
# [error] Word "asdfqwerzxcv" not found in external API.

.\drush diw ""
# [error] Word cannot be empty.
```

## API Endpoints

Base URL: `http://localhost:8081`

### Get All Entries

```bash
# Browser
http://localhost:8081/jsonapi/node/dictionary_entry

# curl
curl http://localhost:8081/jsonapi/node/dictionary_entry

# PowerShell
Invoke-RestMethod -Uri "http://localhost:8081/jsonapi/node/dictionary_entry"
```

### Filter by Word

```bash
http://localhost:8081/jsonapi/node/dictionary_entry?filter[field_word]=hello
```

### Pagination

```bash
# First 10
http://localhost:8081/jsonapi/node/dictionary_entry?page[limit]=10&page[offset]=0

# Next 10
http://localhost:8081/jsonapi/node/dictionary_entry?page[limit]=10&page[offset]=10
```

### Sort

```bash
# Alphabetically
http://localhost:8081/jsonapi/node/dictionary_entry?sort=field_word

# Newest first
http://localhost:8081/jsonapi/node/dictionary_entry?sort=-created
```

### Response Example

```json
{
  "data": [
    {
      "type": "node--dictionary_entry",
      "id": "uuid-here",
      "attributes": {
        "title": "hello",
        "field_word": "hello",
        "field_definitions": "Used as a greeting...",
        "created": "2024-02-11T10:30:00+00:00"
      }
    }
  ]
}
```

### Response Fields

| Field | Description |
|-------|-------------|
| `field_word` | The word (lowercase) |
| `field_definitions` | All definitions separated by `\n\n` |
| `created` | Creation timestamp |
| `changed` | Last update timestamp |

### CORS (for frontend apps)

Add to `web/sites/default/settings.php`:
```php
$settings['cors_domains'] = [
  'http://localhost:3000',  // Next.js
  'http://localhost:5173',  // Vite
];
```

## Testing

**Run from project root:**
```bash
.\vendor\bin\phpunit --configuration=web\modules\custom\dictionary_import\phpunit.xml
```

**Expected output:**
```
PHPUnit 9.6.x by Sebastian Bergmann and contributors.

.......                                                             7 / 7 (100%)

OK (7 tests, 28 assertions)
```

**Run specific test:**
```bash
.\vendor\bin\phpunit --configuration=web\modules\custom\dictionary_import\phpunit.xml --filter testImportCreatesOrUpdatesNode
```

### Test Coverage

- ✅ Import creates new node
- ✅ Word not found throws exception
- ✅ Empty word validation
- ✅ Whitespace validation
- ✅ Update existing entry (no duplicates)
- ✅ No definitions handling
- ✅ Word normalization to lowercase

## Project Structure

```
my_drupal_site/
├── composer.json              # Dependencies
├── drush.cmd / drush.ps1      # Drush launchers
├── vendor/                    # Composer packages (gitignored)
└── web/                       # Document root
    ├── core/                  # Drupal core (gitignored)
    ├── modules/
    │   └── custom/
    │       └── dictionary_import/    # Custom module ⭐
    │           ├── src/
    │           │   ├── Service/DictionaryImporter.php
    │           │   └── Commands/DictionaryImportCommands.php
    │           ├── tests/
    │           │   └── src/Unit/DictionaryImporterTest.php
    │           └── config/install/   # Module configuration
    └── sites/
        └── default/
            ├── settings.php   # DB config (gitignored)
            └── files/         # Uploads (gitignored)
```

## Troubleshooting

### Memory limit error
```bash
php -d memory_limit=512M vendor/bin/drush
```

### Drush not working (Windows)
```powershell
.\vendor\bin\drush status     # Use .\ prefix
```

### MySQL access denied (XAMPP)
```bash
# Use empty password for XAMPP default (root user, no password)
--db-url=mysql://root:@localhost/drupal_dictionary

# If you set a password in phpMyAdmin, use:
--db-url=mysql://root:your_password@localhost/drupal_dictionary
```

### XAMPP MySQL not starting
1. Check if another MySQL service is running (Task Manager)
2. Change MySQL port in XAMPP Config → my.ini
3. Restart XAMPP MySQL service

### SSL certificate error
For local dev only, edit `web/modules/custom/dictionary_import/src/Service/DictionaryImporter.php`:
```php
'verify' => FALSE,  // Add this line (local dev only!)
```

### Empty API response
1. Import a word: `.\drush diw hello`
2. Check module enabled: `.\drush pm:list | Select-String jsonapi`
3. Visit: http://localhost:8081/admin/content
4. Check database in phpMyAdmin: `http://localhost/phpmyadmin` → `drupal_dictionary` database

## Deployment

### Production Checklist

1. **Trusted hosts** in `settings.php`:
   ```php
   $settings['trusted_host_patterns'] = ['^api\.yourdomain\.com$'];
   ```

2. **Disable dev modules:**
   ```bash
   drush pm:uninstall dblog devel -y
   ```

3. **Optimize Composer:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Enable HTTPS** on your web server

5. **Database backups:**
   ```bash
   drush sql:dump --gzip --result-file=backup.sql
   ```

## Additional Resources

- [Module Documentation](web/modules/custom/dictionary_import/README.md)
- [Drupal Docs](https://www.drupal.org/docs)
- [JSON:API Spec](https://jsonapi.org/)
- [External API](https://dictionaryapi.dev/)

## License

GPL-2.0-or-later

---

**Quick Reference:**
```bash
# From E:\my_drupal_site\
.\drush diw <word>           # Import/update word
.\drush cr                   # Clear cache
.\vendor\bin\phpunit --configuration=web\modules\custom\dictionary_import\phpunit.xml  # Run tests
cd web && php -S localhost:8081 .ht.router.php    # Start server (port 8081)

# XAMPP
http://localhost/phpmyadmin  # Access phpMyAdmin
# Database: drupal_dictionary
```
