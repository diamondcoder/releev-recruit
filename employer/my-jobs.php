<?php
require_once __DIR__ . '/../db.php';
session_start();

if (empty($_SESSION['employer_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = db_connect();
$emp_id = $_SESSION['employer_id'];

// Delete only own jobs — done BEFORE any HTML output
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id=? AND employer_id=?");
    $stmt->execute([(int)$_GET['delete'], $emp_id]);
    header('Location: my-jobs.php');
    exit;
}

$page_title = 'My Jobs';
require __DIR__ . '/_layout-top.php';

$stmt = $pdo->prepare("SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) as app_count FROM jobs j WHERE employer_id=? ORDER BY posted_at DESC");
$stmt->execute([$emp_id]);
$jobs = $stmt->fetchAll();
?>

<div class="container">
  <div class="page-head">
    <h2>My Job Postings</h2>
    <a href="post-job.php" class="btn btn-primary">+ Post New Job</a>
  </div>

  <div class="card">
    <?php if ($jobs): ?>
    <table>
      <thead><tr><th>Title</th><th>Sector</th><th>Location</th><th>Status</th><th>Applications</th><th>Posted</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($jobs as $j): ?>
        <tr>
          <td><strong><?= htmlspecialchars($j['title']) ?></strong></td>
          <td><?= htmlspecialchars($j['sector']) ?></td>
          <td><?= htmlspecialchars($j['location']) ?></td>
          <td>
            <span class="pill pill-<?= $j['status'] ?>"><?= ucfirst($j['status']) ?></span>
            <?php if ($j['status'] === 'rejected' && $j['rejection_reason']): ?>
              <br><small style="color:#dc2626;font-size:11px;"><?= htmlspecialchars($j['rejection_reason']) ?></small>
            <?php endif; ?>
          </td>
          <td><span class="pill" style="background:#dbeafe;color:#1e40af;"><?= $j['app_count'] ?></span></td>
          <td style="font-size:12px;color:#888;"><?= date('M j, Y', strtotime($j['posted_at'])) ?></td>
          <td>
            <a href="?delete=<?= $j['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this job?')">🗑 Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty">
        <div class="icon">💼</div>
        You haven't posted any jobs yet.
        <p style="margin-top:14px;"><a href="post-job.php" class="btn btn-primary">Post Your First Job</a></p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
