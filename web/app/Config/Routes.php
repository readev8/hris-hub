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

// Public anonymous ticket portal (no session required)
$routes->get('/public/tickets',                      'PublicTickets::list');
$routes->get('/public/tickets/ajax-list',            'PublicTickets::ajaxList');
$routes->get('/public/tickets/create',               'PublicTickets::create');
$routes->post('/public/tickets/create',              'PublicTickets::create');
$routes->get('/public/tickets/ajax-lookup/(:segment)','PublicTickets::ajaxLookup/$1');
$routes->get('/public/tickets/ajax-batch',           'PublicTickets::ajaxBatchLookup');
$routes->get('/public/tickets/ajax/projects',        'PublicTickets::ajaxProjects');
$routes->get('/public/tickets/ajax/modules/(:segment)','PublicTickets::ajaxModules/$1');
$routes->get('/public/tickets/ajax/pages/(:segment)','PublicTickets::ajaxPages/$1');
$routes->post('/public/tickets/(:any)/close',          'PublicTickets::ajaxClose/$1');
$routes->get('/public/attachment/(:any)',            'PublicTickets::serveAttachment/$1');
$routes->get('/public/tickets/(:any)',               'PublicTickets::detail/$1');

$routes->group('', ['filter' => 'sessionAuth'], static function ($routes) {
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/dashboard/ajax-stats', 'Dashboard::ajaxStats');

    $routes->get('/monitoring', 'Monitoring::index');
    $routes->get('/monitoring/ajax-stats', 'Monitoring::ajaxStats');
    $routes->get('/monitoring/ajax-list', 'Monitoring::ajaxList');
    $routes->get('/monitoring/ajax-trend', 'Monitoring::ajaxTrend');
    $routes->get('/monitoring/ajax-detail', 'Monitoring::ajaxDetail');
    $routes->get('/monitoring/ajax-activity', 'Monitoring::ajaxActivity');
    $routes->get('/monitoring/ajax-alerts', 'Monitoring::ajaxAlerts');
    $routes->get('/monitoring/export', 'Monitoring::export');

    $routes->post('/auth/refresh-permissions', 'Auth::refreshPermissions');

    $routes->get('/tickets', 'Tickets::index');
    $routes->get('/tickets/ajax-list', 'Tickets::ajaxList');
    $routes->get('/tickets/ajax/pages-search', 'Tickets::ajaxPagesSearch');
    $routes->get('/my-tickets', 'Tickets::myTickets');
    $routes->get('/my-tickets/ajax-list', 'Tickets::ajaxMyTickets');
    $routes->get('/tickets/chain', 'Tickets::chain');
    $routes->get('/tickets/ajax-chain', 'Tickets::ajaxChain');
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
    $routes->post('/tickets/(:any)/approve-it', 'Tickets::approveIt/$1');
    $routes->post('/tickets/(:any)/approve-dept', 'Tickets::approveDept/$1');
    $routes->post('/tickets/(:any)/reject-approval', 'Tickets::rejectApproval/$1');
    $routes->post('/tickets/(:any)/resubmit', 'Tickets::resubmit/$1');
    $routes->get('/tickets/(:any)/detail-json', 'Tickets::getDetailJson/$1');
    $routes->get('/tickets/(:any)/edit', 'Tickets::edit/$1');
    $routes->post('/tickets/(:any)/update', 'Tickets::update/$1');
    $routes->post('/tickets/(:any)/delete', 'Tickets::delete/$1');
    $routes->post('/tickets/(:any)/assign', 'Tickets::assign/$1');
    $routes->get('/tickets/(:any)', 'Tickets::detail/$1');

    $routes->get('/improvements', 'Improvements::index');
    $routes->get('/improvements/ajax-list', 'Improvements::ajaxList');
    $routes->get('/improvements/create', 'Improvements::create');
    $routes->post('/improvements/create', 'Improvements::create');
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

    // Blueprints — static routes must precede wildcards
    $routes->get('/blueprints',                          'Blueprints::index');
    $routes->get('/blueprints/ajax-list',                'Blueprints::ajaxList');
    $routes->get('/blueprints/create',                   'Blueprints::create');
    $routes->post('/blueprints/create',                  'Blueprints::create');
    $routes->post('/blueprints/(:any)/comments',         'Blueprints::addComment/$1');
    $routes->post('/blueprints/(:any)/modules',          'Blueprints::createModule/$1');
    $routes->post('/blueprints/modules/(:any)/update',   'Blueprints::updateModule/$1');
    $routes->post('/blueprints/modules/(:any)/delete',   'Blueprints::deleteModule/$1');
    $routes->post('/blueprints/modules/(:any)/business-scenarios',        'Blueprints::createBusinessScenario/$1');
    $routes->post('/blueprints/business-scenarios/(:any)/update',         'Blueprints::updateBusinessScenario/$1');
    $routes->post('/blueprints/business-scenarios/(:any)/delete',         'Blueprints::deleteBusinessScenario/$1');
    $routes->post('/blueprints/modules/(:any)/design-pages',              'Blueprints::createDesignPage/$1');
    $routes->post('/blueprints/design-pages/(:any)/update',               'Blueprints::updateDesignPage/$1');
    $routes->post('/blueprints/design-pages/(:any)/delete',               'Blueprints::deleteDesignPage/$1');
    $routes->get('/blueprints/design-pages/(:any)/specifications',        'Blueprints::pageSpecifications/$1');
    $routes->post('/blueprints/design-pages/(:any)/page-specifications',  'Blueprints::createPageSpecification/$1');
    $routes->post('/blueprints/page-specifications/(:any)/update',        'Blueprints::updatePageSpecification/$1');
    $routes->post('/blueprints/page-specifications/(:any)/delete',        'Blueprints::deletePageSpecification/$1');
    $routes->post('/blueprints/(:any)/attachments',       'Blueprints::uploadAttachment/$1');
    $routes->get('/blueprints/(:any)/edit',              'Blueprints::edit/$1');
    $routes->post('/blueprints/(:any)/update',           'Blueprints::update/$1');
    $routes->post('/blueprints/(:any)/delete',           'Blueprints::delete/$1');
    $routes->get('/uploads/blueprints/(:any)',           'Blueprints::serveFile/$1');
    $routes->get('/blueprints/(:any)/refresh',           'Blueprints::refreshDetail/$1');
    $routes->get('/blueprints/(:any)',                   'Blueprints::detail/$1');

    $routes->get('/approvals', 'Approvals::index');
    $routes->get('/approvals/ajax-list', 'Approvals::ajaxList');

    $routes->get('/users', 'Users::index');
    $routes->get('/users/ajax-list', 'Users::ajaxList');
    $routes->get('/users/add', 'Users::addUserPage');
    $routes->post('/users/ajax-lookup', 'Users::ajaxLookupUser');
    $routes->post('/users/ajax-add-by-userid', 'Users::ajaxAddByUserid');
    $routes->get('/users/ajax-detail', 'Users::ajaxDetail');
    $routes->get('/users/ajax-search-hris', 'Users::ajaxSearchHris');
    $routes->post('/users/ajax-update', 'Users::ajaxUpdate');
    $routes->post('/users/ajax-toggle', 'Users::ajaxToggle');
    $routes->post('/users/ajax-delete', 'Users::ajaxDelete');

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
    $routes->get('/master-projects/(:any)/edit',  'MasterProjects::edit/$1');
    $routes->post('/master-projects/(:any)/edit', 'MasterProjects::edit/$1');
    $routes->get('/master-projects/(:segment)/modules/(:segment)/pages/(:segment)', 'MasterProjects::pageDetail/$1/$2/$3');
    $routes->get('/master-projects/(:segment)/modules/(:segment)', 'MasterProjects::moduleDetail/$1/$2');
    $routes->get('/master-projects/(:any)',        'MasterProjects::detail/$1');
    $routes->post('/modules/(:any)/update',       'MasterProjects::updateModule/$1');
    $routes->post('/modules/(:any)/delete',       'MasterProjects::deleteModule/$1');
    $routes->post('/modules/(:any)/assign-blueprint',  'MasterProjects::assignBlueprintModule/$1');
    $routes->post('/modules/(:any)/unassign-blueprint', 'MasterProjects::unassignBlueprintModule/$1');
    $routes->post('/modules/(:any)/import-design-pages', 'MasterProjects::importDesignPages/$1');
    $routes->get('/blueprint-modules/available',       'MasterProjects::getAvailableBlueprintModules');
    $routes->post('/modules/(:any)/pages',        'MasterProjects::createPage/$1');
    $routes->get('/modules/(:any)/pages',         'MasterProjects::getPages/$1');
    $routes->post('/pages/(:any)/update',         'MasterProjects::updatePage/$1');
    $routes->post('/pages/(:any)/delete',         'MasterProjects::deletePage/$1');
    $routes->post('/pages/(:any)/assign-design-page',   'MasterProjects::assignBlueprintDesignPage/$1');
    $routes->post('/pages/(:any)/unassign-design-page', 'MasterProjects::unassignBlueprintDesignPage/$1');
    $routes->get('/design-pages/available',             'MasterProjects::getAvailableDesignPages');
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

    // Module Flows
    $routes->get('/module-flows',                                    'ModuleFlows::index');
    $routes->get('/module-flows/ajax-canvas',                        'ModuleFlows::ajaxGetCanvas');
    $routes->post('/module-flows/ajax-add-module',                   'ModuleFlows::ajaxAddModule');
    $routes->post('/module-flows/ajax-update-position/(:any)',       'ModuleFlows::ajaxUpdatePosition/$1');
    $routes->post('/module-flows/ajax-update-module/(:any)',        'ModuleFlows::ajaxUpdateModule/$1');
    $routes->post('/module-flows/ajax-remove-module/(:any)',         'ModuleFlows::ajaxRemoveModule/$1');
    $routes->post('/module-flows/ajax-add-connection',               'ModuleFlows::ajaxAddConnection');
    $routes->post('/module-flows/ajax-delete-connection/(:any)',     'ModuleFlows::ajaxDeleteConnection/$1');
});
