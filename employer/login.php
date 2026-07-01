<?php
require_once __DIR__ . '/../db.php';
session_start();
 
// If already logged in, send to dashboard
if (!empty($_SESSION['employer_id'])) {
    header('Location: dashboard.php');
    exit;
}
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
 
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM employers WHERE email=? AND is_active=1");
    $stmt->execute([$email]);
    $emp = $stmt->fetch();
 
    if ($emp && password_verify($password, $emp['password_hash'])) {
        $_SESSION['employer_id']   = $emp['id'];
        $_SESSION['employer_name'] = $emp['company_name'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
 
$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login · Releev Recruitment</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Lato',sans-serif;color:#333;line-height:1.6;min-height:100vh;background:linear-gradient(135deg,#013A73 0%,#0177E3 60%,#38BBFF 100%);display:flex;align-items:center;justify-content:center;padding:20px}
.auth-box{background:#fff;border-radius:16px;padding:48px 40px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
.alert{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:14px;font-weight:600}
.alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-family:'Montserrat',sans-serif;font-weight:600;font-size:13px;color:#333;margin-bottom:6px}
.form-group input{width:100%;padding:12px 14px;border:1.5px solid #e0ecfa;border-radius:8px;font-family:'Lato',sans-serif;font-size:14px;background:#fff}
.form-group input:focus{outline:none;border-color:#0177E3;box-shadow:0 0 0 3px rgba(1,119,227,0.12)}
.btn{display:inline-block;padding:14px;border-radius:8px;font-family:'Montserrat',sans-serif;font-weight:600;font-size:15px;text-decoration:none;border:none;cursor:pointer;transition:all .2s}
.btn-primary{background:linear-gradient(135deg,#0177E3,#013A73);color:#fff;width:100%}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(1,119,227,0.3)}
a{color:#0177E3;text-decoration:none}
</style>
</head>
<body>
<div class="auth-box">
  <h2 style="font-family:'Montserrat',sans-serif;font-weight:800;font-size:22px;color:#333;text-align:center;margin-bottom:6px;">Surely <span style="color:#0177E3;">Sci &amp; Tech</span></h2>
  <p style="text-align:center;color:#666;font-size:14px;margin-bottom:28px;">Employer Sign In</p>
 
  <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
 
  <form method="POST">
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Sign In →</button>
  </form>
 
  <p style="text-align:center;margin-top:24px;color:#666;font-size:14px;">
    Don't have an account? <a href="register.php" style="font-weight:700;">Register here</a>
  </p>
  <p style="text-align:center;margin-top:12px;font-size:13px;">
    <a href="../index.html" style="color:#888;">← Back to website</a>
  </p>
</div>
</body>
</html>
 
