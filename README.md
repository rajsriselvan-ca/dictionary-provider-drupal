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

# Install Drupal with SQLite (no database setup needed)
.\vendor\bin\drush site:install standard --db-url=sqlite://sites/default/files/.sqlite --account-name=admin --account-pass=admin --site-name="Dictionary API" -y

# Enable Dictionary module
.\vendor\bin\drush pm:enable dictionary_import -y
.\vendor\bin\drush cr
```

### Step 3: Start Server
```bash
cd web
php -S localhost:8080 .ht.router.php
```

### Step 4: Import & Test
```bash
# Open new terminal in project root (E:\my_drupal_site\)
.\drush diw hello
.\drush diw world

# Visit API endpoint
# Browser: http://localhost:8080/jsonapi/node/dictionary_entry
```

**That's it!** Your API is running at `http://localhost:8080/jsonapi/node/dictionary_entry`

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
- **SQLite/MySQL/PostgreSQL** - Database

## Prerequisites

1. **PHP 8.1+** with extensions: `curl`, `gd`, `json`, `mbstring`, `xml`, `pdo`
2. **Composer 2.x** - [Download](https://getcomposer.org/download/)
3. **Database**: SQLite (easiest) or MySQL/PostgreSQL
4. **Web Server**: PHP built-in server, XAMPP, or Apache/Nginx

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

**Using SQLite (recommended for local):**
```bash
.\vendor\bin\drush site:install standard --db-url=sqlite://sites/default/files/.sqlite --account-name=admin --account-pass=admin --site-name="Dictionary API" -y
```

**Using MySQL (XAMPP):**
```bash
# Create database first: CREATE DATABASE drupal_dictionary;
.\vendor\bin\drush site:install standard --db-url=mysql://root:@localhost/drupal_dictionary --account-name=admin --account-pass=admin --site-name="Dictionary API" -y
```

### 4. Enable Dictionary Module

```bash
.\vendor\bin\drush pm:enable dictionary_import -y
.\vendor\bin\drush cr
```

## Running Locally

**PHP Built-in Server (simplest):**
```bash
cd web
php -S localhost:8080 .ht.router.php
```

Access at:
- Site: http://localhost:8080
- Admin: http://localhost:8080/user/login (admin/admin)
- API: http://localhost:8080/jsonapi/node/dictionary_entry

**Stop server:** Press `Ctrl+C`

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

Base URL: `http://localhost:8080`

### Get All Entries

```bash
# Browser
http://localhost:8080/jsonapi/node/dictionary_entry

# curl
curl http://localhost:8080/jsonapi/node/dictionary_entry

# PowerShell
Invoke-RestMethod -Uri "http://localhost:8080/jsonapi/node/dictionary_entry"
```

### Filter by Word

```bash
http://localhost:8080/jsonapi/node/dictionary_entry?filter[field_word]=hello
```

### Pagination

```bash
# First 10
http://localhost:8080/jsonapi/node/dictionary_entry?page[limit]=10&page[offset]=0

# Next 10
http://localhost:8080/jsonapi/node/dictionary_entry?page[limit]=10&page[offset]=10
```

### Sort

```bash
# Alphabetically
http://localhost:8080/jsonapi/node/dictionary_entry?sort=field_word

# Newest first
http://localhost:8080/jsonapi/node/dictionary_entry?sort=-created
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
# Use empty password for XAMPP default
--db-url=mysql://root:@localhost/drupal_dictionary
```

### SSL certificate error
For local dev only, edit `web/modules/custom/dictionary_import/src/Service/DictionaryImporter.php`:
```php
'verify' => FALSE,  // Add this line (local dev only!)
```

### Empty API response
1. Import a word: `.\drush diw hello`
2. Check module enabled: `.\drush pm:list | Select-String jsonapi`
3. Visit: http://localhost:8080/admin/content

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
cd web && php -S localhost:8080 .ht.router.php    # Start server
```
