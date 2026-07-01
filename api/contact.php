<?php
/**
 * Contact Form Handler
 * Receives POST from contact.html
 * → Saves to DB
 * → Emails admin
 * → Sends confirmation to visitor
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$first_name = clean($_POST['first_name'] ?? '');
$last_name  = clean($_POST['last_name']  ?? '');
$email      = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone      = clean($_POST['phone']         ?? '');
$enquiry    = clean($_POST['enquiry_type']  ?? '');
$subject    = clean($_POST['subject']       ?? '');
$message    = clean($_POST['message']       ?? '');

if (!$first_name || !$last_name || !$email || !$subject || !$message) {
    json_response(false, 'Please fill in all required fields with a valid email.');
}

try {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("INSERT INTO contacts (first_name, last_name, email, phone, enquiry_type, subject, message)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $email, $phone, $enquiry, $subject, $message]);
    $id = $pdo->lastInsertId();
} catch (Exception $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}

// ── EMAIL ADMIN ────────────────────────────
$admin_html = tpl_admin_notification(
    'New Contact Message Received',
    [
        'Name'     => $first_name . ' ' . $last_name,
        'Email'    => $email,
        'Phone'    => $phone,
        'Enquiry'  => $enquiry,
        'Subject'  => $subject,
        'Date'     => date('Y-m-d H:i'),
    ],
    $message
);
//send_email(ADMIN_EMAIL, 'Admin', 'New Contact: ' . $subject, $admin_html, $email, $first_name);

$result1 = send_email(ADMIN_EMAIL, 'Admin', 'New Contact: ' . $subject, $admin_html, $email, $first_name);
if (!$result1['ok']) {
    file_put_contents(__DIR__ . '/../mail-error.log', "[" . date('Y-m-d H:i:s') . "] ADMIN EMAIL FAILED: " . $result1['error'] . "\n", FILE_APPEND);
}

// ── EMAIL VISITOR ──────────────────────────
$user_html = tpl_user_confirmation(
    $first_name,
    'We received your message',
    '<p>Thank you for contacting Releev Recruitment. We have received your message and a member of our team will respond within <strong>one business day</strong>.</p>
     <p><strong>Your subject:</strong> ' . htmlspecialchars($subject) . '</p>
     <p>If your matter is urgent, please call us on 0000000.</p>'
);
//send_email($email, $first_name . ' ' . $last_name, 'Thank you for contacting Releev Recruitment', $user_html);

$result2 = send_email($email, $first_name . ' ' . $last_name, 'Thank you for contacting Releev Recruitment', $user_html);
if (!$result2['ok']) {
    file_put_contents(__DIR__ . '/../mail-error.log', "[" . date('Y-m-d H:i:s') . "] USER EMAIL FAILED: " . $result2['error'] . "\n", FILE_APPEND);
}
json_response(true, 'Message received successfully.', ['id' => $id]);

