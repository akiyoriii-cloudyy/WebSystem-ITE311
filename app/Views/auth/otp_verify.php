<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #198754, #157347);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 480px;">
    <div class="card p-4">
        <h3 class="text-center fw-bold">OTP Verification</h3>
        <p class="text-muted text-center mb-4">Enter the 6-digit code sent to your email</p>

        <!-- Validation errors -->
        <?php if(session()->has('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach(session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Success/Error messages -->
        <?php if(session()->has('success')): ?>
            <div class="alert alert-success"><?= session('success') ?></div>
        <?php endif; ?>
        <?php if(session()->has('warning')): ?>
            <div class="alert alert-warning"><?= session('warning') ?></div>
        <?php endif; ?>
        <?php if(session()->has('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        
        <!-- Display OTP if email failed -->
        <?php if(session()->has('display_otp')): ?>
            <div class="alert alert-info">
                <strong>Your OTP Code:</strong> 
                <span style="font-size: 1.5em; font-weight: bold; color: #198754; letter-spacing: 3px;">
                    <?= esc(session('display_otp')) ?>
                </span>
                <br><small>Enter this code below to continue</small>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('login') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">OTP Code</label>
                <input name="otp_code" type="text" class="form-control text-center" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required autofocus>
                <small class="form-text text-muted">Enter the 6-digit code from your email</small>
            </div>

            <button class="btn btn-success w-100" type="submit">Verify OTP</button>
        </form>

        <div class="text-center mt-3">
            <p class="mb-0">
                <a href="<?= site_url('login') ?>" onclick="if(confirm('Cancel OTP verification? You will need to login again.')) { return true; } return false;">Cancel</a>
            </p>
        </div>
    </div>
</div>
<script>
    // Auto-format OTP input
    document.querySelector('input[name="otp_code"]').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
</body>
</html>

