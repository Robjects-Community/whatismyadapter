<?php
declare(strict_types=1);

// Minimal CakePHP 5 test bootstrap
// Sets up paths and loads application bootstrap for running PHPUnit tests.

use Cake\Core\Configure;

// Define directory separator if not defined
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$root = dirname(__DIR__);

// Composer autoload
require $root . '/vendor/autoload.php';

// Define core path constants if not already defined
if (!defined('ROOT')) {
    define('ROOT', $root);
}
if (!defined('APP')) {
    define('APP', $root . '/src/');
}
if (!defined('CONFIG')) {
    define('CONFIG', $root . '/config/');
}
if (!defined('TMP')) {
    define('TMP', $root . '/tmp/');
}
if (!defined('LOGS')) {
    define('LOGS', $root . '/logs/');
}
if (!defined('WWW_ROOT')) {
    define('WWW_ROOT', $root . '/webroot/');
}
if (!defined('TESTS')) {
    define('TESTS', $root . '/tests/');
}

// Ensure tmp directories exist
@mkdir(TMP, 0775, true);
@mkdir(LOGS, 0775, true);

// Load application bootstrap
require CONFIG . 'bootstrap.php';

// Configure in-memory SQLite for test connection
use Cake\Datasource\ConnectionManager;
if (in_array('test', ConnectionManager::configured() ?? [], true)) {
    ConnectionManager::drop('test');
}
ConnectionManager::setConfig('test', [
    'className' => Cake\Database\Connection::class,
    'driver' => Cake\Database\Driver\Sqlite::class,
    'database' => ':memory:',
    'encoding' => 'utf8',
    'cacheMetadata' => true,
    'quoteIdentifiers' => false,
    'log' => false,
]);

// Make the test connection the default ORM connection during tests
ConnectionManager::alias('test', 'default');
Configure::write('App.defaultConnection', 'test');

// Create schema for data-only fixtures (CakePHP 5) using SchemaLoader
// Ensures tables exist before TestFixture reflects schema
$schemaFile = $root . '/tests/schema/ai_metrics.php';
if (file_exists($schemaFile)) {
    $loader = new \Cake\TestSuite\Fixture\SchemaLoader();
    $loader->loadInternalFile($schemaFile, 'test');
}

// Force test env settings
Configure::write('debug', true);
