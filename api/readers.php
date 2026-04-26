<?php
session_start();
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../auth/auth_guard.php';

header('Content-Type: application/json');
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: all readers or single ───────────────────
    case 'GET':
        requireRole('staff');
        $reg_no = $_GET['reg_no'] ?? null;

        if ($reg_no) {
            $stmt = $db->prepare(
                "SELECT r.user_id, r.reg_no, r.firstname, r.lastname,
                        CONCAT(r.firstname, ' ', r.lastname) AS name,
                        r.email, r.phone_no, r.address, r.login_id
                 FROM readers r
                 WHERE r.reg_no = ?"
            );
            $stmt->bind_param('s', $reg_no);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            echo json_encode($row ?: ['error' => 'Reader not found']);
        } else {
            $search = '%' . ($db->real_escape_string($_GET['q'] ?? '')) . '%';
            $stmt   = $db->prepare(
                "SELECT user_id, reg_no, firstname, lastname,
                        CONCAT(firstname, ' ', lastname) AS name,
                        email, phone_no, address
                 FROM readers
                 WHERE CONCAT(firstname, ' ', lastname) LIKE ?
                    OR reg_no LIKE ?
                    OR email LIKE ?
                 ORDER BY firstname"
            );
            $stmt->bind_param('sss', $search, $search, $search);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode($rows);
        }
        break;

    // ── POST: register new reader ────────────────────
    case 'POST':
        requireRole('staff');
        $body      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $firstname = trim($body['firstname'] ?? '');
        $lastname  = trim($body['lastname'] ?? '');
        $email     = trim($body['email'] ?? '');
        $phone     = trim($body['phone_no'] ?? '');
        $address   = trim($body['address'] ?? '');
        $password  = $body['password'] ?? '';

        if (!$firstname || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'First name, email, and password are required']);
            break;
        }

        // Generate unique reg_no and user_id that don't clash with existing records
        $res     = $db->query("SELECT MAX(CAST(SUBSTRING(reg_no, 2) AS UNSIGNED)) AS maxn FROM readers WHERE reg_no REGEXP '^R[0-9]+$'");
        $maxn    = $res->fetch_assoc()['maxn'] ?? 0;
        $next    = $maxn + 1;
        $reg_no  = 'R' . str_pad($next, 4, '0', STR_PAD_LEFT);
        $user_id = 'RD' . str_pad($next, 3, '0', STR_PAD_LEFT);

        // Make sure reg_no and user_id are not already taken
        while ($db->query("SELECT reg_no FROM readers WHERE reg_no='$reg_no'")->num_rows > 0) {
            $next++;
            $reg_no  = 'R' . str_pad($next, 4, '0', STR_PAD_LEFT);
            $user_id = 'RD' . str_pad($next, 3, '0', STR_PAD_LEFT);
        }

        // Generate unique login_id from email prefix
        $base     = strtolower(preg_replace('/[^a-z0-9]/i', '', explode('@', $email)[0])) . '_r';
        $login_id = $base;
        $i        = 2;
        while ($db->query("SELECT login_id FROM authentication_system WHERE login_id='$login_id'")->num_rows > 0) {
            $login_id = $base . $i;
            $i++;
        }

        // Create auth record
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt   = $db->prepare(
            "INSERT INTO authentication_system (login_id, password, role) VALUES (?, ?, 'reader')"
        );
        $stmt->bind_param('ss', $login_id, $hashed);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create login: ' . $stmt->error]);
            $stmt->close();
            break;
        }
        $stmt->close();

        // Create reader record
        $stmt = $db->prepare(
            "INSERT INTO readers (user_id, reg_no, firstname, lastname, email, phone_no, address, login_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssssss', $user_id, $reg_no, $firstname, $lastname,
                          $email, $phone, $address, $login_id);
        if ($stmt->execute()) {
            echo json_encode([
                'success'  => true,
                'reg_no'   => $reg_no,
                'user_id'  => $user_id,
                'login_id' => $login_id,
            ]);
        } else {
            // Rollback auth record if reader insert fails
            $db->query("DELETE FROM authentication_system WHERE login_id='$login_id'");
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        break;

    // ── PUT: update reader ───────────────────────────
    case 'PUT':
        requireRole('staff');
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $reg_no    = trim($body['reg_no'] ?? '');
        $firstname = trim($body['firstname'] ?? '');
        $lastname  = trim($body['lastname'] ?? '');
        $phone     = trim($body['phone_no'] ?? '');
        $address   = trim($body['address'] ?? '');
        $email     = trim($body['email'] ?? '');

        if (!$reg_no) {
            http_response_code(400);
            echo json_encode(['error' => 'reg_no required']);
            break;
        }

        $stmt = $db->prepare(
            "UPDATE readers SET firstname=?, lastname=?, phone_no=?, address=?, email=? WHERE reg_no=?"
        );
        $stmt->bind_param('ssssss', $firstname, $lastname, $phone, $address, $email, $reg_no);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        break;

    // ── DELETE ───────────────────────────────────────
    case 'DELETE':
        requireRole('staff');
        $reg_no = $_GET['reg_no'] ?? '';
        if (!$reg_no) {
            http_response_code(400);
            echo json_encode(['error' => 'reg_no required']);
            break;
        }

        // Get login_id so we can delete the auth record too
        $chk = $db->prepare("SELECT login_id FROM readers WHERE reg_no = ?");
        $chk->bind_param('s', $reg_no);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Reader not found']);
            break;
        }

        $db->begin_transaction();
        try {
            $d1 = $db->prepare("DELETE FROM readers WHERE reg_no = ?");
            $d1->bind_param('s', $reg_no);
            $d1->execute();
            $d1->close();

            if ($row['login_id']) {
                $d2 = $db->prepare("DELETE FROM authentication_system WHERE login_id = ?");
                $d2->bind_param('s', $row['login_id']);
                $d2->execute();
                $d2->close();
            }

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
