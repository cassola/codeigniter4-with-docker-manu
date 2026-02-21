<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->get('dashboard', 'Dashboard::index');
$routes->get('lang/(:segment)', 'Language::set/$1');
$routes->get('settings', 'Settings::index');
$routes->post('settings', 'Settings::save');

$routes->get('tickets', 'Tickets::index');
$routes->get('tickets/new', 'Tickets::new');
$routes->post('tickets', 'Tickets::create');
$routes->get('tickets/(:num)', 'Tickets::show/$1');
$routes->post('tickets/(:num)/status', 'Tickets::updateStatus/$1');
$routes->post('tickets/(:num)/transfer', 'Tickets::transfer/$1');
$routes->post('tickets/(:num)/materials', 'Tickets::addMaterial/$1');

$routes->get('assets', 'Assets::index');
$routes->get('customers', 'Customers::index');
$routes->get('master-data', 'MasterData::index');
$routes->get('admin/config', 'Admin::config');
