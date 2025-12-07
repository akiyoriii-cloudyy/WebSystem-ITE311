<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Settings</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Password Change Section (All Users) -->
        <div class="col-md-8 offset-md-2 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white fw-bold">
                    🔒 Change Password
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Update your account password</p>
                    
                    <form id="changePasswordForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100" id="changePasswordBtn">
                            Change Password
                        </button>
                    </form>
                    
                    <div id="changePasswordAlert" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Function to get CSRF token from cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Function to get CSRF token
    function getCsrfToken() {
        const csrfTokenName = '<?= csrf_token() ?>';
        const csrfCookieName = '<?= config("Security")->cookieName ?>';
        return getCookie(csrfCookieName) || 
               $('meta[name="csrf-hash"]').attr('content') || 
               '<?= csrf_hash() ?>';
    }


    // Change Password Form
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#changePasswordBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Changing...');
        
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        
        // Client-side validation
        if (newPassword !== confirmPassword) {
            $btn.prop('disabled', false).html(originalText);
            let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                'New password and confirmation do not match' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            $('#changePasswordAlert').html(alertHtml);
            return;
        }
        
        const csrfToken = getCsrfToken();
        const csrfTokenName = '<?= csrf_token() ?>';
        
        $.ajax({
            url: '<?= base_url('change-password') ?>',
            type: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                current_password: $('#current_password').val(),
                new_password: newPassword,
                confirm_password: confirmPassword,
                [csrfTokenName]: csrfToken
            },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                
                // Update CSRF token
                if (response.csrf_hash) {
                    $('meta[name="csrf-hash"]').attr('content', response.csrf_hash);
                }
                
                let alertClass = response.status === 'success' ? 'alert-success' : 'alert-danger';
                let alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    response.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                
                $('#changePasswordAlert').html(alertHtml);
                
                if (response.status === 'success') {
                    // Reset form
                    $('#changePasswordForm')[0].reset();
                    
                    // Auto-dismiss after 3 seconds
                    setTimeout(function() {
                        $('.alert-success').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    // Auto-dismiss error after 5 seconds
                    setTimeout(function() {
                        $('.alert-danger').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    errorMessage +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                
                $('#changePasswordAlert').html(alertHtml);
                
                setTimeout(function() {
                    $('.alert-danger').fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        });
    });
});
</script>

<?= $this->include('template/footer') ?>

