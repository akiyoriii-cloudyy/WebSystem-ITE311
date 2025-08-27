<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= esc($title) ?> | ITE311</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    nav a { margin-right: 12px; text-decoration: none; }
    body { font-family: Arial, Helvetica, sans-serif; padding: 24px; }
    .active { font-weight: bold; }
    form { margin-top: 16px; }
    label { display: block; margin-top: 8px; }
    input, textarea { width: 320px; max-width: 100%; padding: 8px; }
    button { margin-top: 12px; padding: 8px 14px; }
  </style>
</head>
<body>
  <nav>
    <a href="<?= site_url('/') ?>">Home</a>
    <a href="<?= site_url('about') ?>">About</a>
    <a href="<?= site_url('contact') ?>" class="active">Contact</a>
  </nav>

<?= $this->extend('template') ?>
<?= $this->section('content') ?>

<h1>Contact Us</h1>
<p>We'd love to hear from you! You can reach us at:</p>
<p><strong>markypadilla@gmail.com</strong></p>

<?= $this->endSection() ?>
