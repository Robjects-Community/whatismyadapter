<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

// Check platform requirements
require dirname(__DIR__) . '/vendor/autoload.php';

// Load path constants
require dirname(__DIR__) . '/config/paths.php';

// Note: Core bootstrap is loaded by Application::bootstrap() via parent::bootstrap()
// Removed: require CORE_PATH . 'config' . DS . 'bootstrap.php';

use Cake\Http\Server;

// Bind your application to the server.
$server = new Server(new \App\Application(CONFIG));

// Run the request/response through the application and emit the response.
$server->emit($server->run());
