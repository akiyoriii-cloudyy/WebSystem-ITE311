<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">User Management</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 offset-md-2 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white fw-bold">
                    👥 Create New User
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Create new users (Admin, Teacher, or Student)</p>
                    
                    <form id="createUserForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin (Protected)</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Auto-generated Password Display -->
                        <div class="mb-3">
                            <label class="form-label">Auto-Generated Password</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="generatedPassword" value="MarkyLMS12345" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()" title="Copy Password">
                                    📋 Copy
                                </button>
                            </div>
                            <small class="text-muted">Password is automatically generated. Users should change it after first login.</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="text" class="form-control" id="confirmPassword" value="MarkyLMS12345" readonly>
                            <small class="text-muted">Auto-filled for confirmation</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" id="createUserBtn">
                            Create User
                        </button>
                    </form>
                    
                    <div id="createUserAlert" class="mt-3"></div>
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

    // Auto-generated password
    const AUTO_PASSWORD = 'MarkyLMS12345';
    
    // Function to copy password to clipboard
    window.copyPassword = function() {
        const passwordField = document.getElementById('generatedPassword');
        passwordField.select();
        passwordField.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand('copy');
        
        // Show feedback
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '✓ Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    };

    // Create User Form
    $('#createUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#createUserBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Creating...');
        
        const csrfToken = getCsrfToken();
        const csrfTokenName = '<?= csrf_token() ?>';
        
        // Use auto-generated password
        const autoPassword = AUTO_PASSWORD;
        
        $.ajax({
            url: '<?= base_url('admin/users/create') ?>',
            type: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                name: $('#name').val(),
                email: $('#email').val(),
                password: autoPassword, // Auto-generated password
                role: $('#role').val(),
                status: $('#status').val(),
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
                
                $('#createUserAlert').html(alertHtml);
                
                if (response.status === 'success') {
                    // Reset form (but keep auto-generated password visible)
                    $('#name').val('');
                    $('#email').val('');
                    $('#role').val('');
                    $('#status').val('active');
                    $('#generatedPassword').val(AUTO_PASSWORD);
                    $('#confirmPassword').val(AUTO_PASSWORD);
                    
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
                
                $('#createUserAlert').html(alertHtml);
                
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

