<?php
/**
 * Admin shared layout
 */
require_once __DIR__ . '/../db.php';
session_start();

// Only allow access if logged in (except login page)
$current = basename($_SERVER['PHP_SELF']);
if ($current !== 'login.php' && empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= isset($page_title) ? $page_title . ' · ' : '' ?>Admin · Releev Recruitment</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Lato',sans-serif;background:#f4f8ff;color:#333;line-height:1.6}
  a{color:#0177E3;text-decoration:none}
  .layout{display:flex;min-height:100vh}
  .sidebar{width:240px;background:linear-gradient(180deg,#013A73,#0177E3);color:#fff;padding:24px 0;position:fixed;height:100vh;overflow-y:auto}
  .sidebar h1{font-family:'Montserrat',sans-serif;font-weight:800;font-size:18px;padding:0 24px 24px;border-bottom:1px solid rgba(255,255,255,0.15);margin-bottom:16px}
  .sidebar h1 span{color:#38BBFF}
  .sidebar nav a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,0.85);padding:12px 24px;font-family:'Montserrat',sans-serif;font-weight:500;font-size:14px;border-left:3px solid transparent;transition:all .2s}
  .sidebar nav a:hover{background:rgba(255,255,255,0.08);color:#fff}
  .sidebar nav a.active{background:rgba(255,255,255,0.12);border-left-color:#38BBFF;color:#fff;font-weight:700}
  .sidebar .logout{position:absolute;bottom:0;left:0;right:0;padding:16px 24px;border-top:1px solid rgba(255,255,255,0.15);background:rgba(0,0,0,0.15)}
  .sidebar .logout a{color:rgba(255,255,255,0.7);font-size:13px}
  .main{flex:1;margin-left:240px;padding:32px 40px}
  .main-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px}
  .main-header h2{font-family:'Montserrat',sans-serif;font-weight:800;font-size:26px;color:#333}
  .main-header .badge{background:#0177E3;color:#fff;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;font-family:'Montserrat',sans-serif}
  .card{background:#fff;border-radius:12px;padding:28px;border:1px solid #e0ecfa;box-shadow:0 4px 20px rgba(1,119,227,0.08);margin-bottom:24px}
  .card h3{font-family:'Montserrat',sans-serif;font-weight:700;font-size:18px;margin-bottom:16px;color:#333}
  .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:32px}
  .stat-box{background:#fff;border-radius:12px;padding:24px;border:1px solid #e0ecfa;border-left:4px solid #0177E3}
  .stat-box .num{font-family:'Montserrat',sans-serif;font-weight:900;font-size:32px;color:#0177E3;letter-spacing:-1px}
  .stat-box .lbl{font-size:13px;color:#666;font-family:'Montserrat',sans-serif;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;margin-top:4px}
  table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden}
  th{background:#f4f8ff;color:#333;padding:14px 16px;text-align:left;font-family:'Montserrat',sans-serif;font-weight:700;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;border-bottom:2px solid #e0ecfa}
  td{padding:14px 16px;border-bottom:1px solid #f0f4fa;font-size:14px;color:#444}
  tr:hover{background:#fafcff}
  .btn{display:inline-block;padding:8px 16px;border-radius:6px;font-family:'Montserrat',sans-serif;font-weight:600;font-size:13px;text-decoration:none;border:none;cursor:pointer;transition:all .2s}
  .btn-primary{background:linear-gradient(135deg,#0177E3,#013A73);color:#fff}
  .btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(1,119,227,0.3)}
  .btn-success{background:#16a34a;color:#fff}
  .btn-success:hover{background:#15803d}
  .btn-danger{background:#dc2626;color:#fff}
  .btn-danger:hover{background:#b91c1c}
  .btn-sm{padding:6px 12px;font-size:12px}
  .pill{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;font-family:'Montserrat',sans-serif;letter-spacing:0.4px}
  .pill-pending{background:#fef3c7;color:#b45309}
  .pill-approved{background:#d1fae5;color:#065f46}
  .pill-rejected{background:#fee2e2;color:#991b1b}
  .pill-closed{background:#e5e7eb;color:#374151}
  .pill-new{background:#dbeafe;color:#1e40af}
  .pill-read{background:#e5e7eb;color:#374151}
  .empty{text-align:center;padding:60px 20px;color:#888}
  .empty .icon{font-size:48px;margin-bottom:12px;opacity:0.5}
  .form-group{margin-bottom:16px}
  .form-group label{display:block;font-family:'Montserrat',sans-serif;font-weight:600;font-size:13px;color:#333;margin-bottom:6px}
  .form-group input,.form-group textarea,.form-group select{width:100%;padding:10px 14px;border:1.5px solid #e0ecfa;border-radius:8px;font-family:'Lato',sans-serif;font-size:14px;background:#fff}
  .form-group input:focus,.form-group textarea:focus,.form-group select:focus{outline:none;border-color:#0177E3;box-shadow:0 0 0 3px rgba(1,119,227,0.12)}
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .alert{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:14px;font-weight:600}
  .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7}
  .alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
  details{background:#f4f8ff;padding:14px 18px;border-radius:8px;margin-top:8px;font-size:13px}
  details summary{cursor:pointer;font-weight:600;color:#0177E3}
</style>
</head>
<body>
<div class="layout">
<?php if (!empty($_SESSION['admin_id'])): ?>
<aside class="sidebar">
  <h1>Releev <span>Recruitment</span></h1>
  <nav>
    <a href="dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">📊 Dashboard</a>
    <a href="messages.php" class="<?= $current==='messages.php'?'active':'' ?>">✉️ Messages</a>
    <a href="candidates.php" class="<?= $current==='candidates.php'?'active':'' ?>">👤 Candidates</a>
    <a href="applications.php" class="<?= $current==='applications.php'?'active':'' ?>">📋 Applications</a>
    <a href="jobs.php" class="<?= $current==='jobs.php'?'active':'' ?>">💼 Jobs</a>
    <a href="approve-jobs.php" class="<?= $current==='approve-jobs.php'?'active':'' ?>">✅ Approve Jobs</a>
    <a href="employers.php" class="<?= $current==='employers.php'?'active':'' ?>">🏢 Employers</a>
  </nav>
  <div class="logout">
    <p style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:6px;">Logged in as</p>
    <p style="font-weight:700;font-size:14px;color:#fff;margin-bottom:8px;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></p>
    <a href="logout.php">→ Logout</a>
  </div>
</aside>
<?php endif; ?>
<main class="main">

