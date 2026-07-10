<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes (no auth)
$routes->post('auth/login', 'Auth\Action\Auth::login');

// Protected API routes
$routes->group('', ['filter' => ['cors', 'apikeyauth']], static function ($routes) {
    // Auth
    $routes->get('auth/me', 'Auth\Action\Auth::me');

    // Tickets
    $routes->post('tickets/create',              'Tickets\Action\Tickets::create_ticket');
    $routes->post('tickets/(:any)/approve',      'Tickets\Action\Tickets::approve_ticket/$1');
    $routes->post('tickets/(:any)/reject',       'Tickets\Action\Tickets::reject_ticket/$1');
    $routes->post('tickets/(:any)/take',         'Tickets\Action\Tickets::take_ticket/$1');
    $routes->post('tickets/(:any)/resolve',      'Tickets\Action\Tickets::resolve_ticket/$1');
    $routes->post('tickets/(:any)/close',        'Tickets\Action\Tickets::close_ticket/$1');
    $routes->post('tickets/(:any)/reopen',       'Tickets\Action\Tickets::reopen_ticket/$1');
    $routes->post('tickets/(:any)/comments',     'Tickets\Action\Tickets::add_comment/$1');
    $routes->post('tickets/(:any)/attachments',  'Tickets\Action\Attachments::add/$1');
    $routes->get('tickets/(:any)',               'Tickets\Data\TicketDetail::get_detail/$1');
    $routes->get('tickets',                      'Tickets\Report\TicketList::get_list');
    $routes->get('tickets/pending-approval',     'Tickets\Report\TicketList::get_pending_approval');
    $routes->get('tickets/my-tickets',           'Tickets\Report\TicketList::get_my_tickets');

    // Improvements
    $routes->post('improvements/create',                 'Improvements\Action\Projects::create_improvement');
    $routes->post('improvements/(:any)/approve-it',      'Improvements\Action\Projects::approve_it/$1');
    $routes->post('improvements/(:any)/approve-dept',    'Improvements\Action\Projects::approve_dept/$1');
    $routes->post('improvements/(:any)/reject',          'Improvements\Action\Projects::reject/$1');
    $routes->post('improvements/(:any)/resubmit',        'Improvements\Action\Projects::resubmit/$1');
    $routes->post('improvements/(:any)/comments',        'Improvements\Action\Projects::add_comment/$1');
    $routes->get('improvements/(:any)',                  'Improvements\Data\ProjectDetail::get_detail/$1');
    $routes->get('improvements',                         'Improvements\Report\ProjectList::get_list');
    $routes->get('improvements/pending-approvals',       'Improvements\Report\ProjectList::get_pending_approvals');

    // Dashboard
    $routes->get('dashboard/stats', 'Dashboard\Report\Stats::get_stats');

    // Audit log
    $routes->get('audit-log/recent',      'AuditLog\Report\LogList::get_recent_logs');
    $routes->get('audit-log/entity',       'AuditLog\Report\LogList::get_entity_logs');

    // Attachments
    $routes->delete('attachments/(:any)',   'Tickets\Action\Attachments::delete/$1');

    // Users (Admin)
    $routes->get('users',                  'Users\Action\Users::get_list');
    $routes->post('users/create',          'Users\Action\Users::create');
    $routes->post('users/(:any)/toggle',   'Users\Action\Users::toggle_active/$1');

    // Master Projects
    $routes->get('master-projects',                    'MasterProjects\Report\MasterProjectList::get_list');
    $routes->get('master-projects/active',             'MasterProjects\Report\MasterProjectList::get_active');
    $routes->get('master-projects/(:any)/modules',     'MasterProjects\Report\MasterProjectList::get_modules/$1');
    $routes->get('master-projects/(:any)',             'MasterProjects\Data\MasterProjectDetail::get_detail/$1');
    $routes->get('modules/(:any)/pages',               'MasterProjects\Report\MasterProjectList::get_pages/$1');
    $routes->get('pages/(:any)/bugs',                  'MasterProjects\Report\MasterProjectList::get_bugs/$1');
    $routes->post('master-projects/create',            'MasterProjects\Action\Projects::create_project');
    $routes->post('master-projects/(:any)/update',     'MasterProjects\Action\Projects::update_project/$1');
    $routes->post('master-projects/(:any)/delete',     'MasterProjects\Action\Projects::delete_project/$1');
    $routes->post('master-projects/(:any)/modules',    'MasterProjects\Action\Modules::create_module/$1');
    $routes->post('modules/(:any)/update',             'MasterProjects\Action\Modules::update_module/$1');
    $routes->post('modules/(:any)/delete',             'MasterProjects\Action\Modules::delete_module/$1');
    $routes->post('modules/(:any)/pages',              'MasterProjects\Action\Pages::create_page/$1');
    $routes->post('pages/(:any)/update',               'MasterProjects\Action\Pages::update_page/$1');
    $routes->post('pages/(:any)/delete',               'MasterProjects\Action\Pages::delete_page/$1');
});
