<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Enrollment Management</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Enroll User</h5>
                </div>
                <div class="card-body">
                    <form id="enrollForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Select User</label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">-- Select User --</option>
                                <optgroup label="Students">
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= $student['id'] ?>"><?= esc($student['name']) ?> (<?= esc($student['email']) ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Teachers">
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher['id'] ?>"><?= esc($teacher['name']) ?> (<?= esc($teacher['email']) ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select name="course_id" id="course_id" class="form-select" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>">
                                        <?= esc($course['title']) ?> 
                                        <?php 
                                        $courseNumber = $course['course_number'] ?? $course['code'] ?? $course['course_number'] ?? '';
                                        if (!empty($courseNumber)): ?>
                                            (<?= esc($courseNumber) ?>)
                                        <?php endif; ?>
                                        <?php if (!empty($course['instructor_name'])): ?>
                                            - Instructor: <?= esc($course['instructor_name']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Enroll User</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Assign Teacher to Course</h5>
                </div>
                <div class="card-body">
                    <form id="assignTeacherForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select name="course_id" id="assign_course_id" class="form-select" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>">
                                        <?= esc($course['title']) ?>
                                        <?php if (!empty($course['instructor_name'])): ?>
                                            (Current: <?= esc($course['instructor_name']) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Teacher</label>
                            <select name="teacher_id" id="teacher_id" class="form-select" required>
                                <option value="">-- Select Teacher --</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= esc($teacher['name']) ?> (<?= esc($teacher['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-info w-100">Assign Teacher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">All Enrollments</h5>
        </div>
        <div class="card-body">
            <!-- ✅ Search Form for Enrollments -->
            <div class="card mb-3">
                <div class="card-body">
                    <form id="searchEnrollmentsForm" method="GET" action="javascript:void(0);">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                        </svg>
                                    </span>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="searchEnrollmentsInput" 
                                        name="q" 
                                        placeholder="Search enrollments by user name, email, course, or status..." 
                                        value=""
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" id="searchEnrollmentsBtn">
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ✅ Search Results Info -->
            <div id="searchEnrollmentsInfo" class="mb-3" style="display: none;">
                <p class="text-muted">
                    <span id="enrollmentsResultCount">0</span> result(s) found
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="enrollmentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Course</th>
                            <th>Course Number</th>
                            <th>Status</th>
                            <th>Enrolled At</th>
                            <th>Final Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="enrollmentsTableBody">
                        <?php if (!empty($enrollments)): ?>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><?= esc($enrollment['user_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($enrollment['email'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php 
                                        $role = strtolower($enrollment['role'] ?? 'student');
                                        $roleBadge = 'bg-info';
                                        if ($role === 'teacher') $roleBadge = 'bg-warning';
                                        elseif ($role === 'admin') $roleBadge = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $roleBadge ?>"><?= ucfirst(esc($role)) ?></span>
                                    </td>
                                    <td><?= esc($enrollment['course_title'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php 
                                        $courseNumber = $enrollment['course_number'] ?? '';
                                        if (!empty($courseNumber) && $courseNumber !== 'N/A'): ?>
                                            <span class="badge bg-secondary"><?= esc($courseNumber) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status = strtoupper($enrollment['completion_status'] ?? 'ENROLLED');
                                        $badgeClass = 'bg-info';
                                        if ($status === 'COMPLETED') $badgeClass = 'bg-success';
                                        elseif ($status === 'FAILED' || $status === 'DROPPED') $badgeClass = 'bg-danger';
                                        elseif ($status === 'IN_PROGRESS') $badgeClass = 'bg-warning';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc(ucwords(strtolower(str_replace('_', ' ', $status)))) ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $enrolledDate = $enrollment['enrolled_at'] ?? $enrollment['enrollment_date'] ?? null;
                                        if ($enrolledDate) {
                                            echo esc(date('Y-m-d H:i', strtotime($enrolledDate)));
                                        } else {
                                            echo '<span class="text-muted">N/A</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($enrollment['final_grade']) && $enrollment['final_grade'] > 0): ?>
                                            <strong class="<?= $enrollment['final_grade'] >= 75 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($enrollment['final_grade'], 2) ?>%
                                            </strong>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger unenroll-btn" 
                                                data-enrollment-id="<?= $enrollment['id'] ?>"
                                                data-user-name="<?= esc($enrollment['user_name']) ?>"
                                                data-course-title="<?= esc($enrollment['course_title']) ?>">
                                            Unenroll
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No enrollments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div id="noEnrollmentsSearchResults" style="display: none;">
                <p class="text-muted mb-0 text-center">No enrollments found matching your search.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Enroll User Form
    $('#enrollForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Enrolling...');
        
        $.ajax({
            url: '<?= site_url('admin/enrollments/enroll') ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    // Show success message at the top
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<strong>✓ Success!</strong> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('.container').prepend(alert);
                    
                    // Reset form
                    form[0].reset();
                    
                    // Scroll to top to show message
                    $('html, body').animate({ scrollTop: 0 }, 300);
                    
                    // Reload after 1.5 seconds to show updated enrollments
                    setTimeout(function() {
                        window.location.reload(true);
                    }, 1500);
                } else {
                    // Show error message
                    const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<strong>✗ Error!</strong> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('.container').prepend(alert);
                    $('html, body').animate({ scrollTop: 0 }, 300);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                    } catch (e) {
                        // Use default error message
                    }
                }
                
                const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<strong>✗ Error!</strong> ' + errorMsg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');
                $('.container').prepend(alert);
                $('html, body').animate({ scrollTop: 0 }, 300);
            }
        });
    });

    // Assign Teacher Form
    $('#assignTeacherForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Assigning...');
        
        $.ajax({
            url: '<?= site_url('admin/courses/assign-teacher') ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success' || response.status === 'warning') {
                    // Show success/warning message at the top
                    const alertClass = response.status === 'success' ? 'alert-success' : 'alert-warning';
                    const alert = $('<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                        '<strong>✓ ' + (response.status === 'success' ? 'Success!' : 'Warning!') + '</strong> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('.container').prepend(alert);
                    
                    // Reset form
                    form[0].reset();
                    
                    // Scroll to top to show message
                    $('html, body').animate({ scrollTop: 0 }, 300);
                    
                    // Reload after 1.5 seconds to show updated data
                    setTimeout(function() {
                        window.location.reload(true);
                    }, 1500);
                } else {
                    // Show error message
                    const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<strong>✗ Error!</strong> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('.container').prepend(alert);
                    $('html, body').animate({ scrollTop: 0 }, 300);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                    } catch (e) {
                        // Use default error message
                    }
                }
                
                const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<strong>✗ Error!</strong> ' + errorMsg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');
                $('.container').prepend(alert);
                $('html, body').animate({ scrollTop: 0 }, 300);
            }
        });
    });

    // Unenroll User - AJAX
    $(document).on('click', '.unenroll-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const enrollmentId = btn.data('enrollment-id');
        const userName = btn.data('user-name');
        const courseTitle = btn.data('course-title');
        
        // Confirm action
        if (!confirm(`Are you sure you want to unenroll "${userName}" from "${courseTitle}"?`)) {
            return;
        }
        
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Unenrolling...');
        
        $.ajax({
            url: '<?= site_url('admin/enrollments/unenroll') ?>',
            type: 'POST',
            data: {
                enrollment_id: enrollmentId,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    // Show success message
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<strong>✓ Success!</strong> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('.container').prepend(alert);
                    
                    // Scroll to top
                    $('html, body').animate({ scrollTop: 0 }, 300);
                    
                    // Reload after 1.5 seconds
                    setTimeout(function() {
                        window.location.reload(true);
                    }, 1500);
                } else {
                    // Show error message
                    const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<strong>✗ Error!</strong> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('.container').prepend(alert);
                    $('html, body').animate({ scrollTop: 0 }, 300);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                    } catch (e) {
                        // Use default error message
                    }
                }
                
                const alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<strong>✗ Error!</strong> ' + errorMsg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');
                $('.container').prepend(alert);
                $('html, body').animate({ scrollTop: 0 }, 300);
            }
        });
    });

    // ✅ ENROLLMENTS SEARCH FUNCTIONALITY - Copied EXACT pattern from working manage users search
    let enrollmentsSearchTimeout;
    let isSearchingEnrollments = false;
    const originalEnrollmentsHtml = $('#enrollmentsTableBody').html(); // Store original enrollments
    
    function performEnrollmentsSearch(searchTerm) {
        if (isSearchingEnrollments) return; // Prevent multiple simultaneous searches
        
        isSearchingEnrollments = true;
        const $searchBtn = $('#searchEnrollmentsBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('admin/search/enrollments') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingEnrollments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    // Update search info
                    $('#enrollmentsResultCount').text(response.count);
                    $('#searchEnrollmentsInfo').show();

                    // Clear existing enrollments
                    $('#enrollmentsTableBody').empty();
                    $('#noEnrollmentsSearchResults').hide();

                    if (response.results.length > 0) {
                        response.results.forEach(function(enrollment, index) {
                            const role = (enrollment.role || 'student').toLowerCase();
                            const roleBadge = role === 'teacher' ? 'bg-warning' : (role === 'admin' ? 'bg-danger' : 'bg-info');
                            
                            const status = (enrollment.completion_status || 'ENROLLED').toUpperCase();
                            let statusBadgeClass = 'bg-info';
                            if (status === 'COMPLETED') statusBadgeClass = 'bg-success';
                            else if (status === 'FAILED' || status === 'DROPPED') statusBadgeClass = 'bg-danger';
                            else if (status === 'IN_PROGRESS') statusBadgeClass = 'bg-warning';
                            
                            const courseNumberHtml = enrollment.course_number && enrollment.course_number !== 'N/A'
                                ? '<span class="badge bg-secondary">' + enrollment.course_number + '</span>'
                                : '<span class="text-muted">N/A</span>';
                            
                            const enrolledDate = enrollment.enrolled_at || enrollment.enrollment_date;
                            const enrolledDateHtml = enrolledDate 
                                ? new Date(enrolledDate).toLocaleString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'})
                                : '<span class="text-muted">N/A</span>';
                            
                            const finalGradeHtml = enrollment.final_grade && enrollment.final_grade > 0
                                ? '<strong class="' + (enrollment.final_grade >= 75 ? 'text-success' : 'text-danger') + '">' + 
                                  parseFloat(enrollment.final_grade).toFixed(2) + '%</strong>'
                                : '<span class="text-muted">N/A</span>';
                            
                            const row = '<tr>' +
                                '<td>' + (enrollment.user_name || 'N/A') + '</td>' +
                                '<td>' + (enrollment.email || 'N/A') + '</td>' +
                                '<td><span class="badge ' + roleBadge + '">' + role.charAt(0).toUpperCase() + role.slice(1) + '</span></td>' +
                                '<td>' + (enrollment.course_title || 'N/A') + '</td>' +
                                '<td>' + courseNumberHtml + '</td>' +
                                '<td><span class="badge ' + statusBadgeClass + '">' + status.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase()) + '</span></td>' +
                                '<td>' + enrolledDateHtml + '</td>' +
                                '<td>' + finalGradeHtml + '</td>' +
                                '<td>' +
                                '<button type="button" class="btn btn-sm btn-danger unenroll-btn" ' +
                                'data-enrollment-id="' + enrollment.id + '" ' +
                                'data-user-name="' + (enrollment.user_name || '') + '" ' +
                                'data-course-title="' + (enrollment.course_title || '') + '">Unenroll</button>' +
                                '</td>' +
                            '</tr>';
                            $('#enrollmentsTableBody').append(row);
                        });
                    } else {
                        $('#noEnrollmentsSearchResults').show();
                    }
                } else {
                    $('#searchEnrollmentsInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingEnrollments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchEnrollmentsInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    $('#searchEnrollmentsInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(enrollmentsSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(enrollmentsSearchTimeout);
            enrollmentsSearchTimeout = setTimeout(function() {
                $('#enrollmentsTableBody').html(originalEnrollmentsHtml);
                $('#searchEnrollmentsInfo').hide();
                $('#noEnrollmentsSearchResults').hide();
            }, 300);
        } else {
            enrollmentsSearchTimeout = setTimeout(function() {
                performEnrollmentsSearch(searchTerm);
            }, 500);
        }
    });

    $('#searchEnrollmentsForm').on('submit', function(e) {
        e.preventDefault();
        clearTimeout(enrollmentsSearchTimeout);
        const searchTerm = $('#searchEnrollmentsInput').val().trim();
        if (searchTerm === '') {
            $('#enrollmentsTableBody').html(originalEnrollmentsHtml);
            $('#searchEnrollmentsInfo').hide();
            $('#noEnrollmentsSearchResults').hide();
        } else {
            performEnrollmentsSearch(searchTerm);
        }
    });
});
</script>

<?= $this->include('template/footer') ?>
