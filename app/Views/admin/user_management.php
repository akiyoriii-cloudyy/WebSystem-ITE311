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
                    
                    <form id="createUserForm" method="POST" action="javascript:void(0);" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="throughly_token" id="throughly_token" value="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   required>
                            <small class="form-text text-muted">Enter a proper name. Security characters are not allowed.</small>
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
                        
                        <button type="button" class="btn btn-primary w-100" id="createUserBtn">
                            <i class="bi bi-person-plus"></i> Create User
                        </button>
                    </form>
                    
                    <div id="createUserAlert" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for jQuery and DOM to be ready
(function() {
    function initUserManagement() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            setTimeout(initUserManagement, 100);
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            console.log('=== USER MANAGEMENT INITIALIZED ===');
            console.log('jQuery version:', $.fn.jquery);
            console.log('Form found:', $('#createUserForm').length);
            console.log('Button found:', $('#createUserBtn').length);
            
            // Function to copy password to clipboard
            window.copyPassword = function() {
                var passwordField = document.getElementById('generatedPassword');
                passwordField.select();
                passwordField.setSelectionRange(0, 99999);
                document.execCommand('copy');
                
                var btn = event.target;
                var originalText = btn.innerHTML;
                btn.innerHTML = '✓ Copied!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            };
            
            // Prevent form default submission
            $('#createUserForm').on('submit', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Button click handler - main handler for creating users
            $('#createUserBtn').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('=== BUTTON CLICKED - STARTING USER CREATION ===');
                
                // Get form values
                var name = $('#name').val().trim();
                var email = $('#email').val().trim();
                var role = $('#role').val();
                var status = $('#status').val() || 'active';
                var $btn = $(this);
                var originalText = $btn.html();
                
                // Validation
                if (!name) {
                    $('#createUserAlert').html('<div class="alert alert-danger">Please enter a name.</div>');
                    $('#name').focus();
                    return;
                }
                
                if (!email) {
                    $('#createUserAlert').html('<div class="alert alert-danger">Please enter an email.</div>');
                    $('#email').focus();
                    return;
                }
                
                if (!role) {
                    $('#createUserAlert').html('<div class="alert alert-danger">Please select a role.</div>');
                    $('#role').focus();
                    return;
                }
                
                // Get CSRF token
                var csrfTokenName = '<?= csrf_token() ?>';
                var csrfToken = $('input[name="' + csrfTokenName + '"]').val() || $('meta[name="csrf-hash"]').attr('content') || '<?= csrf_hash() ?>';
                
                // Prepare data
                var formData = {
                    name: name,
                    email: email,
                    password: 'MarkyLMS12345',
                    role: role,
                    status: status
                };
                formData[csrfTokenName] = csrfToken;
                
                // Disable button
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Creating...');
                
                // Send AJAX request
                $.ajax({
                    url: '<?= base_url('admin/users/create') ?>',
                    type: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);
                        $btn.prop('disabled', false).html(originalText);
                        
                        var alertClass = response.status === 'success' ? 'alert-success' : 'alert-danger';
                        $('#createUserAlert').html('<div class="alert ' + alertClass + '">' + response.message + '</div>');
                        
                        if (response.status === 'success') {
                            $('#name').val('');
                            $('#email').val('');
                            $('#role').val('');
                            $('#status').val('active');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        $btn.prop('disabled', false).html(originalText);
                        var errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                        $('#createUserAlert').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    }
                });
            });
            
            console.log('Button handler attached successfully');
        });
    }
    
    // Start initialization
    initUserManagement();
})();
</script>

<?= $this->include('template/footer') ?>

