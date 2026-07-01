<?php
$page_title = 'Approve Jobs';
require __DIR__ . '/_layout-top.php';

$pdo = db_connect();

// Approve / Reject
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE jobs SET status='approved', approved_at=NOW() WHERE id=?")->execute([(int)$_GET['approve']]);
    header('Location: approve-jobs.php');
    exit;
}
if (isset($_GET['reject'])) {
    $pdo->prepare("UPDATE jobs SET status='rejected' WHERE id=?")->execute([(int)$_GET['reject']]);
    header('Location: approve-jobs.php');
    exit;
}

$pending = $pdo->query("SELECT j.*, e.company_name, e.contact_name, e.email FROM jobs j LEFT JOIN employers e ON e.id=j.employer_id WHERE j.status='pending' ORDER BY j.posted_at DESC")->fetchAll();
?>

<div class="main-header">
  <h2>Pending Job Approvals</h2>
  <span class="badge"><?= count($pending) ?> Pending</span>
</div>

<div class="card">
<?php if ($pending): ?>
  <?php foreach ($pending as $j): ?>
  <div style="border:2px solid #fbbf24;border-radius:10px;padding:22px 26px;margin-bottom:16px;background:#fffbeb;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:14px;">
      <div>
        <h3 style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:18px;margin-bottom:6px;"><?= htmlspecialchars($j['title']) ?></h3>
        <div style="font-size:13px;color:#666;">
          <span class="pill pill-new"><?= htmlspecialchars($j['sector']) ?></span>
          📍 <?= htmlspecialchars($j['location']) ?> ·
          💼 <?= htmlspecialchars($j['contract_type']) ?> ·
          ⏱ <?= htmlspecialchars($j['hours']) ?>
          <?php if ($j['salary']): ?> · 💰 <?= htmlspecialchars($j['salary']) ?><?php endif; ?>
        </div>
        <div style="font-size:13px;color:#666;margin-top:8px;">
          <strong>Submitted by:</strong>
          <?= htmlspecialchars($j['company_name'] ?? 'Unknown') ?>
          <?php if ($j['contact_name']): ?>· <?= htmlspecialchars($j['contact_name']) ?><?php endif; ?>
          <?php if ($j['email']): ?>· <a href="mailto:<?= htmlspecialchars($j['email']) ?>"><?= htmlspecialchars($j['email']) ?></a><?php endif; ?>
        </div>
      </div>
      <span style="font-size:12px;color:#888;white-space:nowrap;"><?= date('M j, Y H:i', strtotime($j['posted_at'])) ?></span>
    </div>

    <div style="background:#fff;padding:16px;border-radius:8px;margin-bottom:14px;">
      <p style="font-weight:700;font-size:13px;color:#333;margin-bottom:6px;">Description</p>
      <p style="color:#555;line-height:1.7;font-size:14px;"><?= nl2br(htmlspecialchars($j['description'])) ?></p>
      <?php if ($j['requirements']): ?>
      <p style="font-weight:700;font-size:13px;color:#333;margin:14px 0 6px;">Requirements</p>
      <p style="color:#555;line-height:1.7;font-size:14px;"><?= nl2br(htmlspecialchars($j['requirements'])) ?></p>
      <?php endif; ?>
    </div>

    <div style="display:flex;gap:8px;">
      <a href="?approve=<?= $j['id'] ?>" class="btn btn-success" onclick="return confirm('Approve and publish this job?')">✓ Approve &amp; Publish</a>
      <a href="?reject=<?= $j['id'] ?>" class="btn btn-danger" onclick="return confirm('Reject this job?')">✕ Reject</a>
      <a href="jobs.php?edit=<?= $j['id'] ?>" class="btn btn-primary">✏️ Edit before approving</a>
    </div>
  </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="empty"><div class="icon">✅</div>No jobs pending approval. All caught up!</div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
