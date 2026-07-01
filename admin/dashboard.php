<?php
$page_title = 'Dashboard';
require __DIR__ . '/_layout-top.php';

$pdo = db_connect();
$stats = [
    'messages_unread' => $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn(),
    'messages_total'  => $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn(),
    'candidates'      => $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn(),
    'applications'    => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn(),
    'jobs_pending'    => $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='pending'")->fetchColumn(),
    'jobs_approved'   => $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='approved'")->fetchColumn(),
];

$recent_messages = $pdo->query("SELECT * FROM contacts ORDER BY submitted_at DESC LIMIT 5")->fetchAll();
$recent_apps     = $pdo->query("SELECT a.*, j.title FROM applications a LEFT JOIN jobs j ON j.id=a.job_id ORDER BY a.submitted_at DESC LIMIT 5")->fetchAll();
?>

<div class="main-header">
  <h2>Dashboard</h2>
  <span class="badge"><?= date('l, F j, Y') ?></span>
</div>

<div class="stats">
  <div class="stat-box"><div class="num"><?= $stats['messages_unread'] ?></div><div class="lbl">Unread Messages</div></div>
  <div class="stat-box"><div class="num"><?= $stats['candidates'] ?></div><div class="lbl">Candidate CVs</div></div>
  <div class="stat-box"><div class="num"><?= $stats['applications'] ?></div><div class="lbl">Job Applications</div></div>
  <div class="stat-box"><div class="num"><?= $stats['jobs_pending'] ?></div><div class="lbl">Jobs Pending</div></div>
  <div class="stat-box"><div class="num"><?= $stats['jobs_approved'] ?></div><div class="lbl">Active Jobs</div></div>
  <div class="stat-box"><div class="num"><?= $stats['messages_total'] ?></div><div class="lbl">Total Messages</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  <div class="card">
    <h3>Recent Messages</h3>
    <?php if ($recent_messages): ?>
    <table>
      <thead><tr><th>From</th><th>Subject</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recent_messages as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['first_name'].' '.$m['last_name']) ?><br><small style="color:#888;"><?= htmlspecialchars($m['email']) ?></small></td>
          <td><?= htmlspecialchars(mb_strimwidth($m['subject'], 0, 35, '...')) ?></td>
          <td style="font-size:12px;color:#888;"><?= date('M j, H:i', strtotime($m['submitted_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty"><div class="icon">✉️</div>No messages yet</div>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>Recent Applications</h3>
    <?php if ($recent_apps): ?>
    <table>
      <thead><tr><th>Candidate</th><th>Job</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recent_apps as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['full_name']) ?></td>
          <td style="font-size:13px;"><?= htmlspecialchars(mb_strimwidth($a['title'] ?? 'Unknown', 0, 30, '...')) ?></td>
          <td style="font-size:12px;color:#888;"><?= date('M j', strtotime($a['submitted_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty"><div class="icon">📋</div>No applications yet</div>
    <?php endif; ?>
  </div>

</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
