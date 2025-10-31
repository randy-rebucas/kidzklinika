<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Check maintenance mode - in CI4 we'll handle this via a filter
$maintenanceMode = false; // Set via config
$maintenanceIPs = ['121.54.32.109', '121.54.32.134'];

if ($maintenanceMode && !in_array($_SERVER['REMOTE_ADDR'] ?? '', $maintenanceIPs)) {
    $routes->get('(:any)', 'Maintenance::index');
    $routes->setDefaultController('Maintenance');
} else {
    $routes->get('/', 'Welcome::index');
    $routes->get('controller/(:any)', 'Controller::index/$1');
    $routes->get('settings/(:any)', 'Settings::index/$1');
    $routes->get('my-patients', 'Patients::index');
    $routes->get('my-profile/(:any)', 'Settings::my_profile/$1');
    $routes->post('send-queries', 'Auth::send_queries');
    
    // Default controller
    $routes->setDefaultController('Welcome');
}
