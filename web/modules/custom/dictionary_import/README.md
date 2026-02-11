# Dictionary Import Module

A Drupal 10 module that imports dictionary word definitions from the [DictionaryAPI.dev](https://dictionaryapi.dev/) external API and exposes them via JSON:API endpoints.

## Features

- Fetches word definitions from external dictionary API
- Creates/updates Dictionary Entry content type
- Exposes data via JSON:API for headless/decoupled applications
- Drush command integration for easy word imports
- Full PHPUnit test coverage
- Automatic word normalization (lowercase)
- Duplicate prevention (updates existing entries)

## Requirements

- Drupal 10.x
- JSON:API module (Drupal core, enabled by default)
- PHP 8.1 or higher
- Guzzle HTTP Client (included with Drupal core)

## Installation

1. Enable the module:
   ```bash
   drush pm:enable dictionary_import -y
   ```

2. Clear cache:
   ```bash
   drush cr
   ```

The module automatically creates the Dictionary Entry content type with required fields during installation.

## Usage

### Import a word via Drush

Import or update a dictionary entry from the external API:

```bash
drush dictionary-import:word hello
```

Or use the alias:

```bash
drush diw cursor
```

### Import multiple words

```bash
drush diw hello
drush diw world
drush diw cursor
```

## JSON:API Endpoints

The module exposes Dictionary Entry content via JSON:API.

### Base Endpoint

```
GET /jsonapi/node/dictionary_entry
```

### Get all entries

```
GET /jsonapi/node/dictionary_entry
```

### Filter by word

```
GET /jsonapi/node/dictionary_entry?filter[field_word]=hello
```

### Example Response

```json
{
  "data": [
    {
      "type": "node--dictionary_entry",
      "id": "uuid-here",
      "attributes": {
        "title": "hello",
        "field_word": "hello",
        "field_definitions": "Used as a greeting or to begin a phone conversation.\n\nA greeting (salutation) said when meeting someone or acknowledging someone's arrival or presence.\n\nA greeting used when answering the telephone.\n\n..."
      }
    }
  ]
}
```

### Fields

- `field_word` (string, required, unique): The dictionary word in lowercase
- `field_definitions` (text, long): Definitions collected from the external API, separated by double newlines

## Content Type

**Dictionary Entry** (`dictionary_entry`)
- **Field Word** (`field_word`): Plain text, required, unique
- **Definitions** (`field_definitions`): Long text area containing all definitions

## Testing the API

### Using curl

```bash
# Get all entries
curl http://your-domain.com/jsonapi/node/dictionary_entry

# Filter by specific word
curl "http://your-domain.com/jsonapi/node/dictionary_entry?filter[field_word]=hello"
```

### Using PowerShell

```powershell
# Get all entries
Invoke-WebRequest -Uri "http://your-domain.com/jsonapi/node/dictionary_entry" | Select-Object -Expand Content

# Filter by specific word
Invoke-WebRequest -Uri "http://your-domain.com/jsonapi/node/dictionary_entry?filter[field_word]=hello" | Select-Object -Expand Content
```

### Using browser

Navigate to your site's JSON:API endpoint:
- `/jsonapi/node/dictionary_entry` - All entries
- `/jsonapi/node/dictionary_entry?filter[field_word]=hello` - Specific word

## Permissions

Anonymous users need the "View published content" permission to access entries via JSON:API. This is configured automatically when the module is installed.

## Development

### Running PHPUnit Tests

From the project root:

```bash
vendor/bin/phpunit --configuration=web/modules/custom/dictionary_import/phpunit.xml
```

Or from the module directory:

```bash
cd web/modules/custom/dictionary_import
../../../../vendor/bin/phpunit
```

### Test Coverage

The module includes 7 PHPUnit unit tests for the `DictionaryImporter` service:

1. **testImportCreatesOrUpdatesNode** - Tests successful import creates a new node with correct data
2. **testImportWordNotFoundThrows** - Tests 404 response throws RuntimeException
3. **testImportEmptyWordThrows** - Tests empty string throws RuntimeException
4. **testImportWhitespaceWordThrows** - Tests whitespace-only string throws RuntimeException
5. **testImportUpdatesExistingNode** - Tests updating existing entry instead of creating duplicate
6. **testImportNoDefinitionsThrows** - Tests word with no definitions throws RuntimeException
7. **testImportNormalizesWordToLowercase** - Tests word normalization to lowercase

All tests use mocking to avoid external API calls and database dependencies.

## Architecture

- **DictionaryImporter** service: Fetches and processes data from external API
- **DictionaryImportCommands**: Drush commands for importing words
- **Content type configuration**: YAML config in `config/install/`

## Error Handling

- **Empty word**: Throws `RuntimeException` with message "Word cannot be empty."
- **Word not found**: Throws `RuntimeException` with message 'Word "xyz" not found in external API.'
- **API failure**: Logs error and throws `RuntimeException`
