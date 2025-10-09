<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Cache\Cache;
use Cake\Cache\Engine\FileEngine;
use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Database\TypeFactory;
use Cake\Datasource\ConnectionManager;
use Cake\Queue\QueueManager;
use Josegonzalez\Upload\Database\Type\FileType;
use Migrations\TestSuite\Migrator;

// Clear all caches before running tests
Cache::clearAll();

// Set a consistent timezone for tests
date_default_timezone_set('UTC');

/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

if (empty($_SERVER['HTTP_HOST']) && !Configure::read('App.fullBaseUrl')) {
    Configure::write('App.fullBaseUrl', 'http://localhost');
}

// DebugKit skips settings these connection config if PHP SAPI is CLI / PHPDBG.
// But since PagesControllerTest is run with debug enabled and DebugKit is loaded
// in application, without setting up these config DebugKit errors out.
ConnectionManager::setConfig('test_debug_kit', [
    'className' => 'Cake\Database\Connection',
    'driver' => 'Cake\Database\Driver\Sqlite',
    'database' => TMP . 'debug_kit.sqlite',
    'encoding' => 'utf8',
    'cacheMetadata' => true,
    'quoteIdentifiers' => false,
]);

ConnectionManager::alias('test_debug_kit', 'debug_kit');

// Fixate now to avoid one-second-leap-issues
Chronos::setTestNow(Chronos::now());

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');

// Ensure the Upload plugin is loaded for tests
Configure::write('Plugin.Upload', ['bootstrap' => true]);

// Register the custom database type for 'upload.file'
TypeFactory::map('upload.file', FileType::class);

// Use migrations to build test database schema.
//
// Will rebuild the database if the migration state differs
// from the migration history in files.
//
// If you are not using CakePHP's migrations you can
// hook into your migration tool of choice here or
// load schema from a SQL dump file with
// use Cake\TestSuite\Fixture\SchemaLoader;
// (new SchemaLoader())->loadSqlFiles('./tests/schema.sql', 'test');

(new Migrator())->run();

// Switch queue connection for testing - force null transport to avoid external deps
if (env('CAKE_ENV') === 'test') {
    Configure::write('Queue.default', [
        'url' => 'null://localhost',
        'queue' => 'test_queue',
        'logger' => false,
        'receiveTimeout' => 0,
    'storeFailedJobs' => false,
        'uniqueCache' => [
            'className' => FileEngine::class,
            'prefix' => 'willow_queue_unique_test_',
            'path' => CACHE . 'persistent' . DS . 'queue_unique_jobs' . DS . 'test' . DS,
            'serialize' => true,
            'duration' => '+1 week',
        ],
    ]);
}

QueueManager::setConfig('default', Configure::read('Queue.default'));
