<?php
session_start();
require_once __DIR__ . '/storage.php';

if (empty($_SESSION['application']['details']) || empty($_SESSION['application']['experience'])) {
  header('Location: index.php');
  exit;
}

$details = $_SESSION['application']['details'];
$experience = $_SESSION['application']['experience'];
$cvOriginal = $_SESSION['application']['cv_original'] ?? '';
$errors = [];

$roles = [
  'product-designer' => 'Product Designer',
  'frontend-engineer' => 'Frontend Engineer',
  'backend-engineer' => 'Backend Engineer',
  'full-stack-engineer' => 'Full-Stack Engineer',
  'product-manager' => 'Product Manager',
  'marketing' => 'Marketing Specialist',
  'operations' => 'Operations',
  'other' => 'Other / General application',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $reference = generate_reference();
  $application = [
    'reference' => $reference,
    'status' => 'pending',
    'submitted_at' => date('c'),
    'updated_at' => date('c'),
    'details' => $details,
    'experience' => $experience,
    'cv' => $_SESSION['application']['cv'] ?? '',
    'cv_original' => $cvOriginal,
  ];

  if (!upsert_application($application)) {
    $errors[] = 'We could not save your application. Please try again.';
  } else {
    $_SESSION['submitted'] = [
      'reference' => $reference,
      'email' => $details['email'],
      'first_name' => $details['first_name'],
    ];
    unset($_SESSION['application']);
    header('Location: thanks.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply to ICONIK.CO — Step 3</title>
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
        <a href="status.php">Check application status</a>
        <span class="top-note">Careers · Application · Step 3 of 3</span>
      </nav>
    </header>

    <div class="layout">
      <aside class="intro">
        <div class="eyebrow">Join the studio</div>
        <h1>Review and send.</h1>
        <p class="lede">Check your details. After you submit you will receive a reference number to track whether you have been hired or not selected.</p>

        <ol class="steps">
          <li class="done">
            <span class="step-num">1</span>
            <div class="step-copy">
              <strong>Your information</strong>
              <span>Contact details and CV upload</span>
            </div>
          </li>
          <li class="done">
            <span class="step-num">2</span>
            <div class="step-copy">
              <strong>Experience</strong>
              <span>Work history, skills, and links</span>
            </div>
          </li>
          <li class="active">
            <span class="step-num">3</span>
            <div class="step-copy">
              <strong>Review & send</strong>
              <span>Check everything before submitting</span>
            </div>
          </li>
        </ol>
      </aside>

      <section class="card">
        <div class="card-head">
          <h2>Your application</h2>
          <p>Submit when everything looks correct.</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert error">
            <?php echo htmlspecialchars(implode(' ', $errors)); ?>
          </div>
        <?php endif; ?>

        <div class="review-block">
          <h3>Details</h3>
          <dl class="review-list">
            <div><dt>Name</dt><dd><?php echo htmlspecialchars($details['first_name'] . ' ' . $details['last_name']); ?></dd></div>
            <div><dt>Email</dt><dd><?php echo htmlspecialchars($details['email']); ?></dd></div>
            <div><dt>Phone</dt><dd><?php echo htmlspecialchars($details['phone']); ?></dd></div>
            <div><dt>Role</dt><dd><?php echo htmlspecialchars($roles[$details['role']] ?? $details['role']); ?></dd></div>
            <div><dt>Location</dt><dd><?php echo htmlspecialchars(trim($details['city'] . ', ' . $details['country'], ' ,') ?: '—'); ?></dd></div>
            <div><dt>CV</dt><dd><?php echo htmlspecialchars($cvOriginal ?: 'Uploaded'); ?></dd></div>
          </dl>
        </div>

        <div class="review-block">
          <h3>Experience</h3>
          <dl class="review-list">
            <div><dt>Title</dt><dd><?php echo htmlspecialchars($experience['current_title']); ?></dd></div>
            <div><dt>Years</dt><dd><?php echo htmlspecialchars($experience['years']); ?></dd></div>
            <div><dt>Skills</dt><dd><?php echo htmlspecialchars($experience['skills'] ?: '—'); ?></dd></div>
            <div><dt>Summary</dt><dd><?php echo nl2br(htmlspecialchars($experience['experience'])); ?></dd></div>
          </dl>
        </div>

        <form method="post" action="review.php">
          <div class="actions split">
            <a class="btn ghost" href="experience.php">← Back</a>
            <button class="btn" type="submit">Submit application</button>
          </div>
        </form>
      </section>
    </div>

    <footer>© <?php echo date('Y'); ?> ICONIK.CO · Careers application</footer>
  </div>
</body>
</html>
