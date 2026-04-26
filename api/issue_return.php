<?php
session_start();
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../auth/auth_guard.php';

header('Content-Type: application/json');
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── GET: all issue records or filter ─────────────
    case 'GET':
        requireRole(); // any authenticated user
        $status  = $_GET['status'] ?? null;
        $reg_no  = $_GET['reg_no'] ?? null;

        // Readers can only see their own records — look up reg_no fresh from DB
        if ($_SESSION['role'] === 'reader') {
            $lid = $_SESSION['login_id'];
            $rs  = $db->prepare("SELECT reg_no FROM readers WHERE login_id = ?");
            $rs->bind_param('s', $lid);
            $rs->execute();
            $row = $rs->get_result()->fetch_assoc();
            $rs->close();
            $reg_no = $row['reg_no'] ?? null;
        }


        $where = [];
        $params = [];
        $types  = '';

        if ($status) { $where[] = 'ir.status = ?'; $params[] = $status; $types .= 's'; }
        if ($reg_no) { $where[] = 'ir.reader_reg_no = ?'; $params[] = $reg_no; $types .= 's'; }

        $sql = "SELECT ir.*, b.title, r.firstname, r.lastname
                FROM issue_return ir
                JOIN books b ON ir.isbn = b.isbn
                JOIN readers r ON ir.reader_reg_no = r.reg_no";
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY ir.issue_date DESC';

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Auto-flag overdue records
        $today = date('Y-m-d');
        $db->query(
            "UPDATE issue_return SET status='overdue'
             WHERE status='issued' AND due_date < '$today'"
        );

        echo json_encode($rows);
        break;

    // ── POST: issue a book ───────────────────────────
    case 'POST':
        requireRole('staff');
        $body       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $isbn       = trim($body['isbn'] ?? '');
        $reg_no     = trim($body['reader_reg_no'] ?? '');
        $issue_date = $body['issue_date'] ?? date('Y-m-d');
        $due_date   = $body['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $reserve_dt = $body['reserve_date'] ?? null;

        if (!$isbn || !$reg_no) {
            http_response_code(400);
            echo json_encode(['error' => 'isbn and reader_reg_no are required']);
            break;
        }

        // Check availability
        $chk = $db->prepare("SELECT available_copies FROM books WHERE isbn = ?");
        $chk->bind_param('s', $isbn);
        $chk->execute();
        $book = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$book) { http_response_code(404); echo json_encode(['error' => 'Book not found']); break; }
        if ($book['available_copies'] < 1) {
            http_response_code(409);
            echo json_encode(['error' => 'No available copies']);
            break;
        }

        // Generate book_no
        $res    = $db->query("SELECT COUNT(*) AS cnt FROM issue_return");
        $count  = $res->fetch_assoc()['cnt'] + 1;
        $book_no = 'BK' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $db->begin_transaction();
        try {
            $stmt = $db->prepare(
                "INSERT INTO issue_return (book_no, isbn, reader_reg_no, reserve_date, issue_date, due_date, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'issued')"
            );
            $stmt->bind_param('ssssss', $book_no, $isbn, $reg_no, $reserve_dt, $issue_date, $due_date);
            $stmt->execute();
            $stmt->close();

            // Decrement available copies
            $db->query("UPDATE books SET available_copies = available_copies - 1 WHERE isbn = '$isbn'");

            // Log to reports
            $staff_id = $_SESSION['profile']['staff_id'] ?? 'UNKNOWN';
            $upd = $db->prepare(
                "INSERT INTO reports (user_id, reg_no, book_no, issue_return) VALUES (?, ?, ?, 'issue')"
            );
            $upd->bind_param('sss', $staff_id, $reg_no, $book_no);
            $upd->execute();
            $upd->close();

            $db->commit();
            echo json_encode(['success' => true, 'book_no' => $book_no]);
        } catch (Exception $e) {
            $db->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    // ── PUT: return a book ───────────────────────────
    case 'PUT':
        requireRole('staff');
        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $book_no    = trim($body['book_no'] ?? '');
        $return_date = $body['return_date'] ?? date('Y-m-d');

        if (!$book_no) { http_response_code(400); echo json_encode(['error' => 'book_no required']); break; }

        // Get ISBN from issue record
        $chk = $db->prepare("SELECT isbn, reader_reg_no, status FROM issue_return WHERE book_no = ?");
        $chk->bind_param('s', $book_no);
        $chk->execute();
        $record = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$record) { http_response_code(404); echo json_encode(['error' => 'Issue record not found']); break; }
        if ($record['status'] === 'returned') {
            http_response_code(409);
            echo json_encode(['error' => 'Book already returned']);
            break;
        }

        $db->begin_transaction();
        try {
            $stmt = $db->prepare(
                "UPDATE issue_return SET status='returned', return_date=? WHERE book_no=?"
            );
            $stmt->bind_param('ss', $return_date, $book_no);
            $stmt->execute();
            $stmt->close();

            $isbn   = $db->real_escape_string($record['isbn']);
            $reg_no = $record['reader_reg_no'];
            $db->query("UPDATE books SET available_copies = available_copies + 1 WHERE isbn = '$isbn'");

            $staff_id = $_SESSION['profile']['staff_id'] ?? 'UNKNOWN';
            $rep = $db->prepare(
                "INSERT INTO reports (user_id, reg_no, book_no, issue_return) VALUES (?, ?, ?, 'return')"
            );
            $rep->bind_param('sss', $staff_id, $reg_no, $book_no);
            $rep->execute();
            $rep->close();

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
