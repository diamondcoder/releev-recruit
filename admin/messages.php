<?php
require_once __DIR__ . '/../db.php';
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = db_connect();

// Mark as read action — done BEFORE any HTML output
if (isset($_GET['read'])) {
    $pdo->prepare("UPDATE contacts SET is_read=1 WHERE id=?")->execute([(int)$_GET['read']]);
    header('Location: messages.php');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM contacts WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: messages.php');
    exit;
}

$page_title = 'Messages';
require __DIR__ . '/_layout-top.php';

$messages = $pdo->query("SELECT * FROM contacts ORDER BY submitted_at DESC")->fetchAll();
?>

<div class="main-header">
  <h2>Contact Messages</h2>
  <span class="badge"><?= count($messages) ?> Total</span>
</div>

<div class="card">
<?php if ($messages): ?>
  <?php foreach ($messages as $m): ?>
  <div style="border:1px solid <?= $m['is_read'] ? '#e0ecfa' : '#0177E3' ?>;border-radius:10px;padding:18px 22px;margin-bottom:14px;background:<?= $m['is_read'] ? '#fff' : '#f4f8ff' ?>;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:10px;">
      <div>
        <strong style="font-family:'Montserrat',sans-serif;font-size:16px;"><?= htmlspecialchars($m['first_name'].' '.$m['last_name']) ?></strong>
        <span class="pill <?= $m['is_read']?'pill-read':'pill-new' ?>" style="margin-left:10px;"><?= $m['is_read']?'Read':'New' ?></span>
        <div style="font-size:13px;color:#666;margin-top:2px;">
          <a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a>
          <?php if ($m['phone']): ?> · <?= htmlspecialchars($m['phone']) ?><?php endif; ?>
          <?php if ($m['enquiry_type']): ?> · <em><?= htmlspecialchars($m['enquiry_type']) ?></em><?php endif; ?>
        </div>
      </div>
      <span style="font-size:12px;color:#888;white-space:nowrap;"><?= date('M j, Y H:i', strtotime($m['submitted_at'])) ?></span>
    </div>
    <p style="font-weight:700;color:#0177E3;margin-bottom:6px;font-size:14px;"><?= htmlspecialchars($m['subject']) ?></p>
    <p style="color:#444;line-height:1.7;font-size:14px;"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
    <div style="margin-top:14px;display:flex;gap:8px;">
      <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn btn-primary btn-sm">↩ Reply</a>
      <?php if (!$m['is_read']): ?><a href="?read=<?= $m['id'] ?>" class="btn btn-success btn-sm">✓ Mark Read</a><?php endif; ?>
      <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this message?')">🗑 Delete</a>
    </div>
  </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="empty"><div class="icon">✉️</div>No contact messages yet</div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
