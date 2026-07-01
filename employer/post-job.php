<?php
$page_title = 'Post a Job';
require __DIR__ . '/_layout-top.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = clean($_POST['title']);
    $sector        = clean($_POST['sector']);
    $location      = clean($_POST['location']);
    $contract_type = clean($_POST['contract_type']);
    $hours         = clean($_POST['hours']);
    $salary        = clean($_POST['salary']);
    $description   = clean($_POST['description']);
    $requirements  = clean($_POST['requirements']);

    if (!$title || !$sector || !$location || !$description) {
        $error = 'Please fill in title, sector, location and description.';
    } else {
        $pdo  = db_connect();
        $stmt = $pdo->prepare("INSERT INTO jobs (employer_id, posted_by, title, sector, location, contract_type, hours, salary, description, requirements, status) VALUES (?, 'employer', ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$_SESSION['employer_id'], $title, $sector, $location, $contract_type, $hours, $salary, $description, $requirements]);
        $success = 'Job submitted! Our admin team will review it shortly. You\'ll be notified once it\'s live.';
        $_POST = [];
    }
}
?>
<div class="container">
  <div class="page-head"><h2>Post a New Job</h2></div>

  <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="alert alert-info">
    📌 <strong>How submission works:</strong> Your job will be reviewed by our admin team and published once approved (usually within 1 business day).
  </div>

  <div class="card">
    <form method="POST">
      <div class="form-group">
        <label>Job Title *</label>
        <input type="text" name="title" required placeholder="e.g. Senior Research Scientist" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Sector *</label>
          <select name="sector" required>
            <option value="">Select sector</option>
            <?php foreach (['Life Sciences','Engineering','Management'] as $s): ?>
              <option value="<?= $s ?>" <?= ($_POST['sector'] ?? '')===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Location *</label>
          <input type="text" name="location" required placeholder="e.g. Göteborg, Sweden" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Contract Type</label>
          <select name="contract_type">
            <?php foreach (['Permanent','Contract','Temporary','Internship'] as $t): ?>
              <option value="<?= $t ?>" <?= ($_POST['contract_type'] ?? 'Permanent')===$t?'selected':'' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Hours</label>
          <select name="hours">
            <option value="Full-time" <?= ($_POST['hours'] ?? 'Full-time')==='Full-time'?'selected':'' ?>>Full-time</option>
            <option value="Part-time" <?= ($_POST['hours'] ?? '')==='Part-time'?'selected':'' ?>>Part-time</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Salary / Package</label>
        <input type="text" name="salary" placeholder="e.g. Competitive, 50k–65k SEK/month" value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Job Description *</label>
        <textarea name="description" rows="6" required placeholder="Describe the role, responsibilities, and what makes it exciting..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>Requirements</label>
        <textarea name="requirements" rows="4" placeholder="Skills, experience, qualifications needed..."><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="padding:14px 32px;font-size:15px;">Submit for Approval →</button>
      <a href="dashboard.php" class="btn" style="background:#e5e7eb;color:#333;">Cancel</a>
    </form>
  </div>
</div>

<?php require __DIR__ . '/_layout-bottom.php'; ?>
