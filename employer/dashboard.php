<?php
$page_title = 'Dashboard';
require __DIR__ . '/_layout-top.php';

$pdo = db_connect();
$emp_id = $_SESSION['employer_id'];

$stats = [
    'total'     => $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id=?"),
    'pending'   => $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id=? AND status='pending'"),
    'approved'  => $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id=? AND status='approved'"),
    'rejected'  => $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id=? AND status='rejected'"),
];
foreach ($stats as $k => $s) { $s->execute([$emp_id]); $stats[$k] = $s->fetchColumn(); }

$recent_stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id=? ORDER BY posted_at DESC LIMIT 5");
$recent_stmt->execute([$emp_id]);
$recent = $recent_stmt->fetchAll();
?>

<div class="container">
  <?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success">🎉 Account created successfully! You can now post jobs.</div>
  <?php endif; ?>

  <div class="page-head">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['employer_name']) ?> 👋</h2>
    <a href="post-job.php" class="btn btn-primary">+ Post New Job</a>
  </div>

  <div class="stats">
    <div class="stat-box"><div class="num"><?= $stats['total'] ?></div><div class="lbl">Total Jobs</div></div>
    <div class="stat-box" style="border-left-color:#fbbf24;"><div class="num" style="color:#b45309;"><?= $stats['pending'] ?></div><div class="lbl">Pending Approval</div></div>
    <div class="stat-box" style="border-left-color:#16a34a;"><div class="num" style="color:#16a34a;"><?= $stats['approved'] ?></div><div class="lbl">Live Jobs</div></div>
    <div class="stat-box" style="border-left-color:#dc2626;"><div class="num" style="color:#dc2626;"><?= $stats['rejected'] ?></div><div class="lbl">Rejected</div></div>
  </div>

  <div class="card">
    <h3>Recent Job Postings</h3>
    <?php if ($recent): ?>
    <table>
      <thead><tr><th>Title</th><th>Sector</th><th>Status</th><th>Posted</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recent as $j): ?>
        <tr>
          <td><strong><?= htmlspecialchars($j['title']) ?></strong></td>
          <td><?= htmlspecialchars($j['sector']) ?></td>
          <td><span class="pill pill-<?= $j['status'] ?>"><?= ucfirst($j['status']) ?></span></td>
          <td style="font-size:12px;color:#888;"><?= date('M j, Y', strtotime($j['posted_at'])) ?></td>
          <td><a href="my-jobs.php" class="btn btn-sm btn-primary">View</a></td>
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

  <div class="card" style="background:#dbeafe;border-color:#93c5fd;">
    <h3 style="color:#1e40af;">📌 How It Works</h3>
    <ol style="color:#444;padding-left:20px;line-height:2;font-size:14px;">
      <li>Submit your job listing using the <strong>Post a Job</strong> page</li>
      <li>Our admin team reviews each posting (usually within 1 business day)</li>
      <li>Once approved, your job goes live on the public Jobs page</li>
      <li>Candidates can apply directly through the website</li>
      <li>You'll receive applications via email and can manage them with our team</li>
    </ol>
  </div>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
