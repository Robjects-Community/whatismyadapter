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

// Rely on fixtures to create/drop tables; avoid preloading schema to keep SQLite setup minimal

// Additionally, create minimal SQLite tables that our tests depend on when they don't exist yet.
// CakePHP 5 fixtures reflect schema from the connection by default, so we ensure the essential tables exist.
try {
    /** @var \Cake\Database\Connection $conn */
    $conn = \Cake\Datasource\ConnectionManager::get('test');

    // ai_metrics table
    $conn->execute(
        "CREATE TABLE IF NOT EXISTS ai_metrics (
            id CHAR(36) PRIMARY KEY,
            task_type VARCHAR(50) NOT NULL,
            execution_time_ms INTEGER NULL,
            tokens_used INTEGER NULL,
            cost_usd DECIMAL(10,6) NULL,
            success BOOLEAN NOT NULL DEFAULT 1,
            error_message TEXT NULL,
            model_used VARCHAR(50) NULL,
            created DATETIME NOT NULL,
            modified DATETIME NOT NULL
        )"
    );

    // comments table
    $conn->execute(
        "CREATE TABLE IF NOT EXISTS comments (
            id CHAR(36) PRIMARY KEY,
            foreign_key CHAR(36) NOT NULL,
            model VARCHAR(255) NOT NULL,
            user_id CHAR(36) NOT NULL,
            content TEXT NOT NULL,
            display BOOLEAN NOT NULL DEFAULT 1,
            is_inappropriate BOOLEAN NOT NULL DEFAULT 0,
            is_analyzed BOOLEAN NOT NULL DEFAULT 0,
            inappropriate_reason VARCHAR(300) NULL,
            created DATETIME NULL,
            modified DATETIME NULL,
            created_by CHAR(36) NULL,
            modified_by CHAR(36) NULL
        )"
    );

    // email_templates table
    $conn->execute(
        "CREATE TABLE IF NOT EXISTS email_templates (
            id CHAR(36) PRIMARY KEY,
            template_identifier VARCHAR(50) NULL,
            name VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body_html TEXT NULL,
            body_plain TEXT NULL,
            created DATETIME NOT NULL,
            modified DATETIME NOT NULL
        )"
    );
} catch (\Throwable $e) {
    // Ignore table creation issues in bootstrap; tests will surface actionable errors
}

// Force test env settings
Configure::write('debug', true);
