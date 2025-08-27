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
  </style>
</head>
<body>
  <nav>
    <a href="<?= site_url('/') ?>">Home</a>
    <a href="<?= site_url('about') ?>" class="active">About</a>
    <a href="<?= site_url('contact') ?>">Contact</a>
  </nav>

<?= $this->extend('template') ?>
<?= $this->section('content') ?>

<h1>About Us</h1>
<p>This page gives information about our website, its purpose, and features.</p>

<?= $this->endSection() ?>

