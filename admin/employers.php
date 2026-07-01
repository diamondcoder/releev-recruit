<?php
require_once __DIR__ . '/../db.php';
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = db_connect();

// Toggle active status — done BEFORE any HTML output
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE employers SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
    header('Location: employers.php');
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM employers WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: employers.php');
    exit;
}

$page_title = 'Employers';
require __DIR__ . '/_layout-top.php';

$employers = $pdo->query("SELECT e.*, COUNT(j.id) as job_count FROM employers e LEFT JOIN jobs j ON j.employer_id=e.id GROUP BY e.id ORDER BY e.created_at DESC")->fetchAll();
?>

<div class="main-header">
  <h2>Registered Employers</h2>
  <span class="badge"><?= count($employers) ?> Total</span>
</div>

<div class="card">
<?php if ($employers): ?>
<table>
  <thead>
    <tr><th>Company</th><th>Contact</th><th>Email</th><th>Phone</th><th>Jobs Posted</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($employers as $e): ?>
    <tr>
      <td><strong><?= htmlspecialchars($e['company_name']) ?></strong></td>
      <td><?= htmlspecialchars($e['contact_name']) ?></td>
      <td><a href="mailto:<?= htmlspecialchars($e['email']) ?>"><?= htmlspecialchars($e['email']) ?></a></td>
      <td><?= htmlspecialchars($e['phone'] ?: '—') ?></td>
      <td><span class="pill pill-new"><?= $e['job_count'] ?></span></td>
      <td><span class="pill <?= $e['is_active']?'pill-approved':'pill-rejected' ?>"><?= $e['is_active']?'Active':'Disabled' ?></span></td>
      <td style="font-size:12px;color:#888;"><?= date('M j, Y', strtotime($e['created_at'])) ?></td>
      <td>
        <a href="?toggle=<?= $e['id'] ?>" class="btn btn-primary btn-sm"><?= $e['is_active']?'Disable':'Enable' ?></a>
        <a href="?delete=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete employer and all their jobs?')">🗑</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <div class="empty"><div class="icon">🏢</div>No employers registered yet</div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
