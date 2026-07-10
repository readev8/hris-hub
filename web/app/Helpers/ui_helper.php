<?php

if (!function_exists('status_badge')) {
    function status_badge(string $statusName): string
    {
        $map = [
            'Open'        => 'open',
            'Approved'    => 'approved',
            'In Progress' => 'in-progress',
            'Resolved'    => 'resolved',
            'Closed'      => 'closed',
            'Rejected'    => 'rejected',
            'Pending'     => 'pending',
        ];
        $cls = $map[$statusName] ?? 'closed';
        $name = esc($statusName);
        return "<span class=\"sap-badge {$cls}\"><span class=\"badge-dot\"></span>{$name}</span>";
    }
}

if (!function_exists('priority_dot')) {
    function priority_dot(string $priorityName): string
    {
        $map = [
            'Critical' => 'critical',
            'High'     => 'high',
            'Medium'   => 'medium',
            'Low'      => 'low',
        ];
        $cls = $map[$priorityName] ?? 'medium';
        return "<span class=\"priority-dot {$cls}\"></span>";
    }
}

if (!function_exists('avatar_initials')) {
    function avatar_initials(string $name, string $size = 'sm', string $bg = '#0070F2'): string
    {
        $initial = strtoupper(substr($name, 0, 1));
        $class = $size === 'lg' ? 'avatar-circle-lg' : ($size === 'sm' ? 'avatar-circle-sm' : '');
        return "<div class=\"avatar-circle {$class}\" style=\"background:{$bg};color:#fff\">{$initial}</div>";
    }
}

if (!function_exists('priority_name')) {
    function priority_name(int $priority): string
    {
        $names = [0 => 'Low', 1 => 'Medium', 2 => 'High', 3 => 'Critical'];
        return $names[$priority] ?? 'Medium';
    }
}
