<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My CI4 App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #0d6efd, #6610f2);
      background-attachment: fixed;
      color: #333;
    }

    /* NAVBAR */
    .navbar {
      background: rgba(33, 37, 41, 0.85) !important;
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .navbar-brand {
      font-weight: 700;
      font-size: 1.4rem;
      letter-spacing: 0.5px;
      color: #fff !important;
    }
    .nav-link {
      color: #ddd !important;
      transition: color 0.3s ease;
    }
    .nav-link.active, .nav-link:hover {
      color: #fff !important;
    }

    /* HERO */
    .hero {
      text-align: center;
      padding: 120px 20px;
      color: #fff;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: "";
      position: absolute;
      top: -50px;
      left: -50px;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle at center, rgba(255,255,255,0.15) 0%, transparent 70%);
      animation: pulse 8s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1);}
      50% { transform: scale(1.2);}
      100% { transform: scale(1);}
    }
    .hero h1 {
      font-size: 3.2rem;
      font-weight: 700;
      z-index: 2;
      position: relative;
    }
    .hero p {
      font-size: 1.25rem;
      opacity: 0.9;
      margin-top: 10px;
      z-index: 2;
      position: relative;
    }

    /* CONTENT */
    .content-wrapper {
      margin-top: -40px;
      padding: 40px;
      background: rgba(255,255,255,0.85);
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
    }

    /* FLASH MESSAGES */
    .alert {
      border-radius: 12px;
    }

    /* FOOTER */
    footer {
      margin-top: 60px;
      background: rgba(33, 37, 41, 0.95);
      color: #bbb;
      padding: 30px 0;
      text-align: center;
      font-size: 0.95rem;
    }
    footer a {
      color: #f8f9fa;
      text-decoration: none;
      margin: 0 12px;
      transition: color 0.3s ease;
    }
    footer a:hover {
      color: #0d6efd;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= base_url('/') ?>">My CI4 App</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= service('uri')->getSegment(1)==''?'active':'' ?>" href="<?= base_url('/') ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= service('uri')->getSegment(1)=='about'?'active':'' ?>" href="<?= base_url('about') ?>">About</a></li>
        <li class="nav-item"><a class="nav-link <?= service('uri')->getSegment(1)=='contact'?'active':'' ?>" href="<?= base_url('contact') ?>">Contact</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php $logged = session()->get('isLoggedIn'); ?>
        <?php if ($logged): ?>
          <li class="nav-item"><a class="nav-link <?= service('uri')->getSegment(1)=='dashboard'?'active':'' ?>" href="<?= base_url('dashboard') ?>">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('logout') ?>">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link <?= service('uri')->getSegment(1)=='login'?'active':'' ?>" href="<?= base_url('login') ?>">Login</a></li>
          <li class="nav-item"><a class="nav-link <?= service('uri')->getSegment(1)=='register'?'active':'' ?>" href="<?= base_url('register') ?>">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<div class="container">
  <div class="hero">
    <h1>Welcome to My CI4 App</h1>
    <p>Innovation • Reliability • Excellence</p>
  </div>
</div>

<!-- Flash Messages + Content -->
<div class="container">
  <div class="content-wrapper">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
  </div>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <p>&copy; <?= date('Y') ?> My CI4 App — All Rights Reserved.</p>
    <div>
      <a href="<?= base_url('privacy') ?>">Privacy Policy</a> | 
      <a href="<?= base_url('terms') ?>">Terms of Service</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
