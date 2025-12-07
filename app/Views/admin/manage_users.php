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
                                        <?php if (isset($u['status'])): ?>
                                            <span class="badge <?= $u['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ucfirst(esc($u['status'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (session()->get('user_id') != $u['id'] && strtolower($u['role']) !== 'admin'): ?>
                                            <select class="form-select form-select-sm role-select" 
                                                    data-user-id="<?= $u['id'] ?>" 
                                                    data-current-role="<?= esc($u['role']) ?>">
                                                <option value="teacher" <?= $u['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                                <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                            </select>
                                        <?php elseif (strtolower($u['role']) === 'admin'): ?>
                                            <span class="badge bg-danger">Admin (Protected)</span>
                                        <?php else: ?>
                                            <em class="text-muted">You</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (session()->get('user_id') != $u['id']): ?>
                                            <div class="btn-group" role="group">
                                                <?php if (isset($u['status'])): ?>
                                                    <a href="<?= base_url('admin/users/toggle-status/' . $u['id']) ?>" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Toggle Status">
                                                        <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (strtolower($u['role']) !== 'admin'): ?>
                                                    <a href="<?= base_url('admin/users/delete/' . $u['id']) ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to remove this user from the admin view? The user data will be preserved in the database but hidden from management.');"
                                                       title="Remove User from Admin View">
                                                        Delete
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"></span>
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

<!-- ✅ jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Role change handler - works for multiple changes without refresh
    $(document).on('change', '.role-select', function() {
        const userId = $(this).data('user-id');
        const newRole = $(this).val();
        const selectElement = $(this);
        const currentRole = selectElement.data('current-role');
        
        // Don't proceed if role hasn't actually changed
        if (newRole === currentRole) {
            return;
        }
        
        // Disable select during request to prevent multiple clicks
        selectElement.prop('disabled', true);
        
        if (!confirm('Change this user\'s role to ' + newRole.charAt(0).toUpperCase() + newRole.slice(1) + '?')) {
            // Revert to current role if cancelled
            selectElement.val(currentRole);
            selectElement.prop('disabled', false);
            return;
        }
        
        // Get fresh CSRF token from meta tag or cookie
        const csrfTokenName = '<?= csrf_token() ?>';
        const csrfHeaderName = '<?= csrf_header() ?>';
        const csrfCookieName = '<?= config("Security")->cookieName ?>';
        
        // Try to get token from cookie first (since CSRF uses cookie method)
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
        
        let csrfToken = getCookie(csrfCookieName) || 
                       $('meta[name="' + csrfTokenName + '"]').attr('content') || 
                       '<?= csrf_hash() ?>';
        
        $.ajax({
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
                selectElement.prop('disabled', false);
                
                if (response.status === 'success') {
                    // Update CSRF token from response if available (for next request)
                    if (response.csrf_token) {
                        $('meta[name="' + csrfTokenName + '"]').attr('content', response.csrf_token);
                    }
                    
                    // Update the Current Role column
                    const roleCell = selectElement.closest('tr').find('td:eq(3) strong');
                    const capitalizedRole = newRole.charAt(0).toUpperCase() + newRole.slice(1);
                    roleCell.text(capitalizedRole);
                    
                    // Update the data attribute to new role for next change
                    selectElement.data('current-role', newRole);
                    
                    // Remove any existing alerts first
                    $('.alert-success, .alert-danger').remove();
                    
                    // Show success message
                    let alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    $('.container').first().prepend(alertHtml);
                    
                    // Auto-dismiss after 3 seconds
                    setTimeout(function() {
                        $('.alert-success').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    // Show error message
                    $('.alert-success, .alert-danger').remove();
                    let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        (response.message || 'Error updating role') +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    $('.container').first().prepend(alertHtml);
                    
                    // Revert to current role on error
                    selectElement.val(currentRole);
                    
                    // Auto-dismiss error after 5 seconds
                    setTimeout(function() {
                        $('.alert-danger').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            },
            error: function(xhr, status, error) {
                selectElement.prop('disabled', false);
                
                // Try to parse error response
                let errorMessage = 'An error occurred while updating the role.';
                
                // Check for CSRF error specifically
                if (xhr.status === 403 || (xhr.responseText && xhr.responseText.includes('not allowed'))) {
                    errorMessage = 'CSRF token expired. Please refresh the page and try again.';
                    // Optionally refresh the page to get new token
                    setTimeout(function() {
                        if (confirm('Your session token has expired. Would you like to refresh the page?')) {
                            window.location.reload();
                        }
                    }, 2000);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // If not JSON, check if it's HTML error page
                        if (xhr.responseText.includes('not allowed')) {
                            errorMessage = 'CSRF token expired. Please refresh the page and try again.';
                        }
                    }
                }
                
                // Show error message
                $('.alert-success, .alert-danger').remove();
                let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    errorMessage +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                $('.container').first().prepend(alertHtml);
                
                // Revert to current role on error
                selectElement.val(currentRole);
                
                // Auto-dismiss error after 5 seconds
                setTimeout(function() {
                    $('.alert-danger').fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
                
                console.error('Role update error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    });
});
</script>

<?= $this->include('template/footer') ?>

