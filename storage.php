<?php

function applications_path(): string
{
  $dir = __DIR__ . '/data';
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
  }
  return $dir . '/applications.json';
}

function load_applications(): array
{
  $path = applications_path();
  if (!is_file($path)) {
    return [];
  }
  $raw = file_get_contents($path);
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function save_applications(array $apps): bool
{
  $path = applications_path();
  $json = json_encode(array_values($apps), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  return file_put_contents($path, $json, LOCK_EX) !== false;
}

function generate_reference(): string
{
  return 'IK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function find_application(string $reference, string $email): ?array
{
  $reference = strtoupper(trim($reference));
  $email = strtolower(trim($email));
  foreach (load_applications() as $app) {
    $appRef = strtoupper((string)($app['reference'] ?? ''));
    $appEmail = strtolower((string)($app['details']['email'] ?? ''));
    if ($appRef === $reference && $appEmail === $email) {
      return $app;
    }
  }
  return null;
}

function find_application_by_reference(string $reference): ?array
{
  $reference = strtoupper(trim($reference));
  foreach (load_applications() as $app) {
    if (strtoupper((string)($app['reference'] ?? '')) === $reference) {
      return $app;
    }
  }
  return null;
}

function upsert_application(array $application): bool
{
  $apps = load_applications();
  $found = false;
  foreach ($apps as $i => $app) {
    if (($app['reference'] ?? '') === ($application['reference'] ?? '')) {
      $apps[$i] = $application;
      $found = true;
      break;
    }
  }
  if (!$found) {
    $apps[] = $application;
  }
  return save_applications($apps);
}

function status_label(string $status): string
{
  return [
    'pending' => 'Received',
    'reviewing' => 'In review',
    'hired' => 'Hired',
    'rejected' => 'Not selected',
  ][$status] ?? 'Unknown';
}

function status_message(string $status, string $firstName = ''): string
{
  $name = $firstName !== '' ? $firstName : 'there';
  return [
    'pending' => "Hi {$name}, we have received your application and it is in our queue. Our team will review it shortly.",
    'reviewing' => "Hi {$name}, your application is currently being reviewed by the ICONIK.CO team. We will update this page when a decision is made.",
    'hired' => "Congratulations {$name} — you have been hired. A member of the ICONIK.CO team will contact you at the email on your application with next steps.",
    'rejected' => "Hi {$name}, thank you for applying. After careful review we will not be moving forward with your application this time. We appreciate the time you spent and wish you the very best.",
  ][$status] ?? 'We could not determine the status of this application.';
}
