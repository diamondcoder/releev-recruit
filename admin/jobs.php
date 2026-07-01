<?php
require_once __DIR__ . '/../db.php';
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = db_connect();
$success = '';

// Handle create/update — done BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = (int)($_POST['id'] ?? 0);
    $title         = clean($_POST['title']);
    $sector        = clean($_POST['sector']);
    $location      = clean($_POST['location']);
    $contract_type = clean($_POST['contract_type']);
    $hours         = clean($_POST['hours']);
    $salary        = clean($_POST['salary']);
    $description   = clean($_POST['description']);
    $requirements  = clean($_POST['requirements']);
    $status        = clean($_POST['status']);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE jobs SET title=?, sector=?, location=?, contract_type=?, hours=?, salary=?, description=?, requirements=?, status=? WHERE id=?");
        $stmt->execute([$title, $sector, $location, $contract_type, $hours, $salary, $description, $requirements, $status, $id]);
        $success = 'Job updated.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO jobs (posted_by, title, sector, location, contract_type, hours, salary, description, requirements, status, approved_at) VALUES ('admin', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $sector, $location, $contract_type, $hours, $salary, $description, $requirements, $status]);
        $success = 'Job created.';
    }
}

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM jobs WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: jobs.php');
    exit;
}

// Edit existing
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$page_title = 'Jobs';
require __DIR__ . '/_layout-top.php';

$jobs = $pdo->query("SELECT j.*, e.company_name FROM jobs j LEFT JOIN employers e ON j.employer_id=e.id ORDER BY j.posted_at DESC")->fetchAll();
?>

<div class="main-header">
  <h2><?= $edit ? 'Edit Job' : 'Manage Jobs' ?></h2>
  <span class="badge"><?= count($jobs) ?> Total</span>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="card">
  <h3><?= $edit ? '✏️ Edit Job' : '➕ Create New Job' ?></h3>
  <form method="POST">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="form-group">
      <label>Title *</label>
      <input type="text" name="title" required value="<?= htmlspecialchars($edit['title'] ?? '') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Sector *</label>
        <select name="sector" required>
          <?php foreach (['Life Sciences','Engineering','Management'] as $s): ?>
            <option value="<?= $s ?>" <?= ($edit['sector'] ?? '')===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Location *</label>
        <input type="text" name="location" required value="<?= htmlspecialchars($edit['location'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Contract Type</label>
        <select name="contract_type">
          <?php foreach (['Permanent','Contract','Temporary','Internship'] as $t): ?>
            <option value="<?= $t ?>" <?= ($edit['contract_type'] ?? 'Permanent')===$t?'selected':'' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Hours</label>
        <select name="hours">
          <option value="Full-time" <?= ($edit['hours'] ?? 'Full-time')==='Full-time'?'selected':'' ?>>Full-time</option>
          <option value="Part-time" <?= ($edit['hours'] ?? '')==='Part-time'?'selected':'' ?>>Part-time</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Salary / Package</label>
      <input type="text" name="salary" value="<?= htmlspecialchars($edit['salary'] ?? '') ?>" placeholder="e.g. Competitive, 50k–65k SEK/mo">
    </div>
    <div class="form-group">
      <label>Description *</label>
      <textarea name="description" rows="5" required><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Requirements (optional)</label>
      <textarea name="requirements" rows="3"><?= htmlspecialchars($edit['requirements'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Status</label>
      <select name="status">
        <?php foreach (['approved','pending','rejected','closed'] as $s): ?>
          <option value="<?= $s ?>" <?= ($edit['status'] ?? 'approved')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary"><?= $edit ? '💾 Update Job' : '➕ Create Job' ?></button>
    <?php if ($edit): ?> <a href="jobs.php" class="btn" style="background:#e5e7eb;color:#333;">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <h3>All Jobs</h3>
  <?php if ($jobs): ?>
  <table>
    <thead><tr><th>Title</th><th>Sector</th><th>Location</th><th>Posted By</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($jobs as $j): ?>
      <tr>
        <td><strong><?= htmlspecialchars($j['title']) ?></strong></td>
        <td><?= htmlspecialchars($j['sector']) ?></td>
        <td><?= htmlspecialchars($j['location']) ?></td>
        <td style="font-size:13px;"><?= htmlspecialchars($j['company_name'] ?: ucfirst($j['posted_by'])) ?></td>
        <td><span class="pill pill-<?= $j['status'] ?>"><?= ucfirst($j['status']) ?></span></td>
        <td style="font-size:12px;color:#888;"><?= date('M j, Y', strtotime($j['posted_at'])) ?></td>
        <td>
          <a href="?edit=<?= $j['id'] ?>" class="btn btn-primary btn-sm">✏️</a>
          <a href="?delete=<?= $j['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this job?')">🗑</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty"><div class="icon">💼</div>No jobs yet</div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
