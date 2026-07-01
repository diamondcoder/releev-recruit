<?php
/**
 * Candidate CV Submission Handler
 * Receives POST + file from candidates.html
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$full_name    = clean($_POST['full_name'] ?? '');
$email        = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone        = clean($_POST['phone'] ?? '');
$sector       = clean($_POST['sector'] ?? '');
$role_now = clean($_POST['role_now'] ?? '');
$message      = clean($_POST['message'] ?? '');
$linkedin     = filter_var($_POST['linkedin'] ?? '', FILTER_VALIDATE_URL) ?: '';

if (!$full_name || !$email || !$sector) {
    json_response(false, 'Please fill in all required fields.');
}

// CV upload
$upload = handle_cv_upload('cv', UPLOAD_DIR_CV);
if (!$upload['ok']) {
    json_response(false, $upload['error']);
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("INSERT INTO candidates (full_name, email, phone, sector, role_now, message, linkedin, cv_filename, cv_original_name)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $phone, $sector, $role_now, $message, $linkedin,
                    $upload['filename'], $upload['original']]);
    $id = $pdo->lastInsertId();
} catch (Exception $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}

// ── EMAIL ADMIN ────────────────────────────
$admin_html = tpl_admin_notification(
    'New Candidate CV Submission',
    [
        'Name'         => $full_name,
        'Email'        => $email,
        'Phone'        => $phone,
        'Sector'       => $sector,
        'Current Role' => $role_now ?: 'N/A',
        'LinkedIn'     => $linkedin,
        'CV File'      => $upload['original'] ?? 'No CV uploaded',
        'Date'         => date('Y-m-d H:i'),
    ],
    $message
);
send_email(ADMIN_EMAIL, 'Admin', 'New Candidate: ' . $full_name, $admin_html, $email, $full_name);

// ── EMAIL CANDIDATE ────────────────────────
$user_html = tpl_user_confirmation(
    $full_name,
    'Your profile has been received',
    '<p>Thank you for submitting your details to Releev Recruitment. Our specialist team will review your profile and reach out within <strong>2 business days</strong> if there\'s a match with our current opportunities.</p>
     <p><strong>What happens next:</strong></p>
     <ul style="line-height:1.9;">
       <li>We review your profile against our active roles</li>
       <li>An introductory call to understand your goals</li>
       <li>Matching opportunities presented to you</li>
       <li>Full support through interview and offer</li>
     </ul>
     <p>In the meantime, feel free to <a href="' . SITE_URL . '/jobs.html" style="color:#0177E3;font-weight:600;">browse our current openings</a>.</p>'
);
send_email($email, $full_name, 'CV Received – Releev Recruitment', $user_html);

json_response(true, 'CV received successfully.', ['id' => $id]);

