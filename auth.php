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
    return current_role() === 'admin';
}

function is_collector(): bool
{
    return current_role() === 'collector';
}

function require_role(string $role): void
{
    if (current_role() !== $role) {
        header('Location: login.php');
        exit;
    }
}
