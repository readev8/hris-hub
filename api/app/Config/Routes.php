<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes (no auth)
// $routes->post('auth/login', 'Auth\Action\Auth::login'); // DEPRECATED: Removed for security. Use web Auth controller instead.
$routes->get('tickets/track/(:any)', 'Tickets\Report\TicketTracking::get_by_code/$1', ['filter' => 'ratelimit']);

// Public anonymous ticket routes (no apikeyauth, ratelimited)
$routes->post('tickets/public/create',                       'Tickets\Action\PublicTickets::create', ['filter' => ['cors', 'ratelimit']]);
$routes->get('tickets/public/list',                          'Tickets\Data\PublicTicketDetail::get_list', ['filter' => ['cors', 'ratelimit']]);
$routes->get('tickets/public/by-code/(:any)',                 'Tickets\Data\PublicTicketDetail::get_by_code/$1', ['filter' => ['cors', 'ratelimit']]);
$routes->get('tickets/public/batch-by-codes',                 'Tickets\Data\PublicTicketDetail::get_batch_by_codes', ['filter' => ['cors', 'ratelimit']]);
$routes->post('tickets/public/(:any)/close',                  'Tickets\Data\PublicTicketDetail::close_by_code/$1', ['filter' => ['cors', 'ratelimit']]);
$routes->post('tickets/public/(:any)/attachments',            'Tickets\Action\PublicAttachments::add/$1', ['filter' => ['cors', 'ratelimit']]);
$routes->get('master-projects/public/active',                 'MasterProjects\Report\PublicMasterProjectList::get_active', ['filter' => ['cors', 'ratelimit']]);
$routes->get('master-projects/public/(:any)/modules',         'MasterProjects\Report\PublicMasterProjectList::get_modules/$1', ['filter' => ['cors', 'ratelimit']]);
$routes->get('modules/public/(:any)/pages',                   'MasterProjects\Report\PublicMasterProjectList::get_pages/$1', ['filter' => ['cors', 'ratelimit']]);

// Protected API routes
$routes->group('', ['filter' => ['cors', 'apikeyauth']], static function ($routes) {
    // Auth
    $routes->get('auth/me', 'Auth\Action\Auth::me');
    $routes->post('auth/local-user', 'Auth\Action\Auth::local_user');

    // Tickets
    $routes->post('tickets/create',              'Tickets\Action\Tickets::create_ticket');
    $routes->post('tickets/(:any)/approve-it',    'Tickets\Action\Tickets::approve_it/$1');
    $routes->post('tickets/(:any)/approve-dept',  'Tickets\Action\Tickets::approve_dept/$1');
    $routes->post('tickets/(:any)/reject-approval','Tickets\Action\Tickets::reject_approval/$1');
    $routes->post('tickets/(:any)/resubmit',      'Tickets\Action\Tickets::resubmit/$1');
    $routes->post('tickets/(:any)/update',       'Tickets\Action\Tickets::update_ticket/$1');
    $routes->post('tickets/(:any)/approve',      'Tickets\Action\Tickets::approve_ticket/$1');
    $routes->post('tickets/(:any)/reject',       'Tickets\Action\Tickets::reject_ticket/$1');
    $routes->post('tickets/(:any)/take',         'Tickets\Action\Tickets::take_ticket/$1');
    $routes->post('tickets/(:any)/resolve',      'Tickets\Action\Tickets::resolve_ticket/$1');
    $routes->post('tickets/(:any)/close',        'Tickets\Action\Tickets::close_ticket/$1');
    $routes->post('tickets/(:any)/reopen',       'Tickets\Action\Tickets::reopen_ticket/$1');
    $routes->post('tickets/(:any)/comments',     'Tickets\Action\Tickets::add_comment/$1');
    $routes->post('tickets/(:any)/attachments',  'Tickets\Action\Attachments::add/$1');
    $routes->get('tickets/my-taken',             'Tickets\Report\TicketList::get_my_taken_tickets');
    $routes->get('tickets/chain',                'Tickets\Data\TicketChain::get_chain');
    $routes->get('tickets/pending-approval',     'Tickets\Report\TicketList::get_pending_approval');
    $routes->get('tickets/my-tickets',           'Tickets\Report\TicketList::get_my_tickets');
    $routes->get('tickets/(:any)',               'Tickets\Data\TicketDetail::get_detail/$1');
    $routes->get('tickets',                      'Tickets\Report\TicketList::get_list');

    // Improvements
    $routes->post('improvements/create',                 'Improvements\Action\Projects::create_improvement');
    $routes->post('improvements/(:any)/update',          'Improvements\Action\Projects::update_improvement/$1');
    $routes->post('improvements/(:any)/delete',          'Improvements\Action\Projects::delete_improvement/$1');
    $routes->post('improvements/(:any)/approve-dept',    'Improvements\Action\Projects::approve_dept/$1');
    $routes->post('improvements/(:any)/reject',          'Improvements\Action\Projects::reject/$1');
    $routes->post('improvements/(:any)/resubmit',        'Improvements\Action\Projects::resubmit/$1');
    $routes->post('improvements/(:any)/comments',        'Improvements\Action\Projects::add_comment/$1');
    $routes->post('improvements/(:any)/attachments',     'Improvements\Action\Attachments::add/$1');
    $routes->delete('improvements/(:any)/attachments/(:any)', 'Improvements\Action\Attachments::delete/$1/$2');
    $routes->get('improvements/user-types',              'Improvements\Action\Projects::get_user_types');
    $routes->get('improvements/pending-approvals',       'Improvements\Report\ProjectList::get_pending_approvals');
    $routes->get('improvements/(:any)',                  'Improvements\Data\ProjectDetail::get_detail/$1');
    $routes->get('improvements',                         'Improvements\Report\ProjectList::get_list');

    // Dashboard
    $routes->get('dashboard/stats', 'Dashboard\Report\Stats::get_stats');

    // Monitoring Hub
    $routes->get('monitoring/stats',  'Monitoring\Report\Stats::get_stats');
    $routes->get('monitoring/list',   'Monitoring\Report\ListReport::get_list');
    $routes->get('monitoring/detail', 'Monitoring\Data\Detail::get_detail');
    $routes->get('monitoring/trend',  'Monitoring\Report\Trend::get_trend');
    $routes->get('monitoring/export', 'Monitoring\Report\Export::get_export');

    // Audit log
    $routes->get('audit-log/recent',      'AuditLog\Report\LogList::get_recent_logs');
    $routes->get('audit-log/entity',       'AuditLog\Report\LogList::get_entity_logs');

    // Attachments
    $routes->delete('attachments/(:any)',   'Tickets\Action\Attachments::delete/$1');

    // Users (Admin) — exact routes before wildcards
    $routes->get('users',                           'Users\Action\Users::get_list');
    $routes->post('users',                          'Users\Action\Users::get_list');
    $routes->get('users/search-hris',               'Users\Action\Users::search_hris');
    $routes->post('users/create',                   'Users\Action\Users::create');
    $routes->post('users/lookup',                   'Users\Action\Users::lookup_user');
    $routes->post('users/add-by-userid',            'Users\Action\Users::add_by_userid');
    $routes->get('users/(:any)',                    'Users\Action\Users::get_detail/$1');
    $routes->post('users/(:any)/update',            'Users\Action\Users::update_user/$1');
    $routes->post('users/(:any)/toggle',            'Users\Action\Users::toggle_active/$1');
    $routes->post('users/(:any)/delete',            'Users\Action\Users::delete_user/$1');

    // Master Projects
    $routes->get('master-projects',                    'MasterProjects\Report\MasterProjectList::get_list');
    $routes->get('master-projects/active',             'MasterProjects\Report\MasterProjectList::get_active');
    $routes->get('master-projects/(:any)/kanban',      'MasterProjects\Report\MasterProjectList::get_kanban/$1');
    $routes->get('master-projects/(:any)/modules',     'MasterProjects\Report\MasterProjectList::get_modules/$1');
    $routes->get('master-projects/(:any)',             'MasterProjects\Data\MasterProjectDetail::get_detail/$1');
    $routes->get('modules/(:any)/pages',               'MasterProjects\Report\MasterProjectList::get_pages/$1');
    $routes->get('modules/(:any)',                     'MasterProjects\Data\ModuleDetail::get_detail/$1');
    $routes->get('pages/(:any)/bugs',                  'MasterProjects\Report\MasterProjectList::get_bugs/$1');
    $routes->get('pages/public/search',                'Tickets\Data\PageSearch::search');
    $routes->get('pages/(:any)',                       'MasterProjects\Data\PageDetail::get_detail/$1');
    $routes->post('tickets/(:any)/move',               'Tickets\Action\Tickets::move_ticket/$1');
    $routes->post('master-projects/create',            'MasterProjects\Action\Projects::create_project');
    $routes->post('master-projects/(:any)/update',     'MasterProjects\Action\Projects::update_project/$1');
    $routes->post('master-projects/(:any)/delete',     'MasterProjects\Action\Projects::delete_project/$1');
    $routes->post('master-projects/(:any)/modules',    'MasterProjects\Action\Modules::create_module/$1');
    $routes->post('modules/(:any)/update',             'MasterProjects\Action\Modules::update_module/$1');
    $routes->post('modules/(:any)/delete',             'MasterProjects\Action\Modules::delete_module/$1');
    $routes->post('modules/(:any)/assign-blueprint',   'MasterProjects\Action\Modules::assign_blueprint_module/$1');
    $routes->post('modules/(:any)/unassign-blueprint', 'MasterProjects\Action\Modules::unassign_blueprint_module/$1');
    $routes->post('modules/(:any)/import-design-pages', 'MasterProjects\Action\Pages::import_design_pages/$1');
    $routes->get('blueprint-modules/available',         'MasterProjects\Report\MasterProjectList::get_available_blueprint_modules');
    $routes->post('modules/(:any)/pages',              'MasterProjects\Action\Pages::create_page/$1');
    $routes->post('pages/(:any)/update',               'MasterProjects\Action\Pages::update_page/$1');
    $routes->post('pages/(:any)/delete',               'MasterProjects\Action\Pages::delete_page/$1');
    $routes->post('pages/(:any)/assign-design-page',   'MasterProjects\Action\Pages::assign_blueprint_design_page/$1');
    $routes->post('pages/(:any)/unassign-design-page', 'MasterProjects\Action\Pages::unassign_blueprint_design_page/$1');
    $routes->get('design-pages/available',              'MasterProjects\Report\MasterProjectList::get_available_design_pages');

    // Blueprints
    $routes->get('blueprints',                                          'Blueprints\Report\BlueprintList::get_list');
    $routes->get('blueprints/pending',                                  'Blueprints\Report\BlueprintList::get_pending_approvals');
    $routes->get('blueprints/design-pages/(:any)',                      'Blueprints\Data\DesignPageDetail::get_detail/$1');
    $routes->get('blueprints/(:any)',                                   'Blueprints\Data\BlueprintDetail::get_detail/$1');

    // Blueprints — exact routes first
    $routes->post('blueprints/create',                                  'Blueprints\Action\Blueprints::create');

    // Blueprints — specific sub-entity routes (BEFORE wildcards)
    $routes->post('blueprints/modules/(:any)/update',                   'Blueprints\Action\Modules::update/$1');
    $routes->post('blueprints/modules/(:any)/delete',                   'Blueprints\Action\Modules::delete/$1');
    $routes->post('blueprints/modules/(:any)/business-scenarios',       'Blueprints\Action\BusinessScenarios::create/$1');
    $routes->post('blueprints/modules/(:any)/design-pages',             'Blueprints\Action\DesignPages::create/$1');
    $routes->post('blueprints/business-scenarios/(:any)/update',        'Blueprints\Action\BusinessScenarios::update/$1');
    $routes->post('blueprints/business-scenarios/(:any)/delete',        'Blueprints\Action\BusinessScenarios::delete/$1');
    $routes->post('blueprints/design-pages/(:any)/update',              'Blueprints\Action\DesignPages::update/$1');
    $routes->post('blueprints/design-pages/(:any)/delete',              'Blueprints\Action\DesignPages::delete/$1');
    $routes->post('blueprints/design-pages/(:any)/page-specifications',  'Blueprints\Action\PageSpecifications::create/$1');
    $routes->post('blueprints/page-specifications/(:any)/update',       'Blueprints\Action\PageSpecifications::update/$1');
    $routes->post('blueprints/page-specifications/(:any)/delete',       'Blueprints\Action\PageSpecifications::delete/$1');

    // Blueprints — wildcard blueprint-level routes (LAST)
    $routes->post('blueprints/(:any)/update',                           'Blueprints\Action\Blueprints::update/$1');
    $routes->post('blueprints/(:any)/delete',                           'Blueprints\Action\Blueprints::delete/$1');
    $routes->post('blueprints/(:any)/approve-it',                       'Blueprints\Action\Blueprints::approve_it/$1');
    $routes->post('blueprints/(:any)/approve-dept',                     'Blueprints\Action\Blueprints::approve_dept/$1');
    $routes->post('blueprints/(:any)/reject',                           'Blueprints\Action\Blueprints::reject/$1');
    $routes->post('blueprints/(:any)/resubmit',                         'Blueprints\Action\Blueprints::resubmit/$1');
    $routes->post('blueprints/(:any)/comments',                         'Blueprints\Action\Blueprints::add_comment/$1');
    $routes->post('blueprints/(:any)/attachments',                      'Blueprints\Action\Attachments::add/$1');
    $routes->delete('blueprints/(:any)/attachments/(:any)',             'Blueprints\Action\Attachments::delete/$1/$2');
    $routes->post('blueprints/(:any)/modules',                          'Blueprints\Action\Modules::create/$1');

    // Roles & Permissions
    $routes->get('roles',                              'Roles\Report\RoleList::get_list');
    $routes->get('roles/modules/list',                 'Roles\Report\RoleList::get_modules');
    $routes->get('roles/by-id/(:num)/permissions',     'Roles\Action\Roles::get_permissions_by_id/$1');
    $routes->get('roles/(:any)',                       'Roles\Report\RoleList::get_detail/$1');
    $routes->get('permissions/me',                     'Roles\Report\RoleList::get_user_permissions');
    $routes->post('roles/create',                      'Roles\Action\Roles::create_role');
    $routes->post('roles/(:any)/update',               'Roles\Action\Roles::update_role/$1');
    $routes->post('roles/(:any)/delete',               'Roles\Action\Roles::delete_role/$1');
    $routes->post('roles/(:any)/permissions',          'Roles\Action\Roles::save_permissions/$1');
    $routes->post('roles/(:any)/toggle',               'Roles\Action\Roles::toggle_active/$1');

    // Module Flows
    $routes->get('module-flows/canvas',                            'ModuleFlows\Data\FlowCanvas::get_canvas');
    $routes->post('module-flows/canvas/modules',                   'ModuleFlows\Action\FlowActions::add_module');
    $routes->post('module-flows/canvas/modules/(:any)/position',   'ModuleFlows\Action\FlowActions::update_position/$1');
    $routes->post('module-flows/canvas/modules/(:any)/label',     'ModuleFlows\Action\FlowActions::update_module/$1');
    $routes->post('module-flows/canvas/modules/(:any)/remove',     'ModuleFlows\Action\FlowActions::remove_module/$1');
    $routes->post('module-flows/connections',                      'ModuleFlows\Action\FlowActions::add_connection');
    $routes->post('module-flows/connections/(:any)/delete',        'ModuleFlows\Action\FlowActions::delete_connection/$1');
});
