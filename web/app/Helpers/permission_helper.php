<?php

if (!function_exists('has_permission')) {
    function has_permission(string $module, string $action = 'can_view'): bool
    {
        $perms = session('permissions') ?? [];
        return ($perms[$module][$action] ?? 0) === 1;
    }
}
