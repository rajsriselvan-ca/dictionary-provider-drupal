<?php

/**
 * @file
 * Bootstrap file for PHPUnit tests.
 */

// Load Composer autoloader.
$autoloader = require __DIR__ . '/../../../../../vendor/autoload.php';

// Register the module's namespace with the autoloader.
$autoloader->addPsr4('Drupal\\dictionary_import\\', __DIR__ . '/../src/');
$autoloader->addPsr4('Drupal\\Tests\\dictionary_import\\', __DIR__ . '/src/');

// Load Drupal core classes that are needed for the tests.
$autoloader->addPsr4('Drupal\\Core\\', __DIR__ . '/../../../../../web/core/lib/Drupal/Core/');
$autoloader->addPsr4('Drupal\\node\\', __DIR__ . '/../../../../../web/core/modules/node/src/');
$autoloader->addPsr4('Drupal\\user\\', __DIR__ . '/../../../../../web/core/modules/user/src/');
