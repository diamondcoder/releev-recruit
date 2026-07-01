<?php
/**
 * Public Jobs API
 * Returns approved jobs as JSON for the jobs.html page
 * Optional ?sector= filter
 */

require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$sector = $_GET['sector'] ?? '';

try {
    $pdo = db_connect();
    if ($sector && in_array($sector, ['Life Sciences','Engineering','Management'])) {
        $stmt = $pdo->prepare("SELECT id, title, sector, location, contract_type, hours, salary, description, posted_at
                               FROM jobs WHERE status='approved' AND sector=?
                               ORDER BY posted_at DESC");
        $stmt->execute([$sector]);
    } else {
        $stmt = $pdo->query("SELECT id, title, sector, location, contract_type, hours, salary, description, posted_at
                             FROM jobs WHERE status='approved'
                             ORDER BY posted_at DESC");
    }
    echo json_encode(['success' => true, 'jobs' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading jobs.']);
}
