<?php
/**
 * Job Application Handler
 * Receives POST + CV from the Apply Now modal on jobs.html
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$job_id        = (int)($_POST['job_id'] ?? 0);
$full_name     = clean($_POST['full_name'] ?? '');
$email         = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone         = clean($_POST['phone'] ?? '');
$linkedin      = filter_var($_POST['linkedin'] ?? '', FILTER_VALIDATE_URL) ?: '';
$cover_message = clean($_POST['cover_message'] ?? '');

if (!$job_id || !$full_name || !$email) {
    json_response(false, 'Missing required fields.');
}

// Verify job exists and is approved
try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT id, title FROM jobs WHERE id = ? AND status = 'approved'");
    $stmt->execute([$job_id]);
    $job  = $stmt->fetch();
    if (!$job) json_response(false, 'Job not found or no longer available.');
} catch (Exception $e) {
    json_response(false, 'Database error.');
}

// CV upload
$upload = handle_cv_upload('cv', UPLOAD_DIR_APP);
if (!$upload['ok']) json_response(false, $upload['error']);

try {
    $stmt = $pdo->prepare("INSERT INTO applications (job_id, full_name, email, phone, linkedin, cover_message, cv_filename, cv_original_name)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$job_id, $full_name, $email, $phone, $linkedin, $cover_message,
                    $upload['filename'], $upload['original']]);
    $id = $pdo->lastInsertId();
} catch (Exception $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}

// ── EMAIL ADMIN ────────────────────────────
$admin_html = tpl_admin_notification(
    'New Job Application Received',
    [
        'Job'      => $job['title'],
        'Name'     => $full_name,
        'Email'    => $email,
        'Phone'    => $phone,
        'LinkedIn' => $linkedin,
        'CV File'  => $upload['original'] ?? 'No CV uploaded',
        'Date'     => date('Y-m-d H:i'),
    ],
    $cover_message
);
send_email(ADMIN_EMAIL, 'Admin', 'New Application: ' . $job['title'], $admin_html, $email, $full_name);

// ── EMAIL APPLICANT ────────────────────────
$user_html = tpl_user_confirmation(
    $full_name,
    'Application received',
    '<p>Thank you for applying for the <strong>' . htmlspecialchars($job['title']) . '</strong> role at Releev Recruitment.</p>
     <p>Our recruitment team will review your application carefully. If your profile matches the requirements, we will reach out within <strong>2 business days</strong> to schedule an introductory call.</p>
     <p>If you don\'t hear from us within 5 business days, the role has likely been filled with another candidate — but we will keep your details on file for future matches.</p>'
);
send_email($email, $full_name, 'Application Received – ' . $job['title'], $user_html);

json_response(true, 'Application submitted successfully.', ['id' => $id]);

