<?php
session_start();
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../auth/auth_guard.php';

header('Content-Type: application/json');
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: list all books or single by ISBN ────────
    case 'GET':
        requireRole(); // any authenticated user
        $isbn = $_GET['isbn'] ?? null;

        if ($isbn) {
            $stmt = $db->prepare(
                "SELECT b.*, p.name AS publisher_name
                 FROM books b
                 LEFT JOIN publisher p ON b.publisher_id = p.publisher_id
                 WHERE b.isbn = ?"
            );
            $stmt->bind_param('s', $isbn);
            $stmt->execute();
            $book = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            echo json_encode($book ?: ['error' => 'Book not found']);
        } else {
            $search = '%' . ($db->real_escape_string($_GET['q'] ?? '')) . '%';
            $stmt   = $db->prepare(
                "SELECT b.isbn, b.title, b.auth_no, b.category, b.edition,
                        b.price, b.total_copies, b.available_copies,
                        p.name AS publisher_name
                 FROM books b
                 LEFT JOIN publisher p ON b.publisher_id = p.publisher_id
                 WHERE b.title LIKE ? OR b.isbn LIKE ? OR b.category LIKE ?
                 ORDER BY b.title"
            );
            $stmt->bind_param('sss', $search, $search, $search);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode($rows);
        }
        break;

    // ── POST: add new book ───────────────────────────
    case 'POST':
        requireRole('staff');
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $isbn     = trim($body['isbn'] ?? '');
        $title    = trim($body['title'] ?? '');
        $auth_no  = trim($body['auth_no'] ?? '');
        $category = trim($body['category'] ?? '');
        $edition  = trim($body['edition'] ?? '');
        $price    = floatval($body['price'] ?? 0);
        $copies   = intval($body['total_copies'] ?? 1);
        $pub_id   = trim($body['publisher_id'] ?? '');

        if (!$isbn || !$title) {
            http_response_code(400);
            echo json_encode(['error' => 'ISBN and title are required']);
            break;
        }

        $stmt = $db->prepare(
            "INSERT INTO books (isbn, title, auth_no, category, edition, price,
                                total_copies, available_copies, publisher_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssssdis', $isbn, $title, $auth_no, $category,
                          $edition, $price, $copies, $copies, $pub_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'isbn' => $isbn]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        break;

    // ── PUT: update book ─────────────────────────────
    case 'PUT':
        requireRole('staff');
        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $isbn    = trim($body['isbn'] ?? '');
        $title   = trim($body['title'] ?? '');
        $price   = floatval($body['price'] ?? 0);
        $copies  = intval($body['total_copies'] ?? 1);
        $avail   = intval($body['available_copies'] ?? 0);
        $pub_id  = trim($body['publisher_id'] ?? '');

        if (!$isbn) { http_response_code(400); echo json_encode(['error' => 'ISBN required']); break; }

        $stmt = $db->prepare(
            "UPDATE books SET title=?, price=?, total_copies=?, available_copies=?, publisher_id=?
             WHERE isbn=?"
        );
        $stmt->bind_param('sdiiss', $title, $price, $copies, $avail, $pub_id, $isbn);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        break;

    // ── DELETE: remove book ──────────────────────────
    case 'DELETE':
        requireRole('staff');
        $isbn = $_GET['isbn'] ?? '';
        if (!$isbn) { http_response_code(400); echo json_encode(['error' => 'ISBN required']); break; }

        $stmt = $db->prepare("DELETE FROM books WHERE isbn = ?");
        $stmt->bind_param('s', $isbn);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
