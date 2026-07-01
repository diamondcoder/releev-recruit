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
    $pdo->prepare("DELETE FROM applications WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: applications.php');
    exit;
}

// Status change
if (isset($_GET['status'], $_GET['id'])) {
    $valid = ['new','reviewed','shortlisted','rejected'];
    if (in_array($_GET['status'], $valid)) {
        $pdo->prepare("UPDATE applications SET status=? WHERE id=?")->execute([$_GET['status'], (int)$_GET['id']]);
    }
    header('Location: applications.php');
    exit;
}

$page_title = 'Applications';
require __DIR__ . '/_layout-top.php';

$apps = $pdo->query("SELECT a.*, j.title AS job_title FROM applications a LEFT JOIN jobs j ON j.id=a.job_id ORDER BY a.submitted_at DESC")->fetchAll();
?>

<div class="main-header">
  <h2>Job Applications</h2>
  <span class="badge"><?= count($apps) ?> Total</span>
</div>

<div class="card">
<?php if ($apps): ?>
<table>
  <thead>
    <tr><th>Applicant</th><th>Job</th><th>Email</th><th>CV</th><th>Status</th><th>Date</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($apps as $a): ?>
    <tr>
      <td><strong><?= htmlspecialchars($a['full_name']) ?></strong><?php if($a['phone']): ?><br><small style="color:#888;"><?= htmlspecialchars($a['phone']) ?></small><?php endif; ?></td>
      <td style="font-size:13px;"><?= htmlspecialchars($a['job_title'] ?? 'Job removed') ?></td>
      <td><a href="mailto:<?= htmlspecialchars($a['email']) ?>"><?= htmlspecialchars($a['email']) ?></a></td>
      <td>
        <?php if ($a['cv_filename']): ?>
          <a href="../uploads/applications/<?= htmlspecialchars($a['cv_filename']) ?>" target="_blank" class="btn btn-primary btn-sm">📄 CV</a>
        <?php else: ?>
          <small style="color:#999;">—</small>
        <?php endif; ?>
      </td>
      <td>
        <span class="pill <?= $a['status']==='new'?'pill-new':($a['status']==='shortlisted'?'pill-approved':($a['status']==='rejected'?'pill-rejected':'pill-closed')) ?>"><?= ucfirst($a['status']) ?></span>
      </td>
      <td style="font-size:12px;color:#888;"><?= date('M j, Y', strtotime($a['submitted_at'])) ?></td>
      <td>
        <select onchange="if(this.value)location.href='?id=<?= $a['id'] ?>&status='+this.value" style="padding:5px 8px;border:1px solid #e0ecfa;border-radius:4px;font-size:12px;">
          <option value="">Change status</option>
          <option value="new">New</option>
          <option value="reviewed">Reviewed</option>
          <option value="shortlisted">Shortlisted</option>
          <option value="rejected">Rejected</option>
        </select>
        <a href="?delete=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">🗑</a>
      </td>
    </tr>
    <?php if ($a['cover_message']): ?>
    <tr><td colspan="7" style="background:#fafcff;padding:0 16px 16px 16px;">
      <details><summary>View cover message</summary>
        <p style="margin-top:10px;line-height:1.7;color:#555;"><?= nl2br(htmlspecialchars($a['cover_message'])) ?></p>
        <?php if ($a['linkedin']): ?><p style="margin-top:8px;"><strong>LinkedIn:</strong> <a href="<?= htmlspecialchars($a['linkedin']) ?>" target="_blank"><?= htmlspecialchars($a['linkedin']) ?></a></p><?php endif; ?>
      </details>
    </td></tr>
    <?php endif; ?>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <div class="empty"><div class="icon">📋</div>No applications yet</div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
