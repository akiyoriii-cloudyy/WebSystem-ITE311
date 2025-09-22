<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Inter', sans-serif; }
        .sidebar {
            height: 100vh; background: #212529; color: #fff; padding: 20px;
            position: fixed; top: 0; left: 0; width: 240px;
        }
        .sidebar h4 { font-weight: 700; margin-bottom: 30px; }
        .sidebar a {
            display: block; color: #adb5bd; padding: 10px;
            border-radius: 8px; text-decoration: none; margin-bottom: 8px;
        }
        .sidebar a:hover, .sidebar a.active { background: #0d6efd; color: #fff; }
        .content { margin-left: 260px; padding: 40px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>My CI4 App</h4>
    <a href="<?= site_url($dashboard_url ?? '#') ?>" class="active">🏠 Dashboard</a>
    <a href="<?= site_url('logout') ?>">🚪 Logout</a>
</div>

<div class="content">
    <h2 class="fw-bold">Welcome, <?= esc($user_name) ?> 🎉</h2>
    <p class="text-muted">Your role: <strong><?= esc($user_role) ?></strong></p>

    <?php if(session()->has('success')): ?>
        <div class="alert alert-success"><?= esc(session('success')) ?></div>
    <?php endif; ?>
