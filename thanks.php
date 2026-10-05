<?php
session_start();

$submitted = $_SESSION['submitted'] ?? null;
if (!$submitted) {
  header('Location: status.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application received — ICONIK.CO</title>
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
        <span class="top-note">Careers · Submitted</span>
      </nav>
    </header>

    <div class="layout single">
      <section class="card">
        <div class="card-head">
          <div class="eyebrow">Thank you</div>
          <h2>Your application is in.</h2>
          <p>Save your reference number. You will need it, plus the email you applied with, to check whether you have been hired or not selected.</p>
        </div>

        <div class="status-hero pending">
          <span class="status-pill">Received</span>
          <p class="reference-label">Your reference number</p>
          <p class="reference"><?php echo htmlspecialchars($submitted['reference']); ?></p>
          <p class="lede">Keep this number somewhere safe. Status updates appear on the applicant status page — we do not send a decision until the team has reviewed your file.</p>
        </div>

        <div class="actions split">
          <a class="btn ghost" href="index.php">Submit another application</a>
          <a class="btn" href="status.php?email=<?php echo urlencode($submitted['email']); ?>&reference=<?php echo urlencode($submitted['reference']); ?>">Check my status</a>
        </div>
      </section>
    </div>

    <footer>© <?php echo date('Y'); ?> ICONIK.CO · Careers application</footer>
  </div>
</body>
</html>
