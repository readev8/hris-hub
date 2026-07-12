<?php

helper('form');
?>
<?php if (!function_exists('has_permission')): ?>
<?php
function has_permission(string $module, string $action = 'can_view'): bool
{
    $perms = session('permissions') ?? [];
    return ($perms[$module][$action] ?? 0) === 1;
}
?>
<?php endif; ?>
<?php if (!function_exists('has_any_permission')): ?>
<?php
function has_any_permission(string $module): bool
{
    $perms = session('permissions') ?? [];
    if (!isset($perms[$module])) return false;
    foreach ($perms[$module] as $v) {
        if ($v == 1) return true;
    }
    return false;
}
?>
<?php endif; ?>
