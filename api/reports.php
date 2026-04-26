<?php
// ══════════════════════════════════════════
// reports.php  — GET only, staff-only
// ══════════════════════════════════════════
session_start();
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../auth/auth_guard.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'all';

// Readers can only access summary stats — all other report types are staff-only
if ($type === 'all') {
    requireRole(); // any authenticated user
} else {
    requireRole('staff');
}

// Re-read type after auth check (already set above)

$db   = getDB();
$type = $_GET['type'] ?? 'all';

switch ($type) {

    case 'issued':
        $rows = $db->query(
            "SELECT ir.book_no, ir.isbn, b.title, r.name AS reader_name,
                    ir.reader_reg_no, ir.issue_date, ir.due_date, ir.status
             FROM issue_return ir
             JOIN books b ON ir.isbn = b.isbn
             JOIN readers r ON ir.reader_reg_no = r.reg_no
             WHERE ir.status = 'issued'
             ORDER BY ir.issue_date DESC"
        )->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
        break;

    case 'overdue':
        // Auto-update first
        $today = date('Y-m-d');
        $db->query("UPDATE issue_return SET status='overdue' WHERE status='issued' AND due_date < '$today'");
        $rows = $db->query(
            "SELECT ir.*, b.title, r.name AS reader_name, r.phone_no
             FROM issue_return ir
             JOIN books b ON ir.isbn = b.isbn
             JOIN readers r ON ir.reader_reg_no = r.reg_no
             WHERE ir.status = 'overdue'
             ORDER BY ir.due_date ASC"
        )->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
        break;

    case 'inventory':
        $rows = $db->query(
            "SELECT b.isbn, b.title, b.category, b.total_copies,
                    b.available_copies,
                    (b.total_copies - b.available_copies) AS issued_count,
                    p.name AS publisher
             FROM books b
             LEFT JOIN publisher p ON b.publisher_id = p.publisher_id
             ORDER BY b.title"
        )->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
        break;

    case 'reader_activity':
        $rows = $db->query(
            "SELECT r.reg_no, r.name, COUNT(ir.book_no) AS total_issued,
                    SUM(ir.status = 'issued') AS currently_issued,
                    SUM(ir.status = 'overdue') AS overdue,
                    SUM(ir.status = 'returned') AS returned
             FROM readers r
             LEFT JOIN issue_return ir ON r.reg_no = ir.reader_reg_no
             GROUP BY r.reg_no ORDER BY total_issued DESC"
        )->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
        break;

    case 'monthly':
        $rows = $db->query(
            "SELECT DATE_FORMAT(issue_date,'%Y-%m') AS month,
                    COUNT(*) AS issued,
                    SUM(status='returned') AS returned
             FROM issue_return
             GROUP BY month ORDER BY month DESC LIMIT 12"
        )->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
        break;

    default:
        // Summary dashboard stats
        $stats = [];
        $stats['total_books']    = $db->query("SELECT COUNT(*) AS n FROM books")->fetch_assoc()['n'];
        $stats['total_readers']  = $db->query("SELECT COUNT(*) AS n FROM readers")->fetch_assoc()['n'];
        $stats['total_issued']   = $db->query("SELECT COUNT(*) AS n FROM issue_return WHERE status='issued'")->fetch_assoc()['n'];
        $stats['total_overdue']  = $db->query("SELECT COUNT(*) AS n FROM issue_return WHERE status='overdue'")->fetch_assoc()['n'];
        $stats['total_returned'] = $db->query("SELECT COUNT(*) AS n FROM issue_return WHERE status='returned'")->fetch_assoc()['n'];
        echo json_encode($stats);
}
