<?php
require_once __DIR__ . '/../db.php';
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = db_connect();

// Delete — done BEFORE any HTML output
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM candidates WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: candidates.php');
    exit;
}

$page_title = 'Candidates';
require __DIR__ . '/_layout-top.php';

$candidates = $pdo->query("SELECT * FROM candidates ORDER BY submitted_at DESC")->fetchAll();
?>

<div class="main-header">
  <h2>Candidate Submissions</h2>
  <span class="badge"><?= count($candidates) ?> Total</span>
</div>

<div class="card">
<?php if ($candidates): ?>
<table>
  <thead>
    <tr><th>Name</th><th>Email</th><th>Sector</th><th>Current Role</th><th>CV</th><th>Date</th><th>Action</th></tr>
  </thead>
  <tbody>
    <?php foreach ($candidates as $c): ?>
    <tr>
      <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
      <td><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a><?php if($c['phone']): ?><br><small><?= htmlspecialchars($c['phone']) ?></small><?php endif; ?></td>
      <td><span class="pill pill-new"><?= htmlspecialchars($c['sector']) ?></span></td>
      <td style="font-size:13px;"><?= htmlspecialchars($c['role_now'] ?: '—') ?></td>
      <td>
        <?php if ($c['cv_filename']): ?>
          <a href="../uploads/cvs/<?= htmlspecialchars($c['cv_filename']) ?>" target="_blank" class="btn btn-primary btn-sm">📄 View CV</a>
        <?php else: ?>
          <small style="color:#999;">—</small>
        <?php endif; ?>
      </td>
      <td style="font-size:12px;color:#888;"><?= date('M j, Y', strtotime($c['submitted_at'])) ?></td>
      <td>
        <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">🗑</a>
      </td>
    </tr>
    <?php if ($c['message']): ?>
    <tr><td colspan="7" style="background:#fafcff;padding:0 16px 16px 16px;">
      <details><summary>View message + LinkedIn</summary>
        <p style="margin-top:10px;line-height:1.7;color:#555;"><?= nl2br(htmlspecialchars($c['message'])) ?></p>
        <?php if ($c['linkedin']): ?><p style="margin-top:8px;"><strong>LinkedIn:</strong> <a href="<?= htmlspecialchars($c['linkedin']) ?>" target="_blank"><?= htmlspecialchars($c['linkedin']) ?></a></p><?php endif; ?>
      </details>
    </td></tr>
    <?php endif; ?>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <div class="empty"><div class="icon">👤</div>No candidate submissions yet</div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
