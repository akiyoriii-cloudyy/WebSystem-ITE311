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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h6>My Enrolled Courses</h6>
                    <h3><?= esc($stats['my_courses'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h6>Total Assignments</h6>
                    <h3><?= esc($stats['total_assignments'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h6>Total Quizzes</h6>
                    <h3><?= esc($stats['total_quizzes'] ?? 0) ?></h3>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <a href="<?= site_url('announcements') ?>" class="btn btn-info btn-sm me-2">Announcements</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Enrolled Courses Table -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white fw-bold">My Enrolled Courses</div>
            <div class="card-body">
                <!-- ✅ Search Form for Enrolled Courses -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="searchEnrolledCoursesForm" method="GET" action="javascript:void(0);">
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
                                            id="searchEnrolledCoursesInput" 
                                            name="q" 
                                            placeholder="Search courses by code, name, instructor, academic year, semester, or term..." 
                                            value=""
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100" id="searchEnrolledCoursesBtn">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ✅ Search Results Info -->
                <div id="searchEnrolledCoursesInfo" class="mb-3" style="display: none;">
                    <p class="text-muted">
                        <span id="enrolledCoursesResultCount">0</span> result(s) found
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="enrolledCoursesTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Instructor</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Term</th>
                                <th>Assignments</th>
                                <th>Quizzes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="enrolledCoursesTableBody">
                            <?php if (!empty($enrolledCourses)): ?>
                                <?php foreach ($enrolledCourses as $index => $course): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?php if (!empty($course['course_number'])): ?>
                                                <span class="badge bg-secondary"><?= esc($course['course_number']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($course['title'] ?? $course['name']) ?></td>
                                        <td><?= esc($course['instructor_name'] ?? 'N/A') ?></td>
                                        <td><?= esc($course['acad_year'] ?? 'N/A') ?></td>
                                        <td><?= esc($course['semester_name'] ?? 'N/A') ?></td>
                                        <td><?= esc($course['term_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= esc($course['assignment_count'] ?? 0) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= esc($course['quiz_count'] ?? 0) ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-primary" href="<?= base_url('materials/course/' . (int)$course['course_id']) ?>">
                                                    <i class="bi bi-file-earmark"></i> Materials
                                                </a>
                                                <a class="btn btn-sm btn-info" href="<?= site_url('student/assignments/course/' . (int)$course['course_id']) ?>">
                                                    <i class="bi bi-file-text"></i> Assignments
                                                </a>
                                                <a class="btn btn-sm btn-success" href="<?= site_url('student/quiz/course/' . (int)$course['course_id']) ?>">
                                                    <i class="bi bi-question-circle"></i> Quizzes
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        <p class="mb-0">You are not enrolled in any course yet. Your teacher or admin will enroll you in courses.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="noEnrolledCoursesSearchResults" style="display: none;">
                    <p class="text-muted mb-0 text-center">No courses found matching your search.</p>
                </div>
            </div>
        </div>

        <!-- My Class Schedules -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white fw-bold">My Class Schedules</div>
            <div class="card-body">
                <!-- ✅ Search Form for Schedules -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="searchSchedulesForm" method="GET" action="javascript:void(0);">
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
                                            id="searchSchedulesInput" 
                                            name="q" 
                                            placeholder="Search schedules by course, instructor, day, room, or class type..." 
                                            value=""
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100" id="searchSchedulesBtn">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ✅ Search Results Info -->
                <div id="searchSchedulesInfo" class="mb-3" style="display: none;">
                    <p class="text-muted">
                        <span id="schedulesResultCount">0</span> result(s) found
                    </p>
                </div>

                <?php if (!empty($schedules)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="schedulesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Class Type</th>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Room/Location</th>
                                    <th>Meeting Link</th>
                                </tr>
                            </thead>
                            <tbody id="schedulesTableBody">
                                <?php foreach ($schedules as $index => $schedule): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= esc($schedule['course_title']) ?></strong>
                                            <?php if (!empty($schedule['course_number'])): ?>
                                                <br><small class="text-muted"><?= esc($schedule['course_number']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($schedule['instructor_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($schedule['class_type'] === 'online'): ?>
                                                <span class="badge bg-info">Online</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Face-to-Face</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= esc($schedule['day_of_week']) ?></strong></td>
                                        <td>
                                            <strong class="text-success"><?= date('h:i A', strtotime($schedule['start_time'])) ?></strong>
                                        </td>
                                        <td>
                                            <strong class="text-danger"><?= date('h:i A', strtotime($schedule['end_time'])) ?></strong>
                                        </td>
                                        <td><?= esc($schedule['room'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (!empty($schedule['meeting_link'])): ?>
                                                <a href="<?= esc($schedule['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="bi bi-link-45deg"></i> Join
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="noSchedulesSearchResults" style="display: none;">
                        <p class="text-muted mb-0 text-center">No schedules found matching your search.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mb-0">
                        <p class="mb-0">No class schedules available. Schedules will appear here once your courses have been scheduled by the admin.</p>
                    </div>
                <?php endif; ?>
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

    // ✅ ENROLLED COURSES SEARCH FUNCTIONALITY (Student)
    <?php if ($user_role === 'student'): ?>
    let enrolledCoursesSearchTimeout;
    let isSearchingEnrolledCourses = false;
    const originalEnrolledCoursesHtml = $('#enrolledCoursesTableBody').html(); // Store original courses
    
    function performEnrolledCoursesSearch(searchTerm) {
        if (isSearchingEnrolledCourses) return; // Prevent multiple simultaneous searches
        
        isSearchingEnrolledCourses = true;
        const $searchBtn = $('#searchEnrolledCoursesBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('student/search/enrolled-courses') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingEnrolledCourses = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                console.log('Enrolled courses search response:', response); // Debug log

                if (response.status === 'success') {
                    // Update search info
                    $('#enrolledCoursesResultCount').text(response.count);
                    $('#searchEnrolledCoursesInfo').show();

                    // Clear existing courses
                    $('#enrolledCoursesTableBody').empty();
                    $('#noEnrolledCoursesSearchResults').hide();

                    if (response.results && response.results.length > 0) {
                        // Build table rows from search results
                        response.results.forEach(function(course, index) {
                            const courseCodeHtml = course.course_number 
                                ? '<span class="badge bg-secondary">' + course.course_number + '</span>'
                                : '<span class="text-muted">-</span>';
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + courseCodeHtml + '</td>' +
                                '<td>' + (course.title || course.name || 'Course #' + course.course_id) + '</td>' +
                                '<td>' + (course.instructor_name || 'N/A') + '</td>' +
                                '<td>' + (course.acad_year || 'N/A') + '</td>' +
                                '<td>' + (course.semester_name || 'N/A') + '</td>' +
                                '<td>' + (course.term_name || 'N/A') + '</td>' +
                                '<td><span class="badge bg-info">' + (course.assignment_count || 0) + '</span></td>' +
                                '<td><span class="badge bg-success">' + (course.quiz_count || 0) + '</span></td>' +
                                '<td>' +
                                '<div class="btn-group" role="group">' +
                                '<a class="btn btn-sm btn-primary" href="<?= base_url("materials/course/") ?>' + course.course_id + '">' +
                                '<i class="bi bi-file-earmark"></i> Materials</a> ' +
                                '<a class="btn btn-sm btn-info" href="<?= site_url("student/assignments/course/") ?>' + course.course_id + '">' +
                                '<i class="bi bi-file-text"></i> Assignments</a> ' +
                                '<a class="btn btn-sm btn-success" href="<?= site_url("student/quiz/course/") ?>' + course.course_id + '">' +
                                '<i class="bi bi-question-circle"></i> Quizzes</a>' +
                                '</div>' +
                                '</td>' +
                            '</tr>';
                            $('#enrolledCoursesTableBody').append(row);
                        });
                    } else {
                        $('#noEnrolledCoursesSearchResults').show();
                    }
                } else {
                    $('#searchEnrolledCoursesInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingEnrolledCourses = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchEnrolledCoursesInfo').hide();
                
                console.error('Search error:', error);
                console.error('XHR status:', xhr.status);
                console.error('Response text:', xhr.responseText);
                
                let errorMsg = 'An error occurred during search. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                    } catch (e) {
                        console.error('Could not parse error response');
                    }
                }
                
                alert('Search Error: ' + errorMsg);
            }
        });
    }

    $('#searchEnrolledCoursesInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(enrolledCoursesSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(enrolledCoursesSearchTimeout);
            enrolledCoursesSearchTimeout = setTimeout(function() {
                $('#enrolledCoursesTableBody').html(originalEnrolledCoursesHtml);
                $('#searchEnrolledCoursesInfo').hide();
                $('#noEnrolledCoursesSearchResults').hide();
            }, 300);
        } else {
            enrolledCoursesSearchTimeout = setTimeout(function() {
                performEnrolledCoursesSearch(searchTerm);
            }, 500);
        }
    });

    $('#searchEnrolledCoursesForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(enrolledCoursesSearchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchEnrolledCoursesInput').val().trim();
        if (searchTerm === '') {
            $('#enrolledCoursesTableBody').html(originalEnrolledCoursesHtml);
            $('#searchEnrolledCoursesInfo').hide();
            $('#noEnrolledCoursesSearchResults').hide();
        } else {
            performEnrolledCoursesSearch(searchTerm);
        }
    });

    // Also handle search button click directly
    $('#searchEnrolledCoursesBtn').on('click', function(e) {
        e.preventDefault();
        $('#searchEnrolledCoursesForm').submit();
    });

    // ✅ SCHEDULES SEARCH FUNCTIONALITY (Student)
    let schedulesSearchTimeout;
    let isSearchingSchedules = false;
    const originalSchedulesHtml = $('#schedulesTableBody').html(); // Store original schedules
    
    function performSchedulesSearch(searchTerm) {
        if (isSearchingSchedules) return;
        
        isSearchingSchedules = true;
        const $searchBtn = $('#searchSchedulesBtn');
        const originalBtnText = $searchBtn.html();
        
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('student/search/schedules') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingSchedules = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    $('#schedulesResultCount').text(response.count);
                    $('#searchSchedulesInfo').show();
                    $('#schedulesTableBody').empty();
                    $('#noSchedulesSearchResults').hide();

                    if (response.results.length > 0) {
                        response.results.forEach(function(schedule, index) {
                            const classTypeBadge = schedule.class_type === 'online' 
                                ? '<span class="badge bg-info">Online</span>'
                                : '<span class="badge bg-primary">Face-to-Face</span>';
                            
                            const courseNumberHtml = schedule.course_number 
                                ? '<br><small class="text-muted">' + schedule.course_number + '</small>'
                                : '';
                            
                            // Format time
                            function formatTime(timeStr) {
                                if (!timeStr) return 'N/A';
                                try {
                                    const time = timeStr.split(':');
                                    const hours = parseInt(time[0]);
                                    const minutes = time[1] || '00';
                                    const ampm = hours >= 12 ? 'PM' : 'AM';
                                    const displayHours = hours % 12 || 12;
                                    return (displayHours < 10 ? '0' : '') + displayHours + ':' + minutes + ' ' + ampm;
                                } catch (e) {
                                    return timeStr;
                                }
                            }
                            const startTime = formatTime(schedule.start_time);
                            const endTime = formatTime(schedule.end_time);
                            
                            const meetingLinkHtml = schedule.meeting_link 
                                ? '<a href="' + schedule.meeting_link + '" target="_blank" class="btn btn-sm btn-info">' +
                                  '<i class="bi bi-link-45deg"></i> Join</a>'
                                : '<span class="text-muted">N/A</span>';
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td><strong>' + (schedule.course_title || 'N/A') + '</strong>' + courseNumberHtml + '</td>' +
                                '<td>' + (schedule.instructor_name || 'N/A') + '</td>' +
                                '<td>' + classTypeBadge + '</td>' +
                                '<td><strong>' + (schedule.day_of_week || 'N/A') + '</strong></td>' +
                                '<td><strong class="text-success">' + startTime + '</strong></td>' +
                                '<td><strong class="text-danger">' + endTime + '</strong></td>' +
                                '<td>' + (schedule.room || 'N/A') + '</td>' +
                                '<td>' + meetingLinkHtml + '</td>' +
                            '</tr>';
                            $('#schedulesTableBody').append(row);
                        });
                    } else {
                        $('#noSchedulesSearchResults').show();
                    }
                } else {
                    $('#searchSchedulesInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingSchedules = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchSchedulesInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    $('#searchSchedulesInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(schedulesSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(schedulesSearchTimeout);
            schedulesSearchTimeout = setTimeout(function() {
                $('#schedulesTableBody').html(originalSchedulesHtml);
                $('#searchSchedulesInfo').hide();
                $('#noSchedulesSearchResults').hide();
            }, 300);
        } else {
            schedulesSearchTimeout = setTimeout(function() {
                performSchedulesSearch(searchTerm);
            }, 500);
        }
    });

    $('#searchSchedulesForm').on('submit', function(e) {
        e.preventDefault();
        clearTimeout(schedulesSearchTimeout);
        const searchTerm = $('#searchSchedulesInput').val().trim();
        if (searchTerm === '') {
            $('#schedulesTableBody').html(originalSchedulesHtml);
            $('#searchSchedulesInfo').hide();
            $('#noSchedulesSearchResults').hide();
        } else {
            performSchedulesSearch(searchTerm);
        }
    });
    <?php endif; ?>
});
</script>

<?= $this->include('template/footer') ?>
