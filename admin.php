<?php
session_start();
require_once __DIR__ . '/storage.php';

const ADMIN_PASSWORD = 'iconik-admin';

$notice = '';
$errors = [];

if (isset($_GET['logout'])) {
  unset($_SESSION['admin']);
  header('Location: admin.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && !isset($_POST['reference'])) {
  if (hash_equals(ADMIN_PASSWORD, (string)$_POST['password'])) {
    $_SESSION['admin'] = true;
    header('Location: admin.php');
    exit;
  }
  $errors[] = 'That password is not correct.';
}

if (!empty($_SESSION['admin']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reference'], $_POST['status'])) {
  $reference = strtoupper(trim((string)$_POST['reference']));
  $status = (string)$_POST['status'];
  $allowed = ['pending', 'reviewing', 'hired', 'rejected'];
  $app = find_application_by_reference($reference);
  if (!$app || !in_array($status, $allowed, true)) {
    $errors[] = 'Could not update that application.';
  } else {
    $app['status'] = $status;
    $app['updated_at'] = date('c');
    upsert_application($app);
    $notice = 'Updated ' . $reference . ' to ' . status_label($status) . '.';
  }
}

$apps = !empty($_SESSION['admin']) ? load_applications() : [];
usort($apps, static function ($a, $b) {
  return strcmp((string)($b['submitted_at'] ?? ''), (string)($a['submitted_at'] ?? ''));
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hiring desk — ICONIK.CO</title>
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
        <a href="status.php">Applicant status</a>
        <?php if (!empty($_SESSION['admin'])): ?>
          <a href="admin.php?logout=1">Sign out</a>
        <?php endif; ?>
        <span class="top-note">Careers · Hiring desk</span>
      </nav>
    </header>

    <div class="layout single">
      <section class="card">
        <div class="card-head">
          <h2>Hiring desk</h2>
          <p>Mark applicants as in review, hired, or not selected. They will see the update on the status page.</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert error"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
        <?php endif; ?>
        <?php if ($notice): ?>
          <div class="alert ok"><?php echo htmlspecialchars($notice); ?></div>
        <?php endif; ?>

        <?php if (empty($_SESSION['admin'])): ?>
          <form method="post" action="admin.php">
            <div class="grid">
              <div class="field full">
                <label for="password">Team password</label>
                <input id="password" name="password" type="password" required>
                <p class="hint">Default password is <code>iconik-admin</code>. Change it in admin.php before going live.</p>
              </div>
            </div>
            <div class="actions">
              <button class="btn" type="submit">Open hiring desk</button>
            </div>
          </form>
        <?php elseif (!$apps): ?>
          <div class="alert muted">No applications yet. When someone submits the form they will appear here.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="apps">
              <thead>
                <tr>
                  <th>Applicant</th>
                  <th>Role</th>
                  <th>Reference</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($apps as $app): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars(($app['details']['first_name'] ?? '') . ' ' . ($app['details']['last_name'] ?? '')); ?></strong>
                      <span class="muted-line"><?php echo htmlspecialchars($app['details']['email'] ?? ''); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($app['details']['role'] ?? ''); ?></td>
                    <td><code><?php echo htmlspecialchars($app['reference'] ?? ''); ?></code></td>
                    <td><span class="status-pill compact <?php echo htmlspecialchars($app['status'] ?? ''); ?>"><?php echo htmlspecialchars(status_label($app['status'] ?? '')); ?></span></td>
                    <td>
                      <form method="post" action="admin.php" class="inline-form">
                        <input type="hidden" name="reference" value="<?php echo htmlspecialchars($app['reference'] ?? ''); ?>">
                        <select name="status">
                          <?php foreach (['pending' => 'Received', 'reviewing' => 'In review', 'hired' => 'Hired', 'rejected' => 'Not selected'] as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($app['status'] ?? '') === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button class="btn small" type="submit">Update</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <footer>© <?php echo date('Y'); ?> ICONIK.CO · Careers application</footer>
  </div>
</body>
</html>
