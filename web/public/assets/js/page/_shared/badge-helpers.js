/**
 * ============================================================================
 * Shared Badge Helpers
 * ============================================================================
 *
 * Description: Reusable badge and status rendering helpers
 * Date: 2026-07-16
 * Used by: approvals, improvements, blueprints, tickets
 */

// ===========================
// CONSTANTS
// ===========================

var BADGE_CLASS_MAP = {
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

var PRIORITY_CLASS_MAP = {
    'Critical': 'critical',
    'High': 'high',
    'Medium': 'medium',
    'Low': 'low'
};

// ===========================
// PUBLIC API
// ===========================

function sapBadge(name) {
    var cls = BADGE_CLASS_MAP[name] || 'closed';
    return '<span class="sap-badge ' + cls + '"><span class="badge-dot"></span>' + name + '</span>';
}

function priorityDot(name) {
    var cls = PRIORITY_CLASS_MAP[name] || 'medium';
    return '<span class="priority-dot ' + cls + '" title="' + name + '"></span>';
}

function escHtml(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function escAttr(s) {
    return String(s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
