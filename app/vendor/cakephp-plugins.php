<?php
$baseDir = dirname(dirname(__FILE__));

return [
    'plugins' => [
        'ADmad/I18n' => $baseDir . '/vendor/admad/cakephp-i18n/',
        'AdminTheme' => $baseDir . '/plugins/AdminTheme/',
        'Authentication' => $baseDir . '/vendor/cakephp/authentication/',
        'Authorization' => $baseDir . '/vendor/cakephp/authorization/',
        'Bake' => $baseDir . '/vendor/cakephp/bake/',
        'Cake/Queue' => $baseDir . '/vendor/cakephp/queue/',
        'Cake/TwigView' => $baseDir . '/vendor/cakephp/twig-view/',
        'ContactManager' => $baseDir . '/plugins/ContactManager/',
        'DebugKit' => $baseDir . '/vendor/cakephp/debug_kit/',
        'DefaultTheme' => $baseDir . '/plugins/DefaultTheme/',
        'Josegonzalez/Upload' => $baseDir . '/vendor/josegonzalez/cakephp-upload/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
    ],
];
