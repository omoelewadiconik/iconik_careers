<?php
session_start();

if (empty($_SESSION['application']['details'])) {
  header('Location: index.php');
  exit;
}

$errors = [];
$values = [
  'current_title' => '',
  'years' => '',
  'portfolio' => '',
  'skills' => '',
  'experience' => '',
  'availability' => '',
];

if (!empty($_SESSION['application']['experience']) && is_array($_SESSION['application']['experience'])) {
  $values = array_merge($values, $_SESSION['application']['experience']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($values as $key => $unused) {
    $values[$key] = trim((string)($_POST[$key] ?? ''));
  }

  if ($values['current_title'] === '') {
    $errors[] = 'Please enter your current or most recent title.';
  }
  if ($values['years'] === '') {
    $errors[] = 'Please tell us how many years of experience you have.';
  }
  if ($values['experience'] === '') {
    $errors[] = 'Please share a short summary of your experience.';
  }

  if (!$errors) {
    $_SESSION['application']['step'] = 2;
    $_SESSION['application']['experience'] = $values;
    header('Location: review.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply to ICONIK.CO — Step 2</title>
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
        <span class="top-note">Careers · Application · Step 2 of 3</span>
      </nav>
    </header>

    <div class="layout">
      <aside class="intro">
        <div class="eyebrow">Join the studio</div>
        <h1>Show us your work.</h1>
        <p class="lede">Add a snapshot of your experience so the team can understand how you work and where you want to grow.</p>

        <ol class="steps">
          <li class="done">
            <span class="step-num">1</span>
            <div class="step-copy">
              <strong>Your information</strong>
              <span>Contact details and CV upload</span>
            </div>
          </li>
          <li class="active">
            <span class="step-num">2</span>
            <div class="step-copy">
              <strong>Experience</strong>
              <span>Work history, skills, and links</span>
            </div>
          </li>
          <li>
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
          <h2>Experience</h2>
          <p>This helps us match you to the right role and team.</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert error">
            <?php echo htmlspecialchars(implode(' ', $errors)); ?>
          </div>
        <?php endif; ?>

        <form method="post" action="experience.php" novalidate>
          <div class="grid">
            <div class="field">
              <label for="current_title">Current or most recent title</label>
              <input id="current_title" name="current_title" type="text" required value="<?php echo htmlspecialchars($values['current_title']); ?>">
            </div>
            <div class="field">
              <label for="years">Years of experience</label>
              <input id="years" name="years" type="text" required placeholder="e.g. 4" value="<?php echo htmlspecialchars($values['years']); ?>">
            </div>
            <div class="field">
              <label for="portfolio">Portfolio or GitHub <em>(optional)</em></label>
              <input id="portfolio" name="portfolio" type="url" placeholder="https://" value="<?php echo htmlspecialchars($values['portfolio']); ?>">
            </div>
            <div class="field">
              <label for="availability">Availability <em>(optional)</em></label>
              <input id="availability" name="availability" type="text" placeholder="e.g. 4 weeks notice" value="<?php echo htmlspecialchars($values['availability']); ?>">
            </div>
            <div class="field full">
              <label for="skills">Key skills <em>(optional)</em></label>
              <input id="skills" name="skills" type="text" placeholder="Design systems, React, research..." value="<?php echo htmlspecialchars($values['skills']); ?>">
            </div>
            <div class="field full">
              <label for="experience">Experience summary</label>
              <textarea id="experience" name="experience" required placeholder="A short overview of relevant work, teams, and results"><?php echo htmlspecialchars($values['experience']); ?></textarea>
            </div>
          </div>

          <div class="actions split">
            <a class="btn ghost" href="index.php">← Back</a>
            <button class="btn" type="submit">Save and continue →</button>
          </div>
        </form>
      </section>
    </div>

    <footer>© <?php echo date('Y'); ?> ICONIK.CO · Careers application</footer>
  </div>
</body>
</html>
