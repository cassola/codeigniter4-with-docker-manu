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

$routes->group('app', static function ($routes) {
    $routes->get('dashboard', 'RepairApp::dashboard');
    $routes->get('tickets', 'RepairApp::ticketsList');
    $routes->get('ticket/(:num)', 'RepairApp::ticketDetail/$1');
    $routes->post('ticket/(:num)/change-status', 'RepairApp::changeStatus/$1');
    $routes->post('ticket/(:num)/transfer-site', 'RepairApp::transferSite/$1');
    $routes->post('ticket/(:num)/return-out', 'RepairApp::registerReturnOut/$1');
    $routes->post('ticket/(:num)/return-in', 'RepairApp::registerReturnIn/$1');
    $routes->post('ticket/(:num)/exchange', 'RepairApp::createExchangeFromTicket/$1');
    $routes->match(['get', 'post'], 'ticket-create', 'RepairApp::ticketCreate');
    $routes->get('assets', 'RepairApp::assetsList');
    $routes->get('asset/(:num)', 'RepairApp::asset360/$1');
    $routes->get('movements', 'RepairApp::movementsList');
    $routes->match(['get', 'post'], 'movements/create', 'RepairApp::movementCreate');
    $routes->get('parts-requests', 'RepairApp::partsRequestsList');
    $routes->match(['get', 'post'], 'exchange/create', 'RepairApp::exchangeCreate');
    $routes->get('nonconformities', 'RepairApp::nonConformitiesList');
    $routes->get('people', 'RepairApp::peopleAdmin');
    $routes->match(['get', 'post'], 'settings/user', 'RepairApp::settingsUser');
    $routes->get('settings/system', 'RepairApp::settingsSystem');
    $routes->get('search', 'RepairApp::search');
});
