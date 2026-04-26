<?php
session_start();
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$login_id = trim($_POST['login_id'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? ''; // 'staff' or 'reader'

if (!$login_id || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Login ID and password are required']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare(
    "SELECT a.login_id, a.password, a.role
     FROM authentication_system a
     WHERE a.login_id = ? AND a.role = ?"
);
$stmt->bind_param('ss', $login_id, $role);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- MODIFIED HERE: Plain text comparison instead of password_verify ---
if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

// Fetch extra profile based on role
$profile = [];
if ($user['role'] === 'staff' || $user['role'] === 'admin') {
    // Note: Ensuring the query works for both 'staff' and 'admin' roles
    $ps = $db->prepare("SELECT staff_id, name FROM staff WHERE login_id = ?");
    $ps->bind_param('s', $login_id);
    $ps->execute();
    $profile = $ps->get_result()->fetch_assoc() ?? [];
    $ps->close();
} else {
    $ps = $db->prepare("SELECT user_id, reg_no, firstname, lastname, email FROM readers WHERE login_id = ?");
    $ps->bind_param('s', $login_id);
    $ps->execute();
    $profile = $ps->get_result()->fetch_assoc() ?? [];
    $ps->close();
}

// Regenerate session to prevent fixation
session_regenerate_id(true);
$_SESSION['login_id'] = $user['login_id'];
$_SESSION['role']     = $user['role'];
$_SESSION['profile']  = $profile;

echo json_encode([
    'success' => true,
    'role'    => $user['role'],
    'profile' => $profile,
]);