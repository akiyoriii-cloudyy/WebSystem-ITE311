<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Manage Users</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header fw-bold">All Users</div>
        <div class="card-body">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Current Role</th>
                                <th>Status</th>
                                <th>Change Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $u): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($u['name']) ?></td>
                                    <td><?= esc($u['email']) ?></td>
                                    <td>
                                        <strong><?= ucfirst(esc($u['role'])) ?></strong>
                                        <?php if (strtolower($u['role']) === 'admin'): ?>
                                            <span class="badge bg-danger ms-2">Protected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $userStatus = $u['status'] ?? 'active';
                                        if ($userStatus === 'deleted'): 
                                        ?>
                                            <span class="badge bg-danger">Deleted</span>
                                        <?php elseif ($userStatus === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst(esc($userStatus)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $userStatus = $u['status'] ?? 'active';
                                        if (session()->get('user_id') != $u['id'] && strtolower($u['role']) !== 'admin' && $userStatus !== 'deleted'): 
                                        ?>
                                            <select class="form-select form-select-sm role-select" 
                                                    data-user-id="<?= $u['id'] ?>" 
                                                    data-current-role="<?= esc($u['role']) ?>">
                                                <option value="teacher" <?= $u['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                                <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                            </select>
                                        <?php elseif (strtolower($u['role']) === 'admin'): ?>
                                            <span class="badge bg-danger">Admin (Protected)</span>
                                        <?php elseif ($userStatus === 'deleted'): ?>
                                            <span class="badge bg-secondary">Deleted</span>
                                        <?php else: ?>
                                            <em class="text-muted">You</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (session()->get('user_id') != $u['id']): ?>
                                            <div class="btn-group" role="group">
                                                <!-- Edit Button -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary edit-user-btn" 
                                                        data-user-id="<?= $u['id'] ?>"
                                                        data-user-role="<?= esc($u['role']) ?>"
                                                        data-department-id="<?= esc($u['department_id'] ?? '') ?>"
                                                        data-program-id="<?= esc($u['program_id'] ?? '') ?>"
                                                        data-student-id="<?= esc($u['student_id'] ?? '') ?>"
                                                        title="Edit User"
                                                        style="cursor: pointer; z-index: 1; position: relative;">
                                                    Edit
                                                </button>
                                                
                                                <?php 
                                                $userStatus = $u['status'] ?? 'active';
                                                if ($userStatus === 'deleted'): 
                                                    // Show Restore button for deleted users
                                                ?>
                                                    <a href="<?= base_url('admin/users/restore/' . $u['id']) ?>" 
                                                       class="btn btn-sm btn-success" 
                                                       onclick="return confirm('Are you sure you want to restore this user? The user will be able to access the system again.');"
                                                       title="Restore User">
                                                        Restore
                                                    </a>
                                                <?php elseif (strtolower($u['role']) !== 'admin'): 
                                                    // Show Delete button for active non-admin users
                                                ?>
                                                    <a href="<?= base_url('admin/users/delete/' . $u['id']) ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to remove this user from the admin view? The user data will be preserved in the database but hidden from management.');"
                                                       title="Remove User from Admin View">
                                                        Delete
                                                    </a>
                                                <?php else: 
                                                    // Admin users are protected
                                                ?>
                                                    <span class="badge bg-danger">Protected</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                        <?php else: ?>
                                            <em class="text-muted">—</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Single Reusable Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="throughly_token" id="edit_throughly_token" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_name" 
                               name="name" 
                               required
                               autocomplete="off"
                               spellcheck="false">
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="edit_email" 
                               name="email" 
                               required
                               autocomplete="off"
                               spellcheck="false">
                    </div>
                    
                    <!-- Department and Program (for students only) -->
                    <div id="editStudentFields" style="display: none;">
                        <div class="mb-3">
                            <label for="edit_department_id" class="form-label">Department</label>
                            <select class="form-select" id="edit_department_id" name="department_id">
                                <option value="">-- Select Department (Optional) --</option>
                                <?php if (!empty($departments ?? [])): ?>
                                    <?php foreach ($departments ?? [] as $dept): ?>
                                        <option value="<?= esc($dept['id']) ?>">
                                            <?= esc($dept['department_code']) ?> - <?= esc($dept['department_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_program_id" class="form-label">Program</label>
                            <select class="form-select" id="edit_program_id" name="program_id">
                                <option value="">-- Select Program (Optional) --</option>
                                <?php if (!empty($programs ?? [])): ?>
                                    <?php foreach ($programs ?? [] as $prog): ?>
                                        <option value="<?= esc($prog['id']) ?>" data-department-id="<?= esc($prog['department_id'] ?? '') ?>">
                                            <?= esc($prog['program_code']) ?> - <?= esc($prog['program_name']) ?>
                                            <?php if (!empty($prog['department_name'])): ?>
                                                (<?= esc($prog['department_code'] ?? '') ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_student_id" class="form-label">Student ID</label>
                            <input type="text" class="form-control" id="edit_student_id" name="student_id" 
                                   placeholder="e.g., 2024-00123" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Prevent modal and form blinking/flickering */
.modal[id^="editUserModal"] {
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    transform: translateZ(0);
}

.modal[id^="editUserModal"] .modal-content {
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    transform: translateZ(0);
}

.modal[id^="editUserModal"] .edit-input {
    transition: none !important;
}

.modal[id^="editUserModal"] .form-control:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.modal[id^="editUserModal"] .form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3cpath d='m10 2-8 8M2 2l8 8'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
</style>

<?= $this->include('template/footer') ?>

<!-- Main Script - Must run after jQuery is loaded (in footer) -->
<script>
// Wait for jQuery and DOM to be ready
(function() {
    function initManageUsers() {
        // Ensure jQuery is available
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        // Use jQuery ready to ensure DOM is loaded
        jQuery(document).ready(function($) {
            console.log('Manage Users script initialized');
            
            // Test if edit buttons exist
            const editButtons = $('.edit-user-btn');
            console.log('Found', editButtons.length, 'edit buttons');
            if (editButtons.length === 0) {
                console.warn('No edit buttons found on page');
            }
            
            const roleRequests = {};

            // Role change handler - supports rapid changes by aborting in-flight requests per user
            $(document).on('change', '.role-select', function() {
                const userId = $(this).data('user-id');
                const newRole = $(this).val();
                const selectElement = $(this);
                const currentRole = selectElement.data('current-role');

                if (newRole === currentRole) { return; }

                // Abort previous pending request for this user
                if (roleRequests[userId] && roleRequests[userId].readyState !== 4) {
                    try { roleRequests[userId].abort(); } catch (e) {}
                }

                selectElement.prop('disabled', true);

                if (!confirm('Change this user\'s role to ' + newRole.charAt(0).toUpperCase() + newRole.slice(1) + '?')) {
                    selectElement.val(currentRole);
                    selectElement.prop('disabled', false);
                    return;
                }

                const csrfTokenName = '<?= csrf_token() ?>';
                const csrfHeaderName = '<?= csrf_header() ?>';
                const csrfCookieName = '<?= config("Security")->cookieName ?>';

                function getCookie(name) {
                    const value = `; ${document.cookie}`;
                    const parts = value.split(`; ${name}=`);
                    if (parts.length === 2) return parts.pop().split(';').shift();
                    return null;
                }

                let csrfToken = getCookie(csrfCookieName) || 
                               $('meta[name="' + csrfTokenName + '"]').attr('content') || 
                               '<?= csrf_hash() ?>';

                const xhr = $.ajax({
                    url: '<?= base_url('admin/users/update-role') ?>',
                    type: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        [csrfHeaderName]: csrfToken
                    },
                    data: {
                        id: userId,
                        role: newRole,
                        [csrfTokenName]: csrfToken
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (roleRequests[userId] !== xhr) return;
                        selectElement.prop('disabled', false);

                        if (response.status === 'success') {
                            if (response.csrf_token) {
                                $('meta[name="' + csrfTokenName + '"]').attr('content', response.csrf_token);
                            }

                            const roleCell = selectElement.closest('tr').find('td:eq(3) strong');
                            const capitalizedRole = newRole.charAt(0).toUpperCase() + newRole.slice(1);
                            roleCell.text(capitalizedRole);

                            selectElement.data('current-role', newRole);

                            $('.alert-success, .alert-danger').remove();
                            let alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            $('.container').first().prepend(alertHtml);

                            setTimeout(function() {
                                $('.alert-success').fadeOut(function() { $(this).remove(); });
                            }, 3000);
                        } else {
                            $('.alert-success, .alert-danger').remove();
                            let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                (response.message || 'Error updating role') +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            $('.container').first().prepend(alertHtml);

                            selectElement.val(currentRole);

                            setTimeout(function() {
                                $('.alert-danger').fadeOut(function() { $(this).remove(); });
                            }, 5000);
                        }
                    },
                    error: function(xhr2, status, error) {
                        if (status === 'abort') { return; }
                        if (roleRequests[userId] !== xhr) return;
                        selectElement.prop('disabled', false);

                        let errorMessage = 'An error occurred while updating the role.';

                        if (xhr2.status === 403 || (xhr2.responseText && xhr2.responseText.includes('not allowed'))) {
                            errorMessage = 'CSRF token expired. Please refresh the page and try again.';
                            setTimeout(function() {
                                if (confirm('Your session token has expired. Would you like to refresh the page?')) {
                                    window.location.reload();
                                }
                            }, 2000);
                        } else if (xhr2.responseJSON && xhr2.responseJSON.message) {
                            errorMessage = xhr2.responseJSON.message;
                        } else if (xhr2.responseText) {
                            try {
                                const errorData = JSON.parse(xhr2.responseText);
                                if (errorData.message) { errorMessage = errorData.message; }
                            } catch (e) {
                                if (xhr2.responseText.includes('not allowed')) {
                                    errorMessage = 'CSRF token expired. Please refresh the page and try again.';
                                }
                            }
                        }

                        $('.alert-success, .alert-danger').remove();
                        let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            errorMessage +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        $('.container').first().prepend(alertHtml);

                        selectElement.val(currentRole);

                        setTimeout(function() {
                            $('.alert-danger').fadeOut(function() { $(this).remove(); });
                        }, 5000);

                        console.error('Role update error:', {
                            status: xhr2.status,
                            statusText: xhr2.statusText,
                            error: error,
                            responseText: xhr2.responseText
                        });
                    }
                });

                roleRequests[userId] = xhr;
            });

            // Edit User - Single Modal Approach
            let currentEditUserId = null;
            let editFormSubmitting = false;
            let editModalInstance = null;
            
            // Initialize modal instance once
            const editModalEl = document.getElementById('editUserModal');
            if (editModalEl && typeof bootstrap !== 'undefined') {
                try {
                    editModalInstance = new bootstrap.Modal(editModalEl, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    console.log('Modal instance created');
                } catch (e) {
                    console.error('Error creating modal instance:', e);
                }
            }
            
            // Handle edit button click - use event delegation (most reliable)
            // Remove any existing handlers first
            $(document).off('click', '.edit-user-btn');
            
            // Attach event handler
            $(document).on('click', '.edit-user-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('Edit button clicked - handler fired');
                console.log('Button element:', this);
                console.log('Button data:', $(this).data());
                
                handleEditClick($(this));
                return false;
            });
            
            // Additional test: verify buttons are clickable
            setTimeout(function() {
                const buttons = document.querySelectorAll('.edit-user-btn');
                console.log('Total edit buttons found:', buttons.length);
                buttons.forEach(function(btn, index) {
                    console.log('Button ' + index + ':', {
                        element: btn,
                        userId: btn.getAttribute('data-user-id'),
                        classes: btn.className,
                        disabled: btn.disabled
                    });
                    
                    // Test if button is actually clickable
                    btn.addEventListener('click', function(evt) {
                        console.log('Native click event fired on button', index);
                    }, true);
                });
            }, 200);
            
            // Function to handle edit button click
            function handleEditClick($btn) {
                const userId = $btn.data('user-id');
                
                console.log('User ID:', userId);
                
                if (editFormSubmitting) {
                    console.log('Form is already submitting');
                    return false;
                }
                
                if (!userId) {
                    alert('Error: User ID not found. Please refresh the page.');
                    return false;
                }
                
                const row = $btn.closest('tr');
                const userName = row.find('td:eq(1)').text().trim();
                const userEmail = row.find('td:eq(2)').text().trim();
                const userRole = $btn.attr('data-user-role') || '';
                const departmentId = $btn.attr('data-department-id') || '';
                const programId = $btn.attr('data-program-id') || '';
                const studentId = $btn.attr('data-student-id') || '';
                
                console.log('User data:', { name: userName, email: userEmail, role: userRole, department_id: departmentId, program_id: programId });
                
                currentEditUserId = userId;
                
                // Get throughly token
                $.ajax({
                    url: '<?= base_url('admin/users/get-token') ?>',
                    type: 'GET',
                    dataType: 'json',
                    async: false,
                    success: function(response) {
                        $('#edit_throughly_token').val(response.throughly_token || 'token_' + Date.now());
                    },
                    error: function() {
                        $('#edit_throughly_token').val('token_' + Date.now());
                    }
                });
                
                // Populate form
                $('#edit_name').val(userName.trim());
                $('#edit_email').val(userEmail);
                
                // Populate department/program fields for students
                if (userRole && userRole.toLowerCase() === 'student') {
                    $('#edit_department_id').val(departmentId);
                    $('#edit_program_id').val(programId);
                    $('#edit_student_id').val(studentId);
                    $('#editStudentFields').show();
                    
                    // Filter programs based on selected department
                    if (departmentId) {
                        $('#edit_program_id option').each(function() {
                            const progDeptId = $(this).attr('data-department-id');
                            if (progDeptId && progDeptId != departmentId) {
                                $(this).hide();
                            } else {
                                $(this).show();
                            }
                        });
                    }
                } else {
                    $('#edit_department_id').val('');
                    $('#edit_program_id').val('');
                    $('#edit_student_id').val('');
                    $('#editStudentFields').hide();
                }
                
                $('#editUserModalLabel').text('Edit User: ' + userName.trim());
                
                // Show modal
                if (editModalInstance) {
                    try {
                        editModalInstance.show();
                        console.log('Modal shown using existing instance');
                    } catch (e) {
                        console.error('Error showing modal:', e);
                        // Try to create new instance
                        const editModal = document.getElementById('editUserModal');
                        if (editModal && typeof bootstrap !== 'undefined') {
                            editModalInstance = new bootstrap.Modal(editModal, {
                                backdrop: true,
                                keyboard: true,
                                focus: true
                            });
                            editModalInstance.show();
                            console.log('Modal shown using new instance');
                        } else {
                            alert('Error opening edit modal. Please refresh the page.');
                            return false;
                        }
                    }
                } else {
                    // Try to create modal instance if it doesn't exist
                    const editModal = document.getElementById('editUserModal');
                    if (editModal && typeof bootstrap !== 'undefined') {
                        editModalInstance = new bootstrap.Modal(editModal, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                        editModalInstance.show();
                        console.log('Modal shown using newly created instance');
                    } else {
                        alert('Edit modal not found. Please refresh the page.');
                        return false;
                    }
                }
                
                // Focus on name field
                $('#editUserModal').off('shown.bs.modal').on('shown.bs.modal', function() {
                    setTimeout(function() {
                        $('#edit_name').focus();
                    }, 100);
                });
            }

            // Form submission handler
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (editFormSubmitting) {
                    return false;
                }
                
                if (!currentEditUserId) {
                    alert('Error: User ID not found. Please refresh the page.');
                    return false;
                }
                
                const submitBtn = $(this).find('button[type="submit"]');
                const originalBtnText = submitBtn.text();
                editFormSubmitting = true;
                submitBtn.prop('disabled', true).text('Saving...');
                
                const name = $('#edit_name').val().trim();
                const email = $('#edit_email').val().trim();
                const department_id = $('#edit_department_id').val() || '';
                const program_id = $('#edit_program_id').val() || '';
                const student_id = $('#edit_student_id').val() || '';
                
                if (!name || !email) {
                    alert('Name and email are required.');
                    editFormSubmitting = false;
                    submitBtn.prop('disabled', false).text(originalBtnText);
                    return false;
                }
                
                // Validate name - reject invalid characters
                // Use a JavaScript-compatible regex (Unicode property escapes may not work in all browsers)
                // Allow: letters (a-z, A-Z, including accented), numbers (0-9), spaces, hyphens (-), apostrophes ('), periods (.), commas (,)
                const namePattern = /^[a-zA-Z0-9\s\-'.,àáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞŸ]+$/;
                if (!namePattern.test(name)) {
                    alert('Invalid characters detected in name. Only letters, numbers, spaces, hyphens, apostrophes, periods, and commas are allowed.');
                    editFormSubmitting = false;
                    submitBtn.prop('disabled', false).text(originalBtnText);
                    $('#edit_name').focus().select();
                    return false;
                }
                
                // Check for throughly patterns
                const throughlyPatterns = ['ˈthȯr-', 'ˈthə-(ˌ)rō', 'θʌrəθɜːroʊ', '=+[\';/.,.\'', '[][];;;;[[', '[[', ']]', ';;'];
                const nameLower = name.toLowerCase();
                for (let i = 0; i < throughlyPatterns.length; i++) {
                    if (nameLower.indexOf(throughlyPatterns[i].toLowerCase()) !== -1) {
                        alert('Invalid characters detected in name. Security characters are not allowed.');
                        editFormSubmitting = false;
                        submitBtn.prop('disabled', false).text(originalBtnText);
                        $('#edit_name').focus().select();
                        return false;
                    }
                }
                
                const csrfTokenName = '<?= csrf_token() ?>';
                const csrfHeaderName = '<?= csrf_header() ?>';
                const csrfCookieName = '<?= config("Security")->cookieName ?>';
                
                function getCookie(name) {
                    const value = `; ${document.cookie}`;
                    const parts = value.split(`; ${name}=`);
                    if (parts.length === 2) return parts.pop().split(';').shift();
                    return null;
                }
                
                let csrfToken = getCookie(csrfCookieName) || 
                               $('meta[name="' + csrfTokenName + '"]').attr('content') || 
                               '<?= csrf_hash() ?>';
                
                const formData = {
                    name: name,
                    email: email,
                    department_id: department_id,
                    program_id: program_id,
                    student_id: student_id,
                    throughly_token: $('#edit_throughly_token').val(),
                    [csrfTokenName]: csrfToken
                };
                
                $.ajax({
                    url: '<?= base_url('admin/users/update') ?>/' + currentEditUserId,
                    type: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        [csrfHeaderName]: csrfToken
                    },
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        editFormSubmitting = false;
                        submitBtn.prop('disabled', false).text(originalBtnText);
                        
                        if (response.status === 'success') {
                            if (response.csrf_token) {
                                $('meta[name="' + csrfTokenName + '"]').attr('content', response.csrf_token);
                            }
                            
                            if (editModalInstance) {
                                editModalInstance.hide();
                            }
                            
                            const editBtn = $('.edit-user-btn[data-user-id="' + currentEditUserId + '"]');
                            const row = editBtn.closest('tr');
                            row.find('td:eq(1)').text(name);
                            row.find('td:eq(2)').text(email);
                            $('#editUserModalLabel').text('Edit User: ' + name);
                            
                            $('.alert-success, .alert-danger').remove();
                            let alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            $('.container').first().prepend(alertHtml);
                            
                            setTimeout(function() {
                                $('.alert-success').fadeOut(function() { $(this).remove(); });
                            }, 3000);
                        } else {
                            $('.alert-success, .alert-danger').remove();
                            let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                (response.message || 'Error updating user') +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            $('.container').first().prepend(alertHtml);
                            
                            setTimeout(function() {
                                $('.alert-danger').fadeOut(function() { $(this).remove(); });
                            }, 5000);
                        }
                    },
                    error: function(xhr, status, error) {
                        editFormSubmitting = false;
                        submitBtn.prop('disabled', false).text(originalBtnText);
                        
                        let errorMessage = 'An error occurred while updating the user.';
                        
                        if (xhr.status === 403) {
                            errorMessage = 'CSRF token expired. Please refresh the page and try again.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        $('.alert-success, .alert-danger').remove();
                        let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            errorMessage +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        $('.container').first().prepend(alertHtml);
                        
                        setTimeout(function() {
                            $('.alert-danger').fadeOut(function() { $(this).remove(); });
                        }, 5000);
                    }
                });
                
                return false;
            });
            
            // Reset form when modal is hidden
            // Filter programs based on selected department in edit modal
            $('#edit_department_id').on('change', function() {
                const selectedDeptId = $(this).val();
                const $programSelect = $('#edit_program_id');
                const $programOptions = $programSelect.find('option[data-department-id]');
                
                // Reset program selection
                $programSelect.val('');
                
                if (!selectedDeptId) {
                    // Show all programs if no department selected
                    $programOptions.show();
                    $programSelect.find('option[value=""]').show();
                } else {
                    // Hide all programs first
                    $programOptions.hide();
                    $programSelect.find('option[value=""]').show();
                    
                    // Show only programs that belong to selected department
                    $programOptions.each(function() {
                        const progDeptId = $(this).attr('data-department-id');
                        if (progDeptId == selectedDeptId) {
                            $(this).show();
                        }
                    });
                }
            });

            $('#editUserModal').on('hidden.bs.modal', function() {
                currentEditUserId = null;
                editFormSubmitting = false;
                $('#editUserForm')[0].reset();
                $('#editStudentFields').hide();
                $('#editUserForm').find('button[type="submit"]').prop('disabled', false).text('Save Changes');
            });
        });
    }
    
    // Initialize when jQuery is ready
    if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
        initManageUsers();
    } else {
        // Wait for jQuery
        let attempts = 0;
        const maxAttempts = 100;
        const checkJQuery = setInterval(function() {
            attempts++;
            if (typeof jQuery !== 'undefined' && typeof $ !== 'undefined') {
                clearInterval(checkJQuery);
                initManageUsers();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkJQuery);
                console.error('jQuery failed to load');
            }
        }, 50);
    }
})();
</script>
