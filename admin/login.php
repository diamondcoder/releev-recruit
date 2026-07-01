<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $pdo  = db_connect();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username=? OR email=?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in both fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login · Releev Recruitment</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Lato',sans-serif;background:linear-gradient(135deg,#013A73 0%,#0177E3 60%,#38BBFF 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{background:#fff;border-radius:16px;padding:48px 40px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
.logo{text-align:center;margin-bottom:32px}
.logo h1{font-family:'Montserrat',sans-serif;font-weight:800;font-size:22px;color:#333}
.logo h1 span{color:#0177E3}
.logo p{color:#666;font-size:14px;margin-top:4px}
h2{font-family:'Montserrat',sans-serif;font-weight:700;font-size:20px;color:#333;text-align:center;margin-bottom:6px}
.subtitle{text-align:center;color:#666;font-size:14px;margin-bottom:28px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-family:'Montserrat',sans-serif;font-weight:600;font-size:13px;color:#333;margin-bottom:6px}
.form-group input{width:100%;padding:13px 16px;border:1.5px solid #e0ecfa;border-radius:8px;font-family:'Lato',sans-serif;font-size:15px}
.form-group input:focus{outline:none;border-color:#0177E3;box-shadow:0 0 0 3px rgba(1,119,227,0.12)}
.btn{width:100%;background:linear-gradient(135deg,#0177E3,#013A73);color:#fff;border:none;padding:14px;border-radius:10px;font-family:'Montserrat',sans-serif;font-weight:700;font-size:15px;cursor:pointer;transition:all .2s;margin-top:8px}
.btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(1,119,227,0.4)}
.error{background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:14px;margin-bottom:18px;text-align:center}
.hint{margin-top:24px;padding-top:24px;border-top:1px solid #e0ecfa;text-align:center;color:#888;font-size:13px;line-height:1.7}
.hint code{background:#f4f8ff;padding:2px 8px;border-radius:4px;color:#0177E3;font-family:monospace}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <h1>Releev <span>Recruitment</span></h1>
    <p>Admin Dashboard</p>
  </div>
  <h2>Welcome Back</h2>
  <p class="subtitle">Sign in to manage your site</p>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Username or Email</label>
      <input type="text" name="username" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">Sign In →</button>
  </form>
  <div class="hint">
    Default credentials:<br>
    Username: <code>admin</code> · Password: <code>admin123</code><br>
    <small style="color:#aaa;">(change immediately after first login)</small>
  </div>
</div>
</body>
</html>

