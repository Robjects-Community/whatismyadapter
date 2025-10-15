<?php
/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */
return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', '8831764ad771299067333a9779c3a9818d0309dbbd797fdcdf175366486ed397'),
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => env('DB_HOST', 'mysql'),
            'username' => env('DB_USERNAME', 'cms_user'),
            'password' => env('DB_PASSWORD', 'password'),
            'database' => env('DB_DATABASE', 'cms'),
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'port' => env('DB_PORT', 3306)
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => env('TEST_DB_HOST', 'mysql'),
            'username' => env('TEST_DB_USERNAME', 'cms_user_test'),
            'password' => env('TEST_DB_PASSWORD', 'password'),
            'database' => env('TEST_DB_DATABASE', 'cms_test'),
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'port' => env('TEST_DB_PORT', 3306)
        ],

        /*
         * DigitalOcean production database connection with SSL
         */
        'digitalocean' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => env('DO_DB_HOST', 'private-cms-mysql-test-do-user-25344929-0.e.db.ondigitalocean.com'),
            'username' => env('DO_DB_USERNAME', 'cms_user'),
            'password' => env('DO_DB_PASSWORD', ''),
            'database' => env('DO_DB_DATABASE', 'cms'),
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'port' => env('DO_DB_PORT', 25060),
            'flags' => [
                // SSL configuration for DigitalOcean managed database
                PDO::MYSQL_ATTR_SSL_CA => env('DO_DB_SSL_CA', '/var/www/html/config/certs/digitalocean-ca.crt'),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => filter_var(env('DO_DB_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
            ],
        ],

        /*
         * DigitalOcean test database connection with SSL
         */
        'digitalocean_test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => env('DO_TEST_DB_HOST', 'private-cms-mysql-test-do-user-25344929-0.e.db.ondigitalocean.com'),
            'username' => env('DO_TEST_DB_USERNAME', 'cms_user_test'),
            'password' => env('DO_TEST_DB_PASSWORD', ''),
            'database' => env('DO_TEST_DB_DATABASE', 'cms_test'),
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'port' => env('DO_TEST_DB_PORT', 25060),
            'flags' => [
                // SSL configuration for DigitalOcean managed database
                PDO::MYSQL_ATTR_SSL_CA => env('DO_TEST_DB_SSL_CA', '/var/www/html/config/certs/digitalocean-ca.crt'),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => filter_var(env('DO_TEST_DB_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
            ],
        ],
    ],

    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'default' => [
            'host' => env('EMAIL_HOST','mailpit'),
            'port' => env('EMAIL_PORT', '1025'),
            'username' => env('EMAIL_USERNAME', ''),
            'password' => env('EMAIL_PASSWORD', ''),
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL'),
        ],
    ],
];
