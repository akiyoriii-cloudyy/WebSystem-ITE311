<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Dashboard</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- ✅ Welcome Card -->
    <div class="welcome-card text-center mb-5">
        <h2 class="fw-bold mb-2">Welcome, <?= ucfirst(esc($user_role ?? 'User')) ?> User 🎉</h2>
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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h6>My Courses</h6>
                    <h3><?= esc($stats['my_courses'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h6>Total Students</h6>
                    <h3><?= esc($stats['total_students'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h6>Total Assignments</h6>
                    <h3><?= esc($stats['total_assignments'] ?? 0) ?></h3>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <a href="<?= site_url('teacher/courses') ?>" class="btn btn-primary btn-sm me-2">Manage Courses</a>
                        <a href="<?= site_url('teacher/announcements') ?>" class="btn btn-info btn-sm me-2">Announcements</a>
                        <a href="<?= site_url('teacher/quizzes') ?>" class="btn btn-success btn-sm me-2">Quizzes & Submissions</a>
                    </div>
                </div>
            </div>
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

        <!-- ✅ Search Success Alert -->
        <div id="searchSuccessAlert" class="alert alert-success alert-dismissible fade show mb-3" style="display: none;" role="alert">
            <strong>✅ Search Success:</strong> <span id="searchSuccessMessage">Search results found!</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- ✅ Search Error Alert -->
        <div id="searchErrorAlert" class="alert alert-danger alert-dismissible fade show mb-3" style="display: none;" role="alert">
            <strong>⚠️ Search Error:</strong> <span id="searchErrorMessage">Wrong search not in the field, try again.</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- My Courses Table -->
        <div class="card mt-4">
            <div class="card-header fw-bold">My Courses & Enrolled Students</div>
            <div class="card-body">
                <?php if (!empty($courses)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="coursesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Academic Year</th>
                                    <th>Semester</th>
                                    <th>Term</th>
                                    <th>Students</th>
                                    <th>Assignments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="coursesTableBody">
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
                                        <td>
                                            <?php if (!empty($c['course_number'])): ?>
                                                <span class="badge bg-secondary"><?= esc($c['course_number']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($c['title'] ?? $c['name']) ?></td>
                                        <td><?= !empty($c['acad_year']) ? esc($c['acad_year']) : '<span class="text-muted">N/A</span>' ?></td>
                                        <td><?= !empty($c['semester_name']) ? esc($c['semester_name']) : '<span class="text-muted">N/A</span>' ?></td>
                                        <td><?= !empty($c['term_name']) ? esc($c['term_name']) : '<span class="text-muted">N/A</span>' ?></td>
                                        <td>
                                            <?php if (!empty($students)): ?>
                                                <span class="text-success"><?= count($students) ?> student(s)</span>
                                            <?php else: ?>
                                                <span class="text-muted">0 students</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= esc($c['assignment_count'] ?? 0) ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-primary" href="<?= site_url('teacher/courses/' . $courseId . '/enroll-students') ?>">Enroll Students</a>
                                                <a class="btn btn-sm btn-success" href="<?= site_url('teacher/courses/' . $courseId . '/assignments') ?>">Assignments</a>
                                                <?php if (!empty($students)): ?>
                                                    <button 
                                                        class="btn btn-sm btn-info view-students-btn" 
                                                        data-students='<?= json_encode($students) ?>'
                                                        data-course-title="<?= esc($c['title'] ?? $c['name']) ?>">
                                                        View Students
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="noSearchResults" style="display: none;">
                        <p class="text-muted mb-0">No courses found matching your search.</p>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No courses assigned. Please contact admin to assign courses to you.</p>
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
                        <li class="list-group-item"><?= esc($course['title'] ?? $course['name']) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted no-enrollment-msg">You are not enrolled in any course yet.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Available Courses</div>
            <ul class="list-group list-group-flush">
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
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="course-title"><?= esc($course['title'] ?? $course['name']) ?></span>
                            <?php if ($isEnrolled): ?>
                                <button class="btn btn-sm btn-secondary" disabled>Enrolled</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-success enroll-btn" data-course-id="<?= esc($course['id']) ?>">Enroll</button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted">No available courses found.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div id="alertBox" class="alert mt-3 d-none"></div>
<?php endif; ?>
</div>

<!-- ✅ AJAX Enroll Logic & Modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize enrolled courses set for quick lookup
    const enrolledCourseIds = new Set();
    $('#enrolledCourses li').each(function() {
        const courseText = $(this).text().trim();
        const courseId = $(this).data('course-id');
        if (courseId) enrolledCourseIds.add(courseId);
    });

    // ✅ AUTOMATIC SERVER-SIDE SEARCH (Debounced - for teacher role)
    <?php if ($user_role === 'teacher'): ?>
    let searchTimeout;
    let isSearching = false;
    const originalCoursesHtml = $('#coursesTableBody').html(); // Store original courses
    const courseStudentsData = {}; // Store students data for each course
    
    // Store original course data with students
    <?php 
    $db = \Config\Database::connect();
    foreach ($courses as $c): 
        $courseId = $c['id'];
        $students = $db->table('enrollments')
            ->select('users.id, users.name, users.email')
            ->join('users', 'users.id = enrollments.user_id')
            ->where('enrollments.course_id', $courseId)
            ->get()->getResultArray();
    ?>
        courseStudentsData[<?= $courseId ?>] = <?= json_encode($students) ?>;
    <?php endforeach; ?>
    
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
                    $('#coursesTableBody').empty();
                    $('#noSearchResults').hide();

                    if (response.results.length > 0) {
                        // Hide error alert and show success alert if results found
                        $('#searchErrorAlert').hide();
                        $('#searchSuccessAlert').show();
                        $('#searchSuccessMessage').text('Search results found! ' + response.count + ' course(s) matching your search.');
                        
                        // Build table rows from search results
                        response.results.forEach(function(course, index) {
                            const courseCode = course.course_number || course.code || '';
                            const courseCodeHtml = courseCode 
                                ? '<span class="badge bg-secondary">' + courseCode + '</span>'
                                : '<span class="text-muted">-</span>';
                            
                            // Get students for this course (from stored data or empty)
                            const students = courseStudentsData[course.id] || [];
                            const studentsHtml = students.length > 0
                                ? '<span class="text-success">' + students.length + ' student(s)</span>'
                                : '<span class="text-muted">0 students</span>';
                            
                            const studentsBtnHtml = students.length > 0
                                ? '<button class="btn btn-sm btn-info view-students-btn" data-students=\'' + JSON.stringify(students) + '\' data-course-title="' + course.title + '">View Students</button>'
                                : '';
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + courseCodeHtml + '</td>' +
                                '<td>' + course.title + '</td>' +
                                '<td>' + (course.acad_year || '<span class="text-muted">N/A</span>') + '</td>' +
                                '<td>' + (course.semester_name || '<span class="text-muted">N/A</span>') + '</td>' +
                                '<td>' + (course.term_name || '<span class="text-muted">N/A</span>') + '</td>' +
                                '<td>' + studentsHtml + '</td>' +
                                '<td><span class="badge bg-info">0</span></td>' +
                                '<td>' +
                                    '<div class="btn-group" role="group">' +
                                        '<a class="btn btn-sm btn-primary" href="<?= site_url("teacher/courses/") ?>' + course.id + '/enroll-students">Enroll Students</a> ' +
                                        '<a class="btn btn-sm btn-success" href="<?= site_url("teacher/courses/") ?>' + course.id + '/assignments">Assignments</a> ' +
                                        studentsBtnHtml +
                                    '</div>' +
                                '</td>' +
                            '</tr>';
                            $('#coursesTableBody').append(row);
                        });
                        
                        // Re-initialize view students buttons
                        $('.view-students-btn').off('click').on('click', function() {
                            const students = $(this).data('students');
                            const courseTitle = $(this).data('course-title');
                            $('#studentsModalLabel').text('Students Enrolled in: ' + courseTitle);

                            let listHtml = '';
                            if (students && students.length > 0) {
                                students.forEach(function(s) {
                                    listHtml += '<li class="list-group-item">' + s.name + ' (' + s.email + ')</li>';
                                });
                            } else {
                                listHtml = '<li class="list-group-item text-muted">No students enrolled.</li>';
                            }

                            $('#studentsList').html(listHtml);
                            $('#studentsModal').modal('show');
                        });
                    } else {
                        // Show error alert when no results found
                        $('#searchErrorAlert').show();
                        $('#searchErrorMessage').text('Wrong search not in the field, try again.');
                        $('#noSearchResults').show();
                    }
                } else {
                    $('#searchInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearching = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    // Automatic search as user types (with debouncing)
    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (searchTerm === '') {
            // If search is empty, restore original courses
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Hide error and success alerts when clearing search
                $('#searchErrorAlert').hide();
                $('#searchSuccessAlert').hide();
                $('#coursesTableBody').html(originalCoursesHtml);
                $('#searchInfo').hide();
                $('#noSearchResults').hide();
                // Re-initialize view students buttons
                $('.view-students-btn').off('click').on('click', function() {
                    const students = $(this).data('students');
                    const courseTitle = $(this).data('course-title');
                    $('#studentsModalLabel').text('Students Enrolled in: ' + courseTitle);

                    let listHtml = '';
                    if (students && students.length > 0) {
                        students.forEach(function(s) {
                            listHtml += '<li class="list-group-item">' + s.name + ' (' + s.email + ')</li>';
                        });
                    } else {
                        listHtml = '<li class="list-group-item text-muted">No students enrolled.</li>';
                    }

                    $('#studentsList').html(listHtml);
                    $('#studentsModal').modal('show');
                });
            }, 300);
        } else {
            // Debounce: wait 500ms after user stops typing before searching
            searchTimeout = setTimeout(function() {
                performServerSearch(searchTerm);
            }, 500);
        }
    });

    // ✅ SERVER-SIDE SEARCH (AJAX - Submit form)
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(searchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchInput').val().trim();
        if (searchTerm === '') {
            // Hide error and success alerts when clearing search
            $('#searchErrorAlert').hide();
            $('#searchSuccessAlert').hide();
            $('#coursesTableBody').html(originalCoursesHtml);
            $('#searchInfo').hide();
            $('#noSearchResults').hide();
            // Re-initialize view students buttons
            $('.view-students-btn').off('click').on('click', function() {
                const students = $(this).data('students');
                const courseTitle = $(this).data('course-title');
                $('#studentsModalLabel').text('Students Enrolled in: ' + courseTitle);

                let listHtml = '';
                if (students && students.length > 0) {
                    students.forEach(function(s) {
                        listHtml += '<li class="list-group-item">' + s.name + ' (' + s.email + ')</li>';
                    });
                } else {
                    listHtml = '<li class="list-group-item text-muted">No students enrolled.</li>';
                }

                $('#studentsList').html(listHtml);
                $('#studentsModal').modal('show');
            });
        } else {
            performServerSearch(searchTerm);
        }
    });
    <?php endif; ?>

    // Enroll button
    $('.enroll-btn').each(function() {
        const button = $(this);
        const courseId = button.data('course-id');

        // Disable button if already enrolled (in case of page refresh)
        if (enrolledCourseIds.has(courseId)) {
            button.prop('disabled', true)
                  .removeClass('btn-success')
                  .addClass('btn-secondary')
                  .text('Enrolled');
        }
    });

    $('.enroll-btn').click(function() {
        const courseId = $(this).data('course-id');
        const button = $(this);
        const courseTitle = button.closest('li').find('.course-title').text().trim();

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
                    $('#enrolledCourses').append('<li class="list-group-item" data-course-id="' + courseId + '">' + courseTitle + '</li>');
                    enrolledCourseIds.add(courseId);

                    // Disable the enroll button
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
