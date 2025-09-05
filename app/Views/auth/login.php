<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Log In</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #198754, #157347);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 480px;">
    <div class="card p-4">
        <h3 class="mb-2 text-center fw-bold">Welcome Back</h3>
        <p class="text-muted text-center mb-4">Log in to continue</p>

        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('login') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" value="<?= old('email') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" required>
            </div>
            <button class="btn btn-success w-100" type="submit">Login</button>
            <div class="text-center mt-3">
                <a href="<?= site_url('register') ?>">Don’t have an account? Register</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
