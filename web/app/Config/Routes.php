<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Auth::loginPage');
$routes->get('/login', 'Auth::loginPage');
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');

$routes->get('/request/get', 'Request::get');

$routes->get('/track', 'Tracking::index');
$routes->get('/track/(:any)', 'Tracking::lookup/$1');

$routes->group('', ['filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/dashboard', 'Dashboard::index');

    $routes->post('/auth/refresh-permissions', 'Auth::refreshPermissions');

    $routes->get('/tickets', 'Tickets::index');
    $routes->get('/tickets/ajax-list', 'Tickets::ajaxList');
    $routes->get('/tickets/create', 'Tickets::create');
    $routes->post('/tickets/create', 'Tickets::create');
    $routes->post('/tickets/(:any)/take', 'Tickets::take/$1');
    $routes->post('/tickets/(:any)/resolve', 'Tickets::resolve/$1');
    $routes->post('/tickets/(:any)/close', 'Tickets::close/$1');
    $routes->post('/tickets/(:any)/reopen', 'Tickets::reopen/$1');
    $routes->post('/tickets/(:any)/approve', 'Tickets::approve/$1');
    $routes->post('/tickets/(:any)/reject', 'Tickets::reject/$1');
    $routes->post('/tickets/(:any)/comments', 'Tickets::addComment/$1');
    $routes->post('/tickets/(:any)/upload-attachment', 'Tickets::uploadAttachment/$1');
    $routes->post('/attachments/(:any)/delete', 'Tickets::deleteAttachment/$1');
    $routes->get('/uploads/tickets/(:any)', 'Tickets::serveFile/$1');
    $routes->get('/tickets/(:any)/edit', 'Tickets::edit/$1');
    $routes->post('/tickets/(:any)/update', 'Tickets::update/$1');
    $routes->post('/tickets/(:any)/delete', 'Tickets::delete/$1');
    $routes->post('/tickets/(:any)/assign', 'Tickets::assign/$1');
    $routes->get('/tickets/(:any)', 'Tickets::detail/$1');

    $routes->get('/improvements', 'Improvements::index');
    $routes->get('/improvements/ajax-list', 'Improvements::ajaxList');
    $routes->get('/improvements/create', 'Improvements::create');
    $routes->post('/improvements/create', 'Improvements::create');
    $routes->post('/improvements/(:any)/approve-it', 'Improvements::approveIt/$1');
    $routes->post('/improvements/(:any)/approve-dept', 'Improvements::approveDept/$1');
    $routes->post('/improvements/(:any)/reject', 'Improvements::reject/$1');
    $routes->post('/improvements/(:any)/resubmit', 'Improvements::resubmit/$1');
    $routes->post('/improvements/(:any)/comments', 'Improvements::addComment/$1');
    $routes->get('/improvements/(:any)/edit', 'Improvements::edit/$1');
    $routes->post('/improvements/(:any)/update', 'Improvements::update/$1');
    $routes->post('/improvements/(:any)/delete', 'Improvements::delete/$1');
    $routes->post('/improvements/(:any)/attachments', 'Improvements::uploadAttachment/$1');
    $routes->get('/improvements/(:any)', 'Improvements::detail/$1');

    // Serve uploaded improvement attachments
    $routes->get('/uploads/improvements/(:any)', 'Improvements::serveFile/$1');

    $routes->get('/approvals', 'Approvals::index');
    $routes->get('/approvals/ajax-list', 'Approvals::ajaxList');

    $routes->get('/users', 'Users::index');
    $routes->get('/users/ajax-list', 'Users::ajaxList');

    // Master Projects — static routes must precede wildcards
    $routes->get('/master-projects',              'MasterProjects::index');
    $routes->get('/master-projects/ajax-list',    'MasterProjects::ajaxList');
    $routes->get('/master-projects/create',       'MasterProjects::create');
    $routes->post('/master-projects/create',      'MasterProjects::create');
    $routes->get('/master-projects/active',       'MasterProjects::getActive');
    $routes->get('/master-projects/(:any)/kanban','MasterProjects::getKanban/$1');
    $routes->get('/master-projects/(:any)/modules','MasterProjects::getModules/$1');
    $routes->get('/master-projects/(:any)/detail-json','MasterProjects::getDetail/$1');
    $routes->post('/master-projects/(:any)/update','MasterProjects::update/$1');
    $routes->post('/master-projects/(:any)/delete','MasterProjects::delete/$1');
    $routes->post('/master-projects/(:any)/modules','MasterProjects::createModule/$1');
    $routes->get('/master-projects/(:any)',       'MasterProjects::detail/$1');
    $routes->post('/modules/(:any)/update',       'MasterProjects::updateModule/$1');
    $routes->post('/modules/(:any)/delete',       'MasterProjects::deleteModule/$1');
    $routes->post('/modules/(:any)/pages',        'MasterProjects::createPage/$1');
    $routes->get('/modules/(:any)/pages',         'MasterProjects::getPages/$1');
    $routes->post('/pages/(:any)/update',         'MasterProjects::updatePage/$1');
    $routes->post('/pages/(:any)/delete',         'MasterProjects::deletePage/$1');
    $routes->get('/pages/(:any)/bugs',            'MasterProjects::getBugList/$1');
    $routes->post('/tickets/(:any)/move',         'Tickets::move/$1');

    // Roles & Permissions
    $routes->get('/roles',                        'Roles::index');
    $routes->get('/roles/ajax-list',              'Roles::ajaxList');
    $routes->post('/roles/create',                'Roles::create');
    $routes->post('/roles/(:any)/update',         'Roles::update/$1');
    $routes->post('/roles/(:any)/delete',         'Roles::delete/$1');
    $routes->get('/roles/(:any)/permissions',     'Roles::permissions/$1');
    $routes->post('/roles/(:any)/permissions',    'Roles::savePermissions/$1');
    $routes->post('/roles/(:any)/toggle',         'Roles::toggleActive/$1');
});
