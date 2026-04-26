<?php
session_start();
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../auth/auth_guard.php';

header('Content-Type: application/json');
requireRole('staff');

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $rows = $db->query(
            "SELECT s.staff_id, s.name, s.login_id, a.role
             FROM staff s
             LEFT JOIN authentication_system a ON s.login_id = a.login_id
             ORDER BY s.name"
        )->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
        break;

    case 'POST':
        $body     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name     = trim($body['name'] ?? '');
        $login_id = trim($body['login_id'] ?? '');
        $password = $body['password'] ?? '';

        if (!$name || !$login_id || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'name, login_id, and password are required']);
            break;
        }

        $res      = $db->query("SELECT COUNT(*) AS cnt FROM staff");
        $staff_id = 'ST' . str_pad($res->fetch_assoc()['cnt'] + 1, 3, '0', STR_PAD_LEFT);
        $hashed   = password_hash($password, PASSWORD_BCRYPT);

        $db->begin_transaction();
        try {
            $a = $db->prepare("INSERT INTO authentication_system (login_id, password, role) VALUES (?, ?, 'staff')");
            $a->bind_param('ss', $login_id, $hashed);
            $a->execute();
            $a->close();

            $s = $db->prepare("INSERT INTO staff (staff_id, name, login_id) VALUES (?, ?, ?)");
            $s->bind_param('sss', $staff_id, $name, $login_id);
            $s->execute();
            $s->close();

            $db->commit();
            echo json_encode(['success' => true, 'staff_id' => $staff_id]);
        } catch (Exception $e) {
            $db->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $staff_id = $_GET['staff_id'] ?? '';
        if (!$staff_id) { http_response_code(400); echo json_encode(['error' => 'staff_id required']); break; }

        // Get login_id first to delete auth record
        $chk = $db->prepare("SELECT login_id FROM staff WHERE staff_id = ?");
        $chk->bind_param('s', $staff_id);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$row) { http_response_code(404); echo json_encode(['error' => 'Staff not found']); break; }

        $db->begin_transaction();
        try {
            $d1 = $db->prepare("DELETE FROM staff WHERE staff_id = ?");
            $d1->bind_param('s', $staff_id);
            $d1->execute();
            $d1->close();

            $d2 = $db->prepare("DELETE FROM authentication_system WHERE login_id = ?");
            $d2->bind_param('s', $row['login_id']);
            $d2->execute();
            $d2->close();

            $db->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $db->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
