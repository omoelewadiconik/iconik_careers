<?php
session_start();
require_once __DIR__ . '/storage.php';

$errors = [];
$application = null;
$email = trim((string)($_GET['email'] ?? $_POST['email'] ?? ''));
$reference = strtoupper(trim((string)($_GET['reference'] ?? $_POST['reference'] ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($email !== '' && $reference !== '')) {
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter the email address you used to apply.';
  }
  if ($reference === '') {
    $errors[] = 'Please enter your application reference number.';
  }

  if (!$errors) {
    $application = find_application($reference, $email);
    if (!$application) {
      $errors[] = 'No application matched that email and reference number. Check both and try again.';
    }
  }
}

$status = $application['status'] ?? '';
$firstName = $application['details']['first_name'] ?? '';
$roleLabels = [
  'product-designer' => 'Product Designer',
  'frontend-engineer' => 'Frontend Engineer',
  'backend-engineer' => 'Backend Engineer',
  'full-stack-engineer' => 'Full-Stack Engineer',
  'product-manager' => 'Product Manager',
  'marketing' => 'Marketing Specialist',
  'operations' => 'Operations',
  'other' => 'Other / General application',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application status — ICONIK.CO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="page">
    <header class="topbar">
      <a class="brand" href="index.php">
        <div class="mark">IK</div>
        <div class="brand-name">ICONIK<span>.CO</span></div>
      </a>
      <nav class="top-nav">
        <a href="index.php">Apply</a>
        <span class="top-note">Careers · Applicant status</span>
      </nav>
    </header>

    <div class="layout">
      <aside class="intro">
        <div class="eyebrow">Applicant portal</div>
        <h1>Check your hiring status.</h1>
        <p class="lede">Enter the email from your application and the reference number you received after submitting. You will see whether your file is still in review, you have been hired, or you were not selected.</p>

        <ul class="legend">
          <li><span class="dot pending"></span> Received — we have your application</li>
          <li><span class="dot reviewing"></span> In review — the team is reading your file</li>
          <li><span class="dot hired"></span> Hired — an offer is being prepared</li>
          <li><span class="dot rejected"></span> Not selected — we will not move forward this time</li>
        </ul>
      </aside>

      <section class="card">
        <div class="card-head">
          <h2>Look up an application</h2>
          <p>Use the exact email and reference from your confirmation page.</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert error">
            <?php echo htmlspecialchars(implode(' ', $errors)); ?>
          </div>
        <?php endif; ?>

        <form method="post" action="status.php" novalidate>
          <div class="grid">
            <div class="field">
              <label for="email">Email used to apply</label>
              <input id="email" name="email" type="email" required placeholder="you@email.com" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="field">
              <label for="reference">Reference number</label>
              <input id="reference" name="reference" type="text" required placeholder="IK-20260917-A1B2C3" value="<?php echo htmlspecialchars($reference); ?>">
            </div>
          </div>
          <div class="actions">
            <button class="btn" type="submit">View status</button>
          </div>
        </form>

        <?php if ($application && empty($errors)): ?>
          <div class="status-result">
            <div class="status-hero <?php echo htmlspecialchars($status); ?>">
              <span class="status-pill"><?php echo htmlspecialchars(status_label($status)); ?></span>
              <p class="reference-label">Reference <?php echo htmlspecialchars($application['reference']); ?></p>
              <h3><?php echo htmlspecialchars(($application['details']['first_name'] ?? '') . ' ' . ($application['details']['last_name'] ?? '')); ?></h3>
              <p><?php echo htmlspecialchars(status_message($status, $firstName)); ?></p>
            </div>

            <dl class="review-list">
              <div>
                <dt>Role</dt>
                <dd><?php echo htmlspecialchars($roleLabels[$application['details']['role'] ?? ''] ?? ($application['details']['role'] ?? '—')); ?></dd>
              </div>
              <div>
                <dt>Submitted</dt>
                <dd><?php echo htmlspecialchars(date('j F Y', strtotime((string)$application['submitted_at']))); ?></dd>
              </div>
              <div>
                <dt>Last update</dt>
                <dd><?php echo htmlspecialchars(date('j F Y', strtotime((string)($application['updated_at'] ?? $application['submitted_at'])))); ?></dd>
              </div>
              <div>
                <dt>Email</dt>
                <dd><?php echo htmlspecialchars($application['details']['email'] ?? ''); ?></dd>
              </div>
            </dl>

            <?php if ($status === 'hired'): ?>
              <div class="alert ok">Watch your inbox. Offer details and onboarding steps will come from the ICONIK.CO hiring team.</div>
            <?php elseif ($status === 'rejected'): ?>
              <div class="alert muted">You are welcome to apply again in the future when a new role is a better fit.</div>
            <?php else: ?>
              <div class="alert muted">No decision has been posted yet. Check this page again in a few days.</div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <footer>© <?php echo date('Y'); ?> ICONIK.CO · Careers application</footer>
  </div>
</body>
</html>
