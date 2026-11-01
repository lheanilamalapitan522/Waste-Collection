<?php
require_once 'session.php';

function current_role(): ?string
{
    return isset($_SESSION['role']) && is_string($_SESSION['role'])
        ? $_SESSION['role']
        : null;
}

function is_admin(): bool
{
    return in_array(current_role(), ['admin', 'developer'], true);
}

function is_collector(): bool
{
    return current_role() === 'collector';
}

function is_developer(): bool
{
    return current_role() === 'developer';
}

function is_resident(): bool
{
    return current_role() === 'resident';
}

function require_role(string $role): void
{
    if (current_role() !== $role && !is_admin()) {
        header('Location: login.php');
        exit;
    }
}
