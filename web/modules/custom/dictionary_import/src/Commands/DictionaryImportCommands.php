<?php

namespace Drupal\dictionary_import\Commands;

use Drupal\dictionary_import\Service\DictionaryImporter;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for the Dictionary Import module.
 */
class DictionaryImportCommands extends DrushCommands {

  /**
   * The importer service.
   *
   * @var \Drupal\dictionary_import\Service\DictionaryImporter
   */
  protected DictionaryImporter $importer;

  /**
   * Constructs DictionaryImportCommands.
   */
  public function __construct(DictionaryImporter $importer) {
    parent::__construct();
    $this->importer = $importer;
  }

  /**
   * Import or update a dictionary entry for a given word.
   *
   * @command dictionary-import:word
   * @aliases diw
   *
   * @param string $word
   *   The word to import.
   *
   * @usage drush dictionary-import:word hello
   *   Imports or updates the dictionary entry for "hello".
   */
  public function importWord(string $word): void {
    try {
      $node = $this->importer->import($word);
      $this->logger()->success(sprintf('Imported dictionary entry for "%s" (node ID: %d).', $node->get('field_word')->value, $node->id()));
    }
    catch (\RuntimeException $e) {
      $this->logger()->error($e->getMessage());
    }
  }

}

