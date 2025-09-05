<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
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
        <h3 class="mb-2 text-center fw-bold">Create Account</h3>
        <p class="text-muted text-center mb-4">Join us today and get started</p>

        <?php if ($errors = session('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('register') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="<?= old('name') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" value="<?= old('email') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="user" <?= old('role')==='user'?'selected':'' ?>>User</option>
                    <option value="admin" <?= old('role')==='admin'?'selected':'' ?>>Admin</option>
                </select>
            </div>
            <button class="btn btn-primary w-100" type="submit">Register</button>
            <div class="text-center mt-3">
                <a href="<?= site_url('login') ?>">Already have an account? Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
