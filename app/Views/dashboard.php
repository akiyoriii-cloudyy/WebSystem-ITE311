<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Dashboard</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php 
        $dashErr = session()->getFlashdata('error');
        if ($dashErr && stripos($dashErr, 'access denied') === false && stripos($dashErr, 'insufficient permissions') === false): ?>
        <div class="alert alert-danger text-center"><?= $dashErr ?></div>
    <?php endif; ?>

    <!-- ✅ Welcome Card -->
    <div class="welcome-card text-center mb-5">
        <h2 class="fw-bold mb-2">Welcome, <?= esc($user_name ?? 'User') ?> 🎉</h2>
        <p class="mb-0">Role: <strong><?= ucfirst(esc($user_role ?? '')) ?></strong></p>
    </div>

    <!-- ✅ ROLE-SPECIFIC DASHBOARDS -->
    <?php if ($user_role === 'admin'): ?>
        <!-- ADMIN SECTION -->
        <div class="row mb-4">
            <div class="col-md-3"><div class="card text-center p-3"><h6>Total Users</h6><h3><?= esc($stats['total_users'] ?? 0) ?></h3></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><h6>Total Courses</h6><h3><?= esc($stats['total_courses'] ?? 0) ?></h3></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><h6>Active Students</h6><h3><?= esc($stats['active_students'] ?? 0) ?></h3></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><h6>Active Teachers</h6><h3><?= esc($stats['active_teachers'] ?? 0) ?></h3></div></div>
        </div>

        <div class="card mt-4">
            <div class="card-header fw-bold">System Overview</div>
            <div class="card-body">

                <?php if (!empty($users)): ?>
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Current Role</th>
                                <th>Change Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= esc($u['name']) ?></td>
                                    <td><?= esc($u['email']) ?></td>
                                    <td><strong><?= ucfirst(esc($u['role'])) ?></strong></td>
                                    <td>
                                        <?php if (session()->get('user_id') != $u['id']): ?>
                                            <select class="form-select form-select-sm role-select" data-user-id="<?= $u['id'] ?>">
                                                <option value="teacher" <?= $u['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                                <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                            </select>
                                        <?php else: ?>
                                            <em class="text-muted">You</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted mb-0">No users found.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($user_role === 'teacher'): ?>
        <!-- TEACHER SECTION -->
        <div class="card mt-4">
            <div class="card-header fw-bold">My Courses & Enrolled Students</div>
            <div class="card-body">
                <?php if (!empty($courses)): ?>
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Course Name</th>
                                <th>Students Enrolled</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $db = \Config\Database::connect();
                            foreach ($courses as $index => $c):
                                $courseId = $c['id'];
                                $students = $db->table('enrollments')
                                    ->select('users.id, users.name, users.email')
                                    ->join('users', 'users.id = enrollments.user_id')
                                    ->where('enrollments.course_id', $courseId)
                                    ->get()->getResultArray();
                            ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($c['title'] ?? $c['name']) ?></td>
                                    <td>
                                        <?php if (!empty($students)): ?>
                                            <span class="text-success"><?= count($students) ?> student(s)</span>
                                        <?php else: ?>
                                            <span class="text-muted">No students enrolled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($students)): ?>
                                            <button 
                                                class="btn btn-sm btn-primary view-students-btn" 
                                                data-students='<?= json_encode($students) ?>'
                                                data-course-title="<?= esc($c['title'] ?? $c['name']) ?>">
                                                View
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>No Action</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted mb-0">No courses assigned.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- MODAL FOR VIEWING STUDENTS -->
        <div class="modal fade" id="studentsModal" tabindex="-1" aria-labelledby="studentsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="studentsModalLabel">Enrolled Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="studentsList"></ul>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($user_role === 'student'): ?>
        <!-- STUDENT SECTION -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">My Enrolled Courses</div>
            <ul class="list-group list-group-flush" id="enrolledCourses">
                <?php if (!empty($enrolledCourses)): ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= esc($course['title'] ?? $course['name']) ?></span>
                            <a class="btn btn-sm btn-primary" href="<?= base_url('materials/course/' . (int)$course['id']) ?>">View Materials</a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted no-enrollment-msg">You are not enrolled in any course yet.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ✅ Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="searchForm" method="GET" action="<?= base_url('courses/search') ?>">
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
                                    id="searchInput" 
                                    name="q" 
                                    placeholder="Search courses by title, description, or code..." 
                                    value=""
                                    autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100" id="searchBtn">
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ✅ Search Results Info -->
        <div id="searchInfo" class="mb-3" style="display: none;">
            <p class="text-muted">
                <span id="resultCount">0</span> result(s) found
            </p>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Available Courses</div>
            <ul class="list-group list-group-flush" id="availableCoursesList">
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): ?>
                        <?php
                            $isEnrolled = false;
                            foreach ($enrolledCourses as $en) {
                                if ($en['id'] == $course['id']) {
                                    $isEnrolled = true;
                                    break;
                                }
                            }
                            // Get course code if available
                            $courseCode = $course['code'] ?? '';
                            $courseDescription = $course['description'] ?? '';
                        ?>
                        <li class="list-group-item course-item d-flex justify-content-between align-items-center"
                            data-course-title="<?= esc(strtolower($course['title'] ?? $course['name'] ?? '')) ?>"
                            data-course-description="<?= esc(strtolower($courseDescription)) ?>"
                            data-course-code="<?= esc(strtolower($courseCode)) ?>">
                            <div class="flex-grow-1">
                                <span class="course-title fw-bold">
                                    <?= esc($course['title'] ?? $course['name']) ?>
                                    <?php if (!empty($courseCode)): ?>
                                        <span class="badge bg-secondary ms-2"><?= esc($courseCode) ?></span>
                                    <?php endif; ?>
                                </span>
                                <?php if (!empty($courseDescription)): ?>
                                    <br><small class="text-muted course-description">
                                        <?= esc(substr($courseDescription, 0, 100)) ?>
                                        <?= strlen($courseDescription) > 100 ? '...' : '' ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <?php if ($isEnrolled): ?>
                                <button class="btn btn-sm btn-secondary ms-3" disabled>Enrolled</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-success enroll-btn ms-3" data-course-id="<?= esc($course['id']) ?>">Enroll</button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted" id="noCoursesMessage">No available courses found.</li>
                <?php endif; ?>
            </ul>
            
            <!-- ✅ Empty Search Results Message -->
            <div id="noSearchResults" class="alert alert-warning m-3" style="display: none;">
                <p class="mb-0">No courses found matching your search criteria.</p>
            </div>
        </div>

        <div id="alertBox" class="alert mt-3 d-none"></div>
<?php endif; ?>
</div>

<!-- ✅ AJAX Enroll Logic & Modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize enrolled courses set for quick lookup
    const enrolledCourseIds = new Set();
    $('#enrolledCourses li').each(function() {
        const courseText = $(this).text().trim();
        const courseId = $(this).data('course-id');
        if (courseId) enrolledCourseIds.add(courseId);
    });

    // ✅ AUTOMATIC SERVER-SIDE SEARCH (Debounced - Only for student role)
    <?php if ($user_role === 'student'): ?>
    let searchTimeout;
    let isSearching = false;
    
    function performServerSearch(searchTerm) {
        if (isSearching) return; // Prevent multiple simultaneous searches
        
        isSearching = true;
        const $searchBtn = $('#searchBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('courses/search') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearching = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    // Update search info
                    $('#resultCount').text(response.count);
                    $('#searchInfo').show();

                    // Clear existing courses
                    $('#availableCoursesList').empty();
                    $('#noCoursesMessage').hide();
                    $('#noSearchResults').hide();

                    if (response.results.length > 0) {
                        // Display search results
                        let html = '';
                        response.results.forEach(function(course) {
                            const isEnrolled = course.is_enrolled;
                            let actionButton = '';
                            
                            if (isEnrolled) {
                                actionButton = '<button class="btn btn-sm btn-secondary ms-3" disabled>Enrolled</button>';
                            } else {
                                actionButton = '<button class="btn btn-sm btn-success enroll-btn ms-3" data-course-id="' + course.id + '">Enroll</button>';
                            }

                            html += '<li class="list-group-item course-item d-flex justify-content-between align-items-center" ' +
                                'data-course-title="' + (course.title || '').toLowerCase() + '" ' +
                                'data-course-description="' + (course.description || '').toLowerCase() + '" ' +
                                'data-course-code="' + (course.code || '').toLowerCase() + '">' +
                                '<div class="flex-grow-1">' +
                                '<span class="course-title fw-bold">' + 
                                (course.title || 'Untitled Course') +
                                (course.code ? ' <span class="badge bg-secondary ms-2">' + course.code + '</span>' : '') +
                                '</span>' +
                                (course.description ? '<br><small class="text-muted course-description">' + 
                                (course.description.length > 100 ? course.description.substring(0, 100) + '...' : course.description) + 
                                '</small>' : '') +
                                '</div>' +
                                actionButton +
                                '</li>';
                        });

                        $('#availableCoursesList').html(html);
                        $('#availableCoursesList').data('searched', true); // Mark that we've done a server search
                        
                        // Re-initialize enroll buttons for new results
                        initializeEnrollButtons();
                    } else {
                        $('#noSearchResults').show();
                    }
                } else {
                    $('#alertBox').removeClass('d-none alert-success')
                                  .addClass('alert alert-danger')
                                  .text(response.message || 'Search failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                isSearching = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                
                // Try to parse error response
                let errorMessage = 'An error occurred during search. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // If not JSON, use default message
                    }
                }
                
                $('#alertBox').removeClass('d-none alert-success')
                              .addClass('alert alert-danger')
                              .text(errorMessage);
                console.error('Search error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }
    
    // Automatic search as user types (with debouncing)
    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (searchTerm === '') {
            // If search is empty, reload all courses
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Reload page to show all courses
                window.location.href = "<?= base_url('dashboard') ?>";
            }, 300);
        } else {
            // Debounce: wait 500ms after user stops typing before searching
            searchTimeout = setTimeout(function() {
                performServerSearch(searchTerm);
            }, 500);
        }
    });

    // ✅ SERVER-SIDE SEARCH (AJAX - Submit form - uses same function)
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(searchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchInput').val().trim();
        performServerSearch(searchTerm);
    });
    <?php endif; ?>

    // ✅ Initialize Enroll Buttons Function
    function initializeEnrollButtons() {
        $('.enroll-btn').off('click').on('click', function() {
            const courseId = $(this).data('course-id');
            const button = $(this);
            const courseTitle = button.closest('li').find('.course-title').text().trim().replace(/\s*\(.*?\)\s*/g, '').trim();

            $.ajax({
                url: "<?= base_url('course/enroll') ?>",
                type: "POST",
                data: {
                    course_id: courseId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Add to enrolled course list
                        $('#enrolledCourses').append('<li class="list-group-item d-flex justify-content-between align-items-center">' +
                            '<span>' + courseTitle + '</span>' +
                            '<a class="btn btn-sm btn-primary" href="<?= base_url('materials/course/') ?>' + courseId + '">View Materials</a>' +
                            '</li>');
                        enrolledCourseIds.add(courseId);

                        // Update the enroll button
                        button.prop('disabled', true)
                              .removeClass('btn-success')
                              .addClass('btn-secondary')
                              .text('Enrolled');

                        // Remove "no enrollment" message if present
                        $('.no-enrollment-msg').remove();

                        // Show success alert
                        $('#alertBox').removeClass('d-none alert-danger')
                                      .addClass('alert alert-success')
                                      .text(response.message);

                        // Auto-hide alert after 3 seconds
                        setTimeout(function() {
                            $('#alertBox').addClass('d-none');
                        }, 3000);

                        // ✅ Trigger notification refresh immediately
                        if (typeof fetchNotifications === 'function') {
                            setTimeout(function() {
                                fetchNotifications();
                                
                                // After fetching, get the latest notification and show toast
                                setTimeout(function() {
                                    $.ajax({
                                        url: "<?= base_url('notifications') ?>",
                                        type: "GET",
                                        dataType: 'json',
                                        success: function(notifResponse) {
                                            if (notifResponse.status === 'success' && 
                                                notifResponse.notifications && 
                                                notifResponse.notifications.length > 0) {
                                                
                                                // Get the most recent unread notification
                                                const latestNotif = notifResponse.notifications.find(n => n.is_read == 0);
                                                
                                                if (latestNotif && typeof showNotificationToast === 'function') {
                                                    showNotificationToast(latestNotif.id, latestNotif.message);
                                                }
                                            }
                                        }
                                    });
                                }, 500);
                            }, 500);
                        }
                    } else {
                        $('#alertBox').removeClass('d-none alert-success')
                                      .addClass('alert alert-danger')
                                      .text(response.message);
                    }
                },
                error: function() {
                    $('#alertBox').removeClass('d-none alert-success')
                                  .addClass('alert alert-danger')
                                  .text('An error occurred. Please try again.');
                }
            });
        });
    }

    // Initialize enroll buttons on page load
    initializeEnrollButtons();

    // View students modal
    $('.view-students-btn').click(function() {
        const students = $(this).data('students');
        const courseTitle = $(this).data('course-title');
        $('#studentsModalLabel').text('Students Enrolled in: ' + courseTitle);

        let listHtml = '';
        if (students.length > 0) {
            students.forEach(function(s) {
                listHtml += '<li class="list-group-item">' + s.name + ' (' + s.email + ')</li>';
            });
        } else {
            listHtml = '<li class="list-group-item text-muted">No students enrolled.</li>';
        }

        $('#studentsList').html(listHtml);
        $('#studentsModal').modal('show');
    });
});
</script>

<?= $this->include('template/footer') ?>
