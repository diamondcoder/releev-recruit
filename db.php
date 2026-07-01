<?php
/**
 * Releev Recruitment — Database & Site Config
 * Edit the values below to match your XAMPP and Gmail setup
 */

// ── DATABASE (XAMPP DEFAULTS) ─────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'releev_recruit');
define('DB_USER', 'root');
define('DB_PASS', '');   // XAMPP default is empty password
define('DB_CHARSET', 'utf8mb4');

// ── SITE INFO ─────────────────────────────────────
define('SITE_NAME', 'Releev Recruitment');
define('SITE_URL', 'http://localhost/releev-recruit');
define('ADMIN_EMAIL', 'admin@company.se');   // ← CHANGE: where contact/cv/application notifications go

// ── GMAIL SMTP (FOR SENDING EMAILS) ───────────────
// Get an App Password from: https://myaccount.google.com/apppasswords
// (Must have 2-Step Verification enabled on Gmail first)
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     'email@gmail.com');         // ← CHANGE: your Gmail address
define('SMTP_PASS',     'dbzt tsii dylv fcxw');    // ← CHANGE: 16-char app password
define('SMTP_FROM',     'email@gmail.com');         // ← CHANGE: same as SMTP_USER usually
define('SMTP_FROM_NAME','releev-recruit');

// ── FILE UPLOAD SETTINGS ──────────────────────────
define('UPLOAD_DIR_CV',    __DIR__ . '/uploads/cvs/');
define('UPLOAD_DIR_APP',   __DIR__ . '/uploads/applications/');
define('MAX_UPLOAD_SIZE',  5 * 1024 * 1024);  // 5 MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);

// ── PDO CONNECTION ────────────────────────────────
function db_connect() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// ── HELPER: JSON response ─────────────────────────
function json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// ── HELPER: clean input ───────────────────────────
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// ── HELPER: file upload ───────────────────────────
function handle_cv_upload($file_field, $upload_dir) {
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => null, 'original' => null];
    }

    $f = $_FILES[$file_field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'File upload error.'];
    }
    if ($f['size'] > MAX_UPLOAD_SIZE) {
        return ['ok' => false, 'error' => 'File too large. Maximum 5MB.'];
    }

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['ok' => false, 'error' => 'Only PDF, DOC, DOCX files allowed.'];
    }

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $unique  = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $target  = $upload_dir . $unique;

    if (!move_uploaded_file($f['tmp_name'], $target)) {
        return ['ok' => false, 'error' => 'Failed to save uploaded file.'];
    }

    return ['ok' => true, 'filename' => $unique, 'original' => $f['name']];
}

