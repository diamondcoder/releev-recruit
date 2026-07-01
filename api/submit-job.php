<?php
/**
 * Submit Job (by logged-in employer)
 * Saves job with status=pending until admin approves
 */

require_once __DIR__ . '/../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

if (!isset($_SESSION['employer_id'])) {
    json_response(false, 'You must be logged in as an employer to post jobs.');
}

$employer_id    = (int)$_SESSION['employer_id'];
$title          = clean($_POST['title']         ?? '');
$sector         = clean($_POST['sector']        ?? '');
$location       = clean($_POST['location']      ?? '');
$contract_type  = clean($_POST['contract_type'] ?? 'Permanent');
$hours          = clean($_POST['hours']         ?? 'Full-time');
$salary         = clean($_POST['salary']        ?? '');
$description    = clean($_POST['description']   ?? '');
$requirements   = clean($_POST['requirements']  ?? '');

if (!$title || !$sector || !$location || !$description) {
    json_response(false, 'Please fill in title, sector, location and description.');
}
if (!in_array($sector, ['Life Sciences','Engineering','Management'])) {
    json_response(false, 'Invalid sector.');
}

try {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO jobs (employer_id, posted_by, title, sector, location, contract_type, hours, salary, description, requirements, status)
                           VALUES (?, 'employer', ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$employer_id, $title, $sector, $location, $contract_type, $hours, $salary, $description, $requirements]);
    json_response(true, 'Job submitted for admin review. You will be notified once it is approved.', ['id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
