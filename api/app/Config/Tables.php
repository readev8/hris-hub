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

    // Monitoring Hub — hr_selfservice (schema-qualified, single default connection)
    const MON_SESSION       = 'hr_selfservice.session';
    const MON_ASSIGNMENT    = 'hr_selfservice.assignment';
    const MON_ASSIGN_APPR   = 'hr_selfservice.assignment_approve';
    const MON_PPANELMT      = 'hr_selfservice.ppanelmt_nilai';
    const MON_FPKT          = 'hr_selfservice.fpkt';
    const MON_FPKT_JOBDESC  = 'hr_selfservice.fpkt_jobdesc';
    const MON_FPKT_LATIH    = 'hr_selfservice.fpkt_pelatihan';
    const MON_FPKT_VALUE    = 'hr_selfservice.fpkt_value';
    const MON_NB_ASSESS     = 'hr_selfservice.ninebox_assessment';
    const MON_NB_RTC        = 'hr_selfservice.ninebox_rtc';
    const MON_JOBCODE       = 'hr_selfservice.jobcode';
    const MON_IJIN          = 'hr_selfservice.pengajuan_ijin';
    const MON_IJIN_APPR     = 'hr_selfservice.pengajuan_ijin_approve';
    const MON_RESIGN        = 'hr_selfservice.pengajuan_resign';
    const MON_RESIGN_APPR   = 'hr_selfservice.pengajuan_resign_approve';
    const MON_PPANEL        = 'hr_selfservice.ppanel';
    const MON_SS            = 'hr_selfservice.ss';
    const MON_SS_APPR       = 'hr_selfservice.ss_approval';

    // Monitoring Hub — wine_hris (schema-qualified, single default connection)
    const MON_W_FPK         = 'wine_hris.fpk';
    const MON_W_FPK_APPR    = 'wine_hris.fpk_approve';
    const MON_W_SK_PENGAJ   = 'wine_hris.sk_pengajuan';
    const MON_W_FPMJ_APPR   = 'wine_hris.fpmj_approve';
    const MON_W_PANEL       = 'wine_hris.penilaianpanel';
    const MON_W_MEMO        = 'wine_hris.memo_keluar';
    const MON_W_SK          = 'wine_hris.sk';
    const MON_W_PEGAWAI     = 'wine_hris.pegawai';
    const MON_W_PELAMAR     = 'wine_hris.pelamar';
    const MON_W_PPMJ_APPR   = 'wine_hris.ppmj_approve';
    const MON_W_SP          = 'wine_hris.surat_peringatan';
    const MON_W_SJ          = 'wine_hris.surat_jamsostek';
    const MON_W_SR          = 'wine_hris.surat_referensi';
    const MON_W_KONTRAK     = 'wine_hris.kontrak';
}
