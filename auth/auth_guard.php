<?php
// ═══════════════════════════════════════════════════
// auth_guard.php  — include at the top of every
//                   protected API endpoint
// ═══════════════════════════════════════════════════
// Usage:
//   require_once __DIR__ . '/../auth/auth_guard.php';
//   requireRole('staff');        // staff only
//   requireRole('reader');       // reader only
//   requireRole();               // any authenticated user
// ═══════════════════════════════════════════════════

function requireRole(string $role = '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['login_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthenticated']);
        exit;
    }

    if ($role && $_SESSION['role'] !== $role) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden — insufficient permissions']);
        exit;
    }
}
