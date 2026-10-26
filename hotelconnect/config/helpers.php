<?php
/**
 * Helpers globaux — HotelConnect
 * Toutes les fonctions utilitaires du projet.
 */

require_once __DIR__ . '/config.php';

/* ===================== ÉCHAPPEMENT ===================== */

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ===================== MONNAIE ===================== */

function money($n): string {
    return number_format((float)$n, 0, ',', ' ') . ' FCFA';
}

/* ===================== UTILISATEUR CONNECTÉ ===================== */

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_role(string $role): void {
    require_login();
    if (current_user()['role'] !== $role) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function dashboard_url(): string {
    $u = current_user();
    if (!$u) return BASE_URL . '/login.php';
    return match($u['role']) {
        'admin'    => BASE_URL . '/admin/index.php',
        'hotelier' => BASE_URL . '/hotelier/index.php',
        default    => BASE_URL . '/client/index.php',
    };
}

/* ===================== MESSAGES FLASH ===================== */

function flash(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function get_flashes(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ===================== CSRF ===================== */

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    // Ne vérifie QUE sur POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || $t === '' || !hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        exit('Formulaire expiré. Recharge la page.');
    }
}

/* ===================== REDIRECTION ===================== */

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}