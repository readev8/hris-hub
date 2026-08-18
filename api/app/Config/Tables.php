<?php

namespace Config;

class Tables
{
    // Users & Authentication
    const USERS                = 'hrhub_users';
    const ROLES                = 'hrhub_roles';
    const ROLE_PERMISSIONS     = 'hrhub_role_permissions';
    const ACCESS_MODULES       = 'hrhub_access_modules';
    const API_KEYS             = 'hrhub_api_keys';
    const MASTER_USER_TYPES    = 'hrhub_master_user_types';

    // Tickets
    const TICKETS              = 'hrhub_tickets';
    const TICKET_COMMENTS      = 'hrhub_ticket_comments';
    const TICKET_ATTACHMENTS   = 'hrhub_ticket_attachments';

    // Projects / Improvements
    const PROJECTS             = 'hrhub_projects';
    const APPROVAL_REQUESTS    = 'hrhub_approval_requests';
    const PROJECT_COMMENTS     = 'hrhub_project_comments';
    const PROJECT_ATTACHMENTS  = 'hrhub_project_attachments';
    const PROJECT_USER_TYPES   = 'hrhub_project_user_types';

    // Master Projects
    const MASTER_PROJECTS      = 'hrhub_master_projects';
    const MODULES              = 'hrhub_modules';
    const PAGES                = 'hrhub_pages';
    const MODULE_BLUEPRINT_MODULES = 'hrhub_module_blueprint_modules';

    // Blueprints
    const BLUEPRINTS                   = 'hrhub_blueprints';
    const BLUEPRINT_MODULES            = 'hrhub_blueprint_modules';
    const BLUEPRINT_BUSINESS_SCENARIOS = 'hrhub_blueprint_business_scenarios';
    const BLUEPRINT_DESIGN_PAGES       = 'hrhub_blueprint_design_pages';
    const BLUEPRINT_PAGE_SPECIFICATIONS = 'hrhub_blueprint_page_specifications';
    const BLUEPRINT_APPROVAL_REQUESTS  = 'hrhub_blueprint_approval_requests';
    const BLUEPRINT_COMMENTS           = 'hrhub_blueprint_comments';
    const BLUEPRINT_ATTACHMENTS        = 'hrhub_blueprint_attachments';

    // Audit
    const AUDIT_LOGS           = 'hrhub_audit_logs';

    // Module Flows
    const FLOW_CONNECTIONS     = 'hrhub_flow_connections';
    const FLOW_NODE_POSITIONS  = 'hrhub_flow_node_positions';
}
