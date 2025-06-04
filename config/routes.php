// in config/routes.php
$routes->scope('/', function (RouteBuilder $routes): void {
    $routes->setExtensions(['json']);
    $routes->resources('Recipes');
});