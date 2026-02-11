<?php

namespace Drupal\dictionary_import\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Service to import dictionary entries from dictionaryapi.dev.
 */
class DictionaryImporter {

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a new DictionaryImporter.
   */
  public function __construct(ClientInterface $http_client, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->httpClient = $http_client;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * Imports or updates a dictionary entry for the given word.
   *
   * @param string $word
   *   The word to import.
   *
   * @return \Drupal\node\NodeInterface
   *   The created or updated Dictionary Entry node.
   *
   * @throws \RuntimeException
   *   Thrown when the word cannot be found or parsed.
   */
  public function import(string $word): NodeInterface {
    $normalized_word = mb_strtolower(trim($word));

    if ($normalized_word === '') {
      throw new \RuntimeException('Word cannot be empty.');
    }

    $definitions = $this->fetchDefinitions($normalized_word);

    if (empty($definitions)) {
      throw new \RuntimeException(sprintf('No definitions found for word "%s".', $normalized_word));
    }

    /** @var \Drupal\node\NodeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('node');

    $nodes = $storage->loadByProperties([
      'type' => 'dictionary_entry',
      'field_word' => $normalized_word,
    ]);

    /** @var \Drupal\node\NodeInterface $node */
    $node = $nodes ? reset($nodes) : $storage->create(['type' => 'dictionary_entry']);

    $node->setTitle($normalized_word);
    $node->set('field_word', $normalized_word);
    $node->set('field_definitions', implode("\n\n", $definitions));

    $node->save();

    return $node;
  }

  /**
   * Fetches definitions from the external API.
   *
   * @param string $word
   *   The word to fetch.
   *
   * @return string[]
   *   An array of definition strings.
   */
  protected function fetchDefinitions(string $word): array {
    $url = sprintf('https://api.dictionaryapi.dev/api/v2/entries/en/%s', rawurlencode($word));

    try {
      $response = $this->httpClient->request('GET', $url, [
        'http_errors' => FALSE,
        'timeout' => 10,
      ]);
    }
    catch (RequestException $e) {
      $this->logger->error('Error requesting dictionary API for word "@word": @message', [
        '@word' => $word,
        '@message' => $e->getMessage(),
      ]);
      throw new \RuntimeException('Failed to contact external dictionary API.');
    }

    $status = $response->getStatusCode();
    $body = (string) $response->getBody();

    if ($status === 404) {
      throw new \RuntimeException(sprintf('Word "%s" not found in external API.', $word));
    }

    if ($status < 200 || $status >= 300) {
      $this->logger->error('Unexpected status "@status" from dictionary API for word "@word".', [
        '@status' => $status,
        '@word' => $word,
      ]);
      throw new \RuntimeException('Unexpected response from external dictionary API.');
    }

    $data = json_decode($body, TRUE);
    if (!is_array($data) || empty($data[0]['meanings'])) {
      return [];
    }

    $definitions = [];
    foreach ($data as $entry) {
      if (empty($entry['meanings']) || !is_array($entry['meanings'])) {
        continue;
      }
      foreach ($entry['meanings'] as $meaning) {
        if (empty($meaning['definitions']) || !is_array($meaning['definitions'])) {
          continue;
        }
        foreach ($meaning['definitions'] as $definition) {
          if (!empty($definition['definition'])) {
            $definitions[] = $definition['definition'];
          }
        }
      }
    }

    return array_values(array_unique($definitions));
  }

}

