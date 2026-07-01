<?php
/**
 * SETUP / FIX ADMIN PASSWORD
 *
 * Run this ONCE after importing database.sql:
 *   http://localhost/surely-sci-tech/setup.php
 *
 * It will:
 *   - Verify DB connection
 *   - Set admin password to "admin123" (bcrypt hash)
 *   - Show you what's working
 *
 * DELETE THIS FILE after use, for security.
 */

require_once __DIR__ . '/db.php';

$results = [];

// Test DB connection
try {
    $pdo = db_connect();
    $results[] = ['ok' => true, 'msg' => '✅ Database connection: OK'];
} catch (Exception $e) {
    die('❌ Database connection FAILED: ' . $e->getMessage());
}

// Check tables
$tables = ['admins','employers','jobs','contacts','candidates','applications'];
foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $results[] = ['ok' => true, 'msg' => "✅ Table '$t' exists ($count rows)"];
    } catch (Exception $e) {
        $results[] = ['ok' => false, 'msg' => "❌ Table '$t' NOT FOUND — re-import database.sql"];
    }
}

// Reset admin password to "admin123"
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = 'admin'");
$stmt->execute([$hash]);

if ($stmt->rowCount() > 0) {
    $results[] = ['ok' => true, 'msg' => '✅ Admin password reset to "admin123"'];
} else {
    // Maybe admin doesn't exist — create
    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@company.se', $hash, 'Site Administrator']);
    $results[] = ['ok' => true, 'msg' => '✅ Admin user created (admin / admin123)'];
}

// Check folders
foreach (['uploads/cvs', 'uploads/applications'] as $f) {
    $path = __DIR__ . '/' . $f;
    if (!is_dir($path)) mkdir($path, 0755, true);
    $results[] = ['ok' => is_writable($path), 'msg' => (is_writable($path) ? '✅' : '⚠️ ') . " Folder '$f' " . (is_writable($path) ? 'is writable' : 'is NOT writable')];
}

// PHPMailer check
$phpmailer_ok = file_exists(__DIR__ . '/includes/PHPMailer/PHPMailer.php');
$results[] = ['ok' => $phpmailer_ok, 'msg' => ($phpmailer_ok ? '✅' : '⚠️ ') . ' PHPMailer ' . ($phpmailer_ok ? 'installed' : 'NOT installed (emails will fail) — see README step 4')];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Setup · Releev Recruitment</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f8ff;padding:40px;line-height:1.7}
.box{max-width:680px;margin:0 auto;background:#fff;border-radius:12px;padding:36px;box-shadow:0 4px 20px rgba(0,0,0,0.08)}
h1{color:#0177E3;font-size:26px;margin-bottom:8px}
.checks{margin:24px 0;padding:20px;background:#f4f8ff;border-radius:8px;font-family:monospace;font-size:14px}
.checks div{padding:6px 0}
.next{background:#d1fae5;padding:20px;border-radius:8px;margin-top:24px}
.warn{background:#fef3c7;padding:20px;border-radius:8px;margin-top:24px;color:#92400e}
a{color:#0177E3;font-weight:600}
code{background:#fff;padding:3px 8px;border-radius:4px}
</style>
</head>
<body>
<div class="box">
  <h1>🔧 Releev Recruitment — Setup Check</h1>
  <p>Running setup verifications...</p>

  <div class="checks">
    <?php foreach ($results as $r): ?>
      <div><?= $r['msg'] ?></div>
    <?php endforeach; ?>
  </div>

  <div class="next">
    <strong>✅ All set! Next steps:</strong>
    <ol>
      <li>Visit the <a href="index.html">public site</a></li>
      <li>Log in to <a href="admin/login.php">admin dashboard</a> with <code>admin</code> / <code>admin123</code></li>
      <li>Register an <a href="employer/register.php">employer account</a> to test job posting flow</li>
    </ol>
  </div>

  <div class="warn">
    <strong>⚠️ Security:</strong> DELETE this <code>setup.php</code> file once everything works.
    Also change the admin password from the default after first login.
  </div>
</div>
</body>
</html>

