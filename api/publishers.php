<?php
session_start();
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../auth/auth_guard.php';

header('Content-Type: application/json');
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        requireRole();
        $id = $_GET['publisher_id'] ?? null;
        if ($id) {
            $stmt = $db->prepare(
                "SELECT p.*, COUNT(b.isbn) AS book_count
                 FROM publisher p LEFT JOIN books b ON p.publisher_id = b.publisher_id
                 WHERE p.publisher_id = ? GROUP BY p.publisher_id"
            );
            $stmt->bind_param('s', $id);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_assoc());
            $stmt->close();
        } else {
            $rows = $db->query(
                "SELECT p.*, COUNT(b.isbn) AS book_count
                 FROM publisher p LEFT JOIN books b ON p.publisher_id = b.publisher_id
                 GROUP BY p.publisher_id ORDER BY p.name"
            )->fetch_all(MYSQLI_ASSOC);
            echo json_encode($rows);
        }
        break;

    case 'POST':
        requireRole('staff');
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name = trim($body['name'] ?? '');
        $year = intval($body['year_of_publication'] ?? 0);

        if (!$name) { http_response_code(400); echo json_encode(['error' => 'Publisher name required']); break; }

        $res   = $db->query("SELECT COUNT(*) AS cnt FROM publisher");
        $id    = 'PUB' . str_pad($res->fetch_assoc()['cnt'] + 1, 4, '0', STR_PAD_LEFT);
        $stmt  = $db->prepare("INSERT INTO publisher (publisher_id, name, year_of_publication) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $id, $name, $year);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'publisher_id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        requireRole('staff');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = trim($body['publisher_id'] ?? '');
        $name = trim($body['name'] ?? '');
        $year = intval($body['year_of_publication'] ?? 0);

        if (!$id) { http_response_code(400); echo json_encode(['error' => 'publisher_id required']); break; }
        $stmt = $db->prepare("UPDATE publisher SET name=?, year_of_publication=? WHERE publisher_id=?");
        $stmt->bind_param('sis', $name, $year, $id);
        echo json_encode($stmt->execute() ? ['success' => true] : ['error' => $stmt->error]);
        $stmt->close();
        break;

    case 'DELETE':
        requireRole('staff');
        $id = $_GET['publisher_id'] ?? '';
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'publisher_id required']); break; }
        $stmt = $db->prepare("DELETE FROM publisher WHERE publisher_id = ?");
        $stmt->bind_param('s', $id);
        echo json_encode($stmt->execute() ? ['success' => true] : ['error' => $stmt->error]);
        $stmt->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
