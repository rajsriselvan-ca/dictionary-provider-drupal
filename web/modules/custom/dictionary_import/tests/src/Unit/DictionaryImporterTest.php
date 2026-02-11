<?php

namespace Drupal\Tests\dictionary_import\Unit;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dictionary_import\Service\DictionaryImporter;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @coversDefaultClass \Drupal\dictionary_import\Service\DictionaryImporter
 *
 * @group dictionary_import
 */
class DictionaryImporterTest extends TestCase {

  /**
   * Tests a successful import of a word.
   *
   * @covers ::import
   */
  public function testImportCreatesOrUpdatesNode(): void {
    $word = 'Hello';
    $normalized = 'hello';

    $definitions_payload = [
      [
        'meanings' => [
          [
            'definitions' => [
              ['definition' => 'A greeting.'],
              ['definition' => 'An expression of surprise.'],
            ],
          ],
        ],
      ],
    ];

    // Mock HTTP response body.
    $body = $this->createMock(StreamInterface::class);
    $body->method('__toString')->willReturn(json_encode($definitions_payload, JSON_THROW_ON_ERROR));

    // Mock HTTP response.
    $response = $this->createMock(ResponseInterface::class);
    $response->method('getStatusCode')->willReturn(200);
    $response->method('getBody')->willReturn($body);

    $http_client = $this->createMock(ClientInterface::class);
    $http_client->method('request')
      ->with('GET', sprintf('https://api.dictionaryapi.dev/api/v2/entries/en/%s', $normalized), $this->anything())
      ->willReturn($response);

    // Mock node and storage.
    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())->method('setTitle')->with($normalized);
    $node->expects($this->exactly(2))->method('set')
      ->willReturnCallback(function ($field, $value) use ($normalized) {
        if ($field === 'field_word') {
          $this->assertEquals($normalized, $value);
        }
        elseif ($field === 'field_definitions') {
          $this->assertIsString($value);
          $this->assertStringContainsString('A greeting.', $value);
          $this->assertStringContainsString('An expression of surprise.', $value);
        }
      });
    $node->expects($this->once())->method('save');

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')->willReturn([]);
    $storage->method('create')->with(['type' => 'dictionary_entry'])->willReturn($node);

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->method('getStorage')->with('node')->willReturn($storage);

    $logger = $this->createMock(LoggerInterface::class);

    $importer = new DictionaryImporter($http_client, $entity_type_manager, $logger);

    $result_node = $importer->import($word);
    $this->assertSame($node, $result_node);
  }

  /**
   * Tests that a 404 response throws a RuntimeException.
   *
   * @covers ::import
   */
  public function testImportWordNotFoundThrows(): void {
    $word = 'nonexistentword';
    $normalized = 'nonexistentword';

    $body = $this->createMock(StreamInterface::class);
    $body->method('__toString')->willReturn('{}');

    $response = $this->createMock(ResponseInterface::class);
    $response->method('getStatusCode')->willReturn(404);
    $response->method('getBody')->willReturn($body);

    $http_client = $this->createMock(ClientInterface::class);
    $http_client->method('request')->willReturn($response);

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $importer = new DictionaryImporter($http_client, $entity_type_manager, $logger);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf('Word "%s" not found in external API.', $normalized));
    $importer->import($word);
  }

  /**
   * Tests that an empty word throws a RuntimeException.
   *
   * @covers ::import
   */
  public function testImportEmptyWordThrows(): void {
    $http_client = $this->createMock(ClientInterface::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $importer = new DictionaryImporter($http_client, $entity_type_manager, $logger);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Word cannot be empty.');
    $importer->import('');
  }

  /**
   * Tests that whitespace-only word throws a RuntimeException.
   *
   * @covers ::import
   */
  public function testImportWhitespaceWordThrows(): void {
    $http_client = $this->createMock(ClientInterface::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $importer = new DictionaryImporter($http_client, $entity_type_manager, $logger);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Word cannot be empty.');
    $importer->import('   ');
  }

  /**
   * Tests updating an existing node instead of creating a new one.
   *
   * @covers ::import
   */
  public function testImportUpdatesExistingNode(): void {
    $word = 'hello';
    $normalized = 'hello';

    $definitions_payload = [
      [
        'meanings' => [
          [
            'definitions' => [
              ['definition' => 'Updated greeting.'],
            ],
          ],
        ],
      ],
    ];

    // Mock HTTP response body.
    $body = $this->createMock(StreamInterface::class);
    $body->method('__toString')->willReturn(json_encode($definitions_payload, JSON_THROW_ON_ERROR));

    // Mock HTTP response.
    $response = $this->createMock(ResponseInterface::class);
    $response->method('getStatusCode')->willReturn(200);
    $response->method('getBody')->willReturn($body);

    $http_client = $this->createMock(ClientInterface::class);
    $http_client->method('request')->willReturn($response);

    // Mock existing node.
    $existing_node = $this->createMock(NodeInterface::class);
    $existing_node->expects($this->once())->method('setTitle')->with($normalized);
    $existing_node->expects($this->exactly(2))->method('set')
      ->willReturnCallback(function ($field, $value) use ($normalized) {
        if ($field === 'field_word') {
          $this->assertEquals($normalized, $value);
        }
        elseif ($field === 'field_definitions') {
          $this->assertIsString($value);
          $this->assertStringContainsString('Updated greeting.', $value);
        }
      });
    $existing_node->expects($this->once())->method('save');

    $storage = $this->createMock(EntityStorageInterface::class);
    // Return existing node when loading by properties.
    $storage->method('loadByProperties')->willReturn([$existing_node]);
    // create() should NOT be called when updating.
    $storage->expects($this->never())->method('create');

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->method('getStorage')->with('node')->willReturn($storage);

    $logger = $this->createMock(LoggerInterface::class);

    $importer = new DictionaryImporter($http_client, $entity_type_manager, $logger);

    $result_node = $importer->import($word);
    $this->assertSame($existing_node, $result_node);
  }

  /**
   * Tests that word with no definitions throws a RuntimeException.
   *
   * @covers ::import
   */
  public function testImportNoDefinitionsThrows(): void {
    $word = 'hello';
    $normalized = 'hello';

    // Empty meanings array - no definitions.
    $definitions_payload = [
      ['meanings' => []],
    ];

    $body = $this->createMock(StreamInterface::class);
    $body->method('__toString')->willReturn(json_encode($definitions_payload, JSON_THROW_ON_ERROR));

    $response = $this->createMock(ResponseInterface::class);
    $response->method('getStatusCode')->willReturn(200);
    $response->method('getBody')->willReturn($body);

    $http_client = $this->createMock(ClientInterface::class);
    $http_client->method('request')->willReturn($response);

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $importer = new DictionaryImporter($http_client, $entity_type_manager, $logger);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf('No definitions found for word "%s".', $normalized));
    $importer->import($word);
  }

  /**
   * Tests that word is normalized to lowercase.
   *
   * @covers ::import
   */
  public function testImportNormalizesWordToLowercase(): void {
    $word = 'HELLO';
    $normalized = 'hello';

    $definitions_payload = [
      [
        'meanings' => [
          [
            'definitions' => [
              ['definition' => 'A greeting.'],
            ],
          ],
        ],
      ],
    ];

    $body = $this->createMock(StreamInterface::class);
    $body->method('__toString')->willReturn(json_encode($definitions_payload, JSON_THROW_ON_ERROR));

    $response = $this->createMock(ResponseInterface::class);
    $response->method('getStatusCode')->willReturn(200);
    $response->method('getBody')->willReturn($body);

    $http_client = $this->createMock(ClientInterface::class);
    $http_client->method('request')
      ->with('GET', 'https://api.dictionaryapi.dev/api/v2/entries/en/hello', $this->anything())
      ->willReturn($response);

    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())->method('setTitle')->with($normalized);
    $node->expects($this->exactly(2))->method('set')->willReturnCallback(function ($field, $value) use ($normalized) {
      if ($field === 'field_word') {
        $this->assertEquals($normalized, $value);
      }
    });
    $node->method('save');

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')->with([
      'type' => 'dictionary_entry',
      'field_word' => $normalized,
    ])->willReturn([]);
    $storage->method('create')->willReturn($node);

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->method('getStorage')->willReturn($storage);

    $logger = $this->createMock(LoggerInterface::class);

    $importer = new DictionaryImporter($http_client, $entity_type_manager, $logger);

    $importer->import($word);
  }

}
