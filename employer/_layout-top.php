<?php
require_once __DIR__ . '/../db.php';
session_start();

$current = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'register.php'];
if (!in_array($current, $public_pages) && empty($_SESSION['employer_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= isset($page_title) ? $page_title . ' · ' : '' ?>Employer Area · Releev Recruitment</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Lato',sans-serif;background:#f4f8ff;color:#333;line-height:1.6;min-height:100vh}
a{color:#0177E3;text-decoration:none}
.topbar{background:linear-gradient(135deg,#013A73,#0177E3);color:#fff;padding:16px 5%;display:flex;justify-content:space-between;align-items:center}
.topbar h1{font-family:'Montserrat',sans-serif;font-weight:800;font-size:18px}
.topbar h1 span{color:#38BBFF}
.topbar nav{display:flex;gap:24px;align-items:center}
.topbar nav a{color:rgba(255,255,255,0.85);font-family:'Montserrat',sans-serif;font-weight:500;font-size:14px}
.topbar nav a:hover,.topbar nav a.active{color:#fff}
.topbar nav .me{padding-left:24px;border-left:1px solid rgba(255,255,255,0.2);font-size:13px;color:rgba(255,255,255,0.7)}
.container{max-width:1100px;margin:0 auto;padding:32px 5%}
.page-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:28px}
.page-head h2{font-family:'Montserrat',sans-serif;font-weight:800;font-size:26px;color:#333}
.card{background:#fff;border-radius:12px;padding:32px;border:1px solid #e0ecfa;box-shadow:0 4px 20px rgba(1,119,227,0.08);margin-bottom:24px}
.card h3{font-family:'Montserrat',sans-serif;font-weight:700;font-size:18px;margin-bottom:18px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:32px}
.stat-box{background:#fff;border-radius:12px;padding:24px;border:1px solid #e0ecfa;border-left:4px solid #0177E3}
.stat-box .num{font-family:'Montserrat',sans-serif;font-weight:900;font-size:30px;color:#0177E3}
.stat-box .lbl{font-size:13px;color:#666;font-weight:600;text-transform:uppercase;font-family:'Montserrat',sans-serif;margin-top:4px}
table{width:100%;border-collapse:collapse}
th{background:#f4f8ff;padding:14px 16px;text-align:left;font-family:'Montserrat',sans-serif;font-weight:700;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;border-bottom:2px solid #e0ecfa;color:#333}
td{padding:14px 16px;border-bottom:1px solid #f0f4fa;font-size:14px;color:#444}
.btn{display:inline-block;padding:10px 20px;border-radius:8px;font-family:'Montserrat',sans-serif;font-weight:600;font-size:14px;text-decoration:none;border:none;cursor:pointer;transition:all .2s}
.btn-primary{background:linear-gradient(135deg,#0177E3,#013A73);color:#fff}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(1,119,227,0.3)}
.btn-danger{background:#dc2626;color:#fff}
.btn-sm{padding:6px 12px;font-size:12px}
.pill{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;font-family:'Montserrat',sans-serif}
.pill-pending{background:#fef3c7;color:#b45309}
.pill-approved{background:#d1fae5;color:#065f46}
.pill-rejected{background:#fee2e2;color:#991b1b}
.pill-closed{background:#e5e7eb;color:#374151}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-family:'Montserrat',sans-serif;font-weight:600;font-size:13px;color:#333;margin-bottom:6px}
.form-group input,.form-group textarea,.form-group select{width:100%;padding:12px 14px;border:1.5px solid #e0ecfa;border-radius:8px;font-family:'Lato',sans-serif;font-size:14px;background:#fff}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{outline:none;border-color:#0177E3;box-shadow:0 0 0 3px rgba(1,119,227,0.12)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.alert{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:14px;font-weight:600}
.alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7}
.alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
.alert-info{background:#dbeafe;color:#1e40af;border:1px solid #93c5fd}
.empty{text-align:center;padding:60px 20px;color:#888}
.empty .icon{font-size:48px;margin-bottom:12px;opacity:0.5}
.auth-bg{min-height:100vh;background:linear-gradient(135deg,#013A73 0%,#0177E3 60%,#38BBFF 100%);display:flex;align-items:center;justify-content:center;padding:20px}
.auth-box{background:#fff;border-radius:16px;padding:48px 40px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
</style>
</head>
<body>
<?php if (!empty($_SESSION['employer_id'])): ?>
<div class="topbar">
  <h1>Releev <span>Recruitment</span> · Employers</h1>
  <nav>
    <a href="dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">Dashboard</a>
    <a href="post-job.php" class="<?= $current==='post-job.php'?'active':'' ?>">Post a Job</a>
    <a href="my-jobs.php" class="<?= $current==='my-jobs.php'?'active':'' ?>">My Jobs</a>
    <span class="me">👤 <?= htmlspecialchars($_SESSION['employer_name'] ?? 'Employer') ?> · <a href="logout.php" style="color:#fbbf24;">Logout</a></span>
  </nav>
</div>
<?php endif; ?>

