/**
 * ============================================================================
 * Shared Badge Helpers
 * ============================================================================
 *
 * Reusable badge and status rendering helpers for approvals, improvements,
 * blueprints, tickets, and other modules.
 *
 * Dependencies: None (pure utility functions)
 * Date: 2026-08-18
 */

/* global GlobalSanitize */

// ===========================
// CONSTANTS
// ===========================

const BADGE_CLASS_MAP = {
    'Open': 'open',
    'Approved': 'approved',
    'In Progress': 'in-progress',
    'Resolved': 'resolved',
    'Closed': 'closed',
    'Rejected': 'rejected',
    'Draft': 'draft',
    'Pending IT Approval': 'pending',
    'Pending Dept Approval': 'pending'
};

const PRIORITY_CLASS_MAP = {
    'Critical': 'critical',
    'High': 'high',
    'Medium': 'medium',
    'Low': 'low'
};

// ===========================
// PUBLIC API
// ===========================

function sapBadge(name) {
    const cls = BADGE_CLASS_MAP[name] || 'closed';
    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

function priorityDot(name) {
    const cls = PRIORITY_CLASS_MAP[name] || 'medium';
    return '<span class="priority-dot ' + cls + '" title="' + escHtml(name) + '"></span>';
}

function escHtml(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function escAttr(s) {
    return String(s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ===========================
// WINDOW EXPORTS
// ===========================

window.sapBadge = sapBadge;
window.priorityDot = priorityDot;
window.escHtml = escHtml;
window.escAttr = escAttr;
window.BADGE_CLASS_MAP = BADGE_CLASS_MAP;
window.PRIORITY_CLASS_MAP = PRIORITY_CLASS_MAP;
