<?php
session_start();
require_once __DIR__ . '/storage.php';

$errors = [];
$values = [
  'first_name' => '',
  'last_name' => '',
  'email' => '',
  'phone' => '',
  'city' => '',
  'country' => '',
  'role' => '',
  'linkedin' => '',
  'source' => '',
  'intro' => '',
];

if (!empty($_SESSION['application']['details']) && is_array($_SESSION['application']['details'])) {
  $values = array_merge($values, $_SESSION['application']['details']);
}

$existingCv = $_SESSION['application']['cv_original'] ?? '';

$roles = [
  '' => 'Select a role',
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
  foreach ($values as $key => $unused) {
    $values[$key] = trim((string)($_POST[$key] ?? ''));
  }

  if ($values['first_name'] === '') {
    $errors[] = 'Please enter your first name.';
  }
  if ($values['last_name'] === '') {
    $errors[] = 'Please enter your last name.';
  }
  if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
  }
  if ($values['phone'] === '') {
    $errors[] = 'Please enter a phone number.';
  }
  if ($values['role'] === '' || !isset($roles[$values['role']])) {
    $errors[] = 'Please choose the role you are applying for.';
  }

  $cvPath = $_SESSION['application']['cv'] ?? '';
  $cvOriginal = $existingCv;
  $newUpload = isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE;

  if ($newUpload) {
    if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
      $errors[] = 'The CV could not be uploaded. Please try again.';
    } else {
      $file = $_FILES['cv'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($file['tmp_name']);
      $allowedMime = ['application/pdf', 'application/x-pdf'];

      if ($ext !== 'pdf' || !in_array($mime, $allowedMime, true)) {
        $errors[] = 'Your CV must be a PDF file.';
      } elseif ($file['size'] > 8 * 1024 * 1024) {
        $errors[] = 'Your CV must be 8 MB or smaller.';
      } else {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0755, true);
        }
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $fullPath = $uploadDir . '/' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '_' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
          $errors[] = 'We could not save your CV. Please try again.';
        } else {
          $cvPath = basename($fullPath);
          $cvOriginal = $file['name'];
        }
      }
    }
  } elseif ($cvPath === '') {
    $errors[] = 'Please upload your CV as a PDF file.';
  }

  if (!$errors) {
    $previous = $_SESSION['application'] ?? [];
    $_SESSION['application'] = array_merge($previous, [
      'step' => 1,
      'details' => $values,
      'cv' => $cvPath,
      'cv_original' => $cvOriginal,
    ]);
    header('Location: experience.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply to ICONIK.CO — Step 1</title>
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
        <span class="top-note">Careers · Application · Step 1 of 3</span>
      </nav>
    </header>

    <div class="layout">
      <aside class="intro">
        <div class="eyebrow">Join the studio</div>
        <h1>Tell us who you are.</h1>
        <p class="lede">Start your ICONIK.CO application with your details and a PDF of your CV. You can add experience and a short pitch in the next steps.</p>

        <ol class="steps">
          <li class="active">
            <span class="step-num">1</span>
            <div class="step-copy">
              <strong>Your information</strong>
              <span>Contact details and CV upload</span>
            </div>
          </li>
          <li>
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
          <h2>Applicant details</h2>
          <p>All fields marked required must be completed.</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert error">
            <?php echo htmlspecialchars(implode(' ', $errors)); ?>
          </div>
        <?php endif; ?>

        <form method="post" action="index.php" enctype="multipart/form-data" novalidate>
          <div class="grid">
            <div class="field">
              <label for="first_name">First name</label>
              <input id="first_name" name="first_name" type="text" required value="<?php echo htmlspecialchars($values['first_name']); ?>">
            </div>
            <div class="field">
              <label for="last_name">Last name</label>
              <input id="last_name" name="last_name" type="text" required value="<?php echo htmlspecialchars($values['last_name']); ?>">
            </div>
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" required placeholder="you@email.com" value="<?php echo htmlspecialchars($values['email']); ?>">
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" required placeholder="+1 555 000 0000" value="<?php echo htmlspecialchars($values['phone']); ?>">
            </div>
            <div class="field">
              <label for="city">City <em>(optional)</em></label>
              <input id="city" name="city" type="text" value="<?php echo htmlspecialchars($values['city']); ?>">
            </div>
            <div class="field">
              <label for="country">Country <em>(optional)</em></label>
              <input id="country" name="country" type="text" value="<?php echo htmlspecialchars($values['country']); ?>">
            </div>
            <div class="field full">
              <label for="role">Role you are applying for</label>
              <select id="role" name="role" required>
                <?php foreach ($roles as $value => $label): ?>
                  <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $values['role'] === $value ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="linkedin">LinkedIn <em>(optional)</em></label>
              <input id="linkedin" name="linkedin" type="url" placeholder="https://linkedin.com/in/you" value="<?php echo htmlspecialchars($values['linkedin']); ?>">
            </div>
            <div class="field">
              <label for="source">How did you hear about us? <em>(optional)</em></label>
              <select id="source" name="source">
                <?php
                  $sources = ['' => 'Select one', 'website' => 'ICONIK.CO website', 'linkedin' => 'LinkedIn', 'referral' => 'Employee referral', 'other' => 'Other'];
                  foreach ($sources as $value => $label):
                ?>
                  <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $values['source'] === $value ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field full">
              <label for="intro">Short introduction <em>(optional)</em></label>
              <textarea id="intro" name="intro" placeholder="A few lines about why you want to join ICONIK.CO"><?php echo htmlspecialchars($values['intro']); ?></textarea>
            </div>
            <div class="field full">
              <label>Curriculum vitae (PDF)</label>
              <label class="upload" id="dropzone" for="cv">
                <input id="cv" name="cv" type="file" accept="application/pdf,.pdf" <?php echo $existingCv ? '' : 'required'; ?>>
                <div class="upload-icon">PDF</div>
                <strong>Drop your CV here or click to browse</strong>
                <span>PDF only · max 8 MB<?php echo $existingCv ? ' · previously uploaded: ' . htmlspecialchars($existingCv) : ''; ?></span>
                <div class="file-chip" id="fileChip"><b id="fileName"></b></div>
              </label>
              <p class="hint">Upload a PDF of your CV. We do not accept Word or image files on this step.</p>
            </div>
          </div>

          <div class="actions">
            <p class="privacy">By continuing you agree that ICONIK.CO may use this information to review your application. After you submit, you can check your hiring status anytime.</p>
            <button class="btn" type="submit">Save and continue →</button>
          </div>
        </form>
      </section>
    </div>

    <footer>© <?php echo date('Y'); ?> ICONIK.CO · Careers application</footer>
  </div>

  <script src="script.js" defer></script>
</body>
</html>
