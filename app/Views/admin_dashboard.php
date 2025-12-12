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

        <!-- Quick Access Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">📚 Academic Structure</h5>
                        <p class="card-text">Manage Academic Years, Semesters, and Terms</p>
                        <div class="d-flex flex-column gap-2">
                            <a href="<?= site_url('admin/academic/years') ?>" class="btn btn-primary btn-sm">Academic Years</a>
                            <div class="d-flex gap-2">
                                <a href="<?= site_url('admin/academic/semesters') ?>" class="btn btn-primary btn-sm flex-fill">Semesters</a>
                                <a href="<?= site_url('admin/academic/terms') ?>" class="btn btn-primary btn-sm flex-fill">Terms</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">🏢 Department & Programs</h5>
                        <p class="card-text">Manage Departments and Academic Programs</p>
                        <div class="d-flex gap-2">
                            <a href="<?= site_url('admin/departments') ?>" class="btn btn-success btn-sm flex-fill">Departments</a>
                            <a href="<?= site_url('admin/programs') ?>" class="btn btn-success btn-sm flex-fill">Programs</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">👥 Enrollment Management</h5>
                        <p class="card-text">Enroll students and teachers, assign teachers to courses</p>
                        <a href="<?= site_url('admin/enrollments') ?>" class="btn btn-info btn-sm">Manage Enrollments</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">📝 Quizzes & Submissions</h5>
                        <p class="card-text">Manage Quizzes and View All Submissions</p>
                        <a href="<?= site_url('admin/quizzes') ?>" class="btn btn-success btn-sm w-100">View All Quizzes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">📅 Course Schedules</h5>
                        <p class="card-text">Manage class schedules, online and face-to-face sessions</p>
                        <a href="<?= site_url('admin/schedules') ?>" class="btn btn-warning btn-sm w-100">Manage Schedules</a>
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

        <div class="card mt-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>Courses Overview</span>
                <a href="<?= site_url('admin/courses') ?>" class="btn btn-sm btn-primary">Manage Courses</a>
            </div>
            <div class="card-body">
                <?php if (!empty($courses)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="coursesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Course Code</th>
                                    <th>Course Title</th>
                                    <th>Instructor</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="coursesTableBody">
                                <?php foreach ($courses as $i => $c): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <?php 
                                            $courseCode = $c['course_number'] ?? $c['course_code'] ?? $c['code'] ?? '';
                                            if (!empty($courseCode)): ?>
                                                <span class="badge bg-secondary"><?= esc($courseCode) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($c['title'] ?? $c['name'] ?? ('Course #' . $c['id'])) ?></td>
                                        <td><?= esc($c['instructor_name'] ?? 'Not Assigned') ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="<?= base_url('admin/course/' . $c['id'] . '/upload') ?>">Materials</a>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editCourse(<?= $c['id'] ?>)" title="Edit Course">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <a class="btn btn-sm btn-info" href="<?= site_url('admin/courses') ?>">Manage</a>
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
                    <p class="text-muted mb-0">No courses found. <a href="<?= site_url('admin/courses') ?>">Create a course</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Course Schedules Overview -->
        <div class="card mt-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>Course Schedules Overview</span>
                <a href="<?= site_url('admin/schedules') ?>" class="btn btn-sm btn-warning">Manage Schedules</a>
            </div>
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
                                            placeholder="Search schedules by course, instructor, day, or room..." 
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

                <!-- ✅ Search Results Info for Schedules -->
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
                                    <th>Actions</th>
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
                                        <td><?= esc($schedule['day_of_week']) ?></td>
                                        <td>
                                            <strong><?= date('h:i A', strtotime($schedule['start_time'])) ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= date('h:i A', strtotime($schedule['end_time'])) ?></strong>
                                        </td>
                                        <td><?= esc($schedule['room'] ?? 'N/A') ?></td>
                                        <td>
                                            <a href="<?= site_url('admin/schedules') ?>" class="btn btn-sm btn-info">Manage</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="noSchedulesSearchResults" style="display: none;">
                        <p class="text-muted mb-0">No schedules found matching your search.</p>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No schedules found. <a href="<?= site_url('admin/schedules') ?>">Create a schedule</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Users Overview -->
        <div class="card mt-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>Users Overview</span>
                <a href="<?= site_url('admin/users') ?>" class="btn btn-sm btn-primary">Manage Users</a>
            </div>
            <div class="card-body">
                <!-- ✅ Search Form for Users -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="searchUsersForm" method="GET" action="javascript:void(0);">
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
                                            id="searchUsersInput" 
                                            name="q" 
                                            placeholder="Search users by name, email, or role..." 
                                            value=""
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100" id="searchUsersBtn">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ✅ Search Results Info for Users -->
                <div id="searchUsersInfo" class="mb-3" style="display: none;">
                    <p class="text-muted">
                        <span id="usersResultCount">0</span> result(s) found
                    </p>
                </div>

                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <?php foreach ($users as $index => $user): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= esc($user['name'] ?? 'N/A') ?></td>
                                        <td><?= esc($user['email'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php 
                                            $role = strtolower($user['role'] ?? '');
                                            $badgeClass = $role === 'admin' ? 'bg-danger' : ($role === 'teacher' ? 'bg-primary' : 'bg-success');
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= ucfirst(esc($role)) ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status = strtolower($user['status'] ?? 'active');
                                            $statusBadgeClass = $status === 'active' ? 'bg-success' : ($status === 'inactive' ? 'bg-warning' : 'bg-secondary');
                                            ?>
                                            <span class="badge <?= $statusBadgeClass ?>"><?= ucfirst(esc($status)) ?></span>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('admin/users') ?>" class="btn btn-sm btn-info">Manage</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="noUsersSearchResults" style="display: none;">
                        <p class="text-muted mb-0">No users found matching your search.</p>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No users found. <a href="<?= site_url('admin/users') ?>">Create a user</a></p>
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

    // ✅ AUTOMATIC SERVER-SIDE SEARCH (Debounced - for admin role)
    <?php if ($user_role === 'admin'): ?>
    let searchTimeout;
    let isSearching = false;
    const originalCoursesHtml = $('#coursesTableBody').html(); // Store original courses
    
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
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + courseCodeHtml + '</td>' +
                                '<td>' + course.title + '</td>' +
                                '<td>' + (course.instructor_name || 'Not Assigned') + '</td>' +
                                '<td>' +
                                    '<a class="btn btn-sm btn-primary" href="<?= base_url("admin/course/") ?>' + course.id + '/upload">Materials</a> ' +
                                    '<a class="btn btn-sm btn-info" href="<?= site_url("admin/courses") ?>">Manage</a>' +
                                '</td>' +
                            '</tr>';
                            $('#coursesTableBody').append(row);
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
        } else {
            performServerSearch(searchTerm);
        }
    });

    // ✅ SCHEDULES SEARCH FUNCTIONALITY
    let schedulesSearchTimeout;
    let isSearchingSchedules = false;
    const originalSchedulesHtml = $('#schedulesTableBody').html(); // Store original schedules
    
    function performSchedulesSearch(searchTerm) {
        if (isSearchingSchedules) return; // Prevent multiple simultaneous searches
        
        isSearchingSchedules = true;
        const $searchBtn = $('#searchSchedulesBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('admin/search/schedules') ?>",
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
                    // Update search info
                    $('#schedulesResultCount').text(response.count);
                    $('#searchSchedulesInfo').show();

                    // Clear existing schedules
                    $('#schedulesTableBody').empty();
                    $('#noSchedulesSearchResults').hide();

                    if (response.results.length > 0) {
                        // Build table rows from search results
                        response.results.forEach(function(schedule, index) {
                            const classTypeBadge = schedule.class_type === 'online' 
                                ? '<span class="badge bg-info">Online</span>'
                                : '<span class="badge bg-primary">Face-to-Face</span>';
                            
                            const courseNumberHtml = schedule.course_number 
                                ? '<br><small class="text-muted">' + schedule.course_number + '</small>'
                                : '';
                            
                            // Format time (handle HH:MM:SS or HH:MM format)
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
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td><strong>' + schedule.course_title + '</strong>' + courseNumberHtml + '</td>' +
                                '<td>' + (schedule.instructor_name || 'N/A') + '</td>' +
                                '<td>' + classTypeBadge + '</td>' +
                                '<td>' + schedule.day_of_week + '</td>' +
                                '<td><strong>' + startTime + '</strong></td>' +
                                '<td><strong>' + endTime + '</strong></td>' +
                                '<td>' + (schedule.room || 'N/A') + '</td>' +
                                '<td><a href="<?= site_url("admin/schedules") ?>" class="btn btn-sm btn-info">Manage</a></td>' +
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

    // Automatic search as user types (with debouncing) for schedules
    $('#searchSchedulesInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(schedulesSearchTimeout);
        
        if (searchTerm === '') {
            // If search is empty, restore original schedules
            clearTimeout(schedulesSearchTimeout);
            schedulesSearchTimeout = setTimeout(function() {
                $('#schedulesTableBody').html(originalSchedulesHtml);
                $('#searchSchedulesInfo').hide();
                $('#noSchedulesSearchResults').hide();
            }, 300);
        } else {
            // Debounce: wait 500ms after user stops typing before searching
            schedulesSearchTimeout = setTimeout(function() {
                performSchedulesSearch(searchTerm);
            }, 500);
        }
    });

    // Submit form for schedules search
    $('#searchSchedulesForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(schedulesSearchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchSchedulesInput').val().trim();
        if (searchTerm === '') {
            $('#schedulesTableBody').html(originalSchedulesHtml);
            $('#searchSchedulesInfo').hide();
            $('#noSchedulesSearchResults').hide();
        } else {
            performSchedulesSearch(searchTerm);
        }
    });

    // ✅ USERS SEARCH FUNCTIONALITY
    let usersSearchTimeout;
    let isSearchingUsers = false;
    const originalUsersHtml = $('#usersTableBody').html(); // Store original users
    
    function performUsersSearch(searchTerm) {
        if (isSearchingUsers) return; // Prevent multiple simultaneous searches
        
        isSearchingUsers = true;
        const $searchBtn = $('#searchUsersBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('admin/search/users') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingUsers = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    // Update search info
                    $('#usersResultCount').text(response.count);
                    $('#searchUsersInfo').show();

                    // Clear existing users
                    $('#usersTableBody').empty();
                    $('#noUsersSearchResults').hide();

                    if (response.results.length > 0) {
                        // Build table rows from search results
                        response.results.forEach(function(user, index) {
                            const role = (user.role || '').toLowerCase();
                            const badgeClass = role === 'admin' ? 'bg-danger' : (role === 'teacher' ? 'bg-primary' : 'bg-success');
                            
                            const status = (user.status || 'active').toLowerCase();
                            const statusBadgeClass = status === 'active' ? 'bg-success' : (status === 'inactive' ? 'bg-warning' : 'bg-secondary');
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + (user.name || 'N/A') + '</td>' +
                                '<td>' + (user.email || 'N/A') + '</td>' +
                                '<td><span class="badge ' + badgeClass + '">' + (role.charAt(0).toUpperCase() + role.slice(1)) + '</span></td>' +
                                '<td><span class="badge ' + statusBadgeClass + '">' + (status.charAt(0).toUpperCase() + status.slice(1)) + '</span></td>' +
                                '<td><a href="<?= site_url("admin/users") ?>" class="btn btn-sm btn-info">Manage</a></td>' +
                            '</tr>';
                            $('#usersTableBody').append(row);
                        });
                    } else {
                        $('#noUsersSearchResults').show();
                    }
                } else {
                    $('#searchUsersInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingUsers = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchUsersInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    // Automatic search as user types (with debouncing) for users
    $('#searchUsersInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(usersSearchTimeout);
        
        if (searchTerm === '') {
            // If search is empty, restore original users
            clearTimeout(usersSearchTimeout);
            usersSearchTimeout = setTimeout(function() {
                $('#usersTableBody').html(originalUsersHtml);
                $('#searchUsersInfo').hide();
                $('#noUsersSearchResults').hide();
            }, 300);
        } else {
            // Debounce: wait 500ms after user stops typing before searching
            usersSearchTimeout = setTimeout(function() {
                performUsersSearch(searchTerm);
            }, 500);
        }
    });

    // Submit form for users search
    $('#searchUsersForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(usersSearchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchUsersInput').val().trim();
        if (searchTerm === '') {
            $('#usersTableBody').html(originalUsersHtml);
            $('#searchUsersInfo').hide();
            $('#noUsersSearchResults').hide();
        } else {
            performUsersSearch(searchTerm);
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

// Edit Course Function
function editCourse(courseId) {
    // Fetch course data
    fetch('<?= site_url('admin/courses/get') ?>/' + courseId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.course) {
            const course = data.course;
            const dropdowns = data.dropdowns || {};
            
            // Populate dropdowns
            if (dropdowns.teachers) {
                const instructorSelect = document.getElementById('edit_course_instructor_id');
                instructorSelect.innerHTML = '<option value="">-- Select Instructor (Optional) --</option>';
                dropdowns.teachers.forEach(teacher => {
                    const option = document.createElement('option');
                    option.value = teacher.id;
                    option.textContent = teacher.name + ' (' + teacher.email + ')';
                    instructorSelect.appendChild(option);
                });
            }
            
            if (dropdowns.acadYears) {
                const acadYearSelect = document.getElementById('edit_course_acad_year_id');
                acadYearSelect.innerHTML = '<option value="">-- Select Academic Year (Optional) --</option>';
                dropdowns.acadYears.forEach(year => {
                    const option = document.createElement('option');
                    option.value = year.id;
                    option.textContent = year.acad_year;
                    acadYearSelect.appendChild(option);
                });
            }
            
            if (dropdowns.semesters) {
                const semesterSelect = document.getElementById('edit_course_semester_id');
                semesterSelect.innerHTML = '<option value="">-- Select Semester (Optional) --</option>';
                dropdowns.semesters.forEach(semester => {
                    const option = document.createElement('option');
                    option.value = semester.id;
                    option.textContent = semester.semester + (semester.acad_year ? ' (' + semester.acad_year + ')' : '');
                    semesterSelect.appendChild(option);
                });
            }
            
            if (dropdowns.terms) {
                const termSelect = document.getElementById('edit_course_term_id');
                termSelect.innerHTML = '<option value="">-- Select Term (Optional) --</option>';
                dropdowns.terms.forEach(term => {
                    const option = document.createElement('option');
                    option.value = term.id;
                    option.setAttribute('data-semester-id', term.semester_id || '');
                    option.textContent = term.term + (term.semester ? ' - ' + term.semester : '');
                    termSelect.appendChild(option);
                });
            }
            
            if (dropdowns.departments) {
                const deptSelect = document.getElementById('edit_course_department_id');
                deptSelect.innerHTML = '<option value="">-- Select Department (Optional) --</option>';
                dropdowns.departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.department_code + ' - ' + dept.department_name;
                    deptSelect.appendChild(option);
                });
            }
            
            if (dropdowns.programs) {
                const progSelect = document.getElementById('edit_course_program_id');
                progSelect.innerHTML = '<option value="">-- Select Program (Optional) --</option>';
                dropdowns.programs.forEach(prog => {
                    const option = document.createElement('option');
                    option.value = prog.id;
                    option.setAttribute('data-department-id', prog.department_id || '');
                    option.textContent = prog.program_code + ' - ' + prog.program_name;
                    progSelect.appendChild(option);
                });
            }
            
            // Populate form fields
            document.getElementById('edit_course_id').value = course.id;
            document.getElementById('edit_course_title').value = course.title || '';
            document.getElementById('edit_course_number').value = course.course_number || '';
            document.getElementById('edit_course_description').value = course.description || '';
            document.getElementById('edit_course_units').value = course.units || '';
            document.getElementById('edit_course_instructor_id').value = course.instructor_id || '';
            document.getElementById('edit_course_acad_year_id').value = course.acad_year_id || '';
            document.getElementById('edit_course_semester_id').value = course.semester_id || '';
            document.getElementById('edit_course_term_id').value = course.term_id || '';
            document.getElementById('edit_course_department_id').value = course.department_id || '';
            document.getElementById('edit_course_program_id').value = course.program_id || '';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
            modal.show();
        } else {
            alert('Failed to load course data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load course data.');
    });
}
</script>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCourseForm">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_course_id" name="course_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_title" class="form-label">Course Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_course_title" name="title" required 
                                   placeholder="e.g., ITE 321 - Web Application Development" maxlength="255">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_course_number" class="form-label">Course Number/Code</label>
                            <input type="text" class="form-control" id="edit_course_number" name="course_number" 
                                   placeholder="e.g., ITE321, CS101" maxlength="50">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="edit_course_units" class="form-label">Units</label>
                            <input type="number" class="form-control" id="edit_course_units" name="units" 
                                   placeholder="e.g., 3" min="0" max="10" step="1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_course_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_course_description" name="description" rows="3" 
                                  placeholder="Enter course description..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_instructor_id" class="form-label">Instructor</label>
                            <select class="form-select" id="edit_course_instructor_id" name="instructor_id">
                                <option value="">-- Select Instructor (Optional) --</option>
                                <?php if (!empty($teachers ?? [])): ?>
                                    <?php foreach ($teachers ?? [] as $teacher): ?>
                                        <option value="<?= esc($teacher['id']) ?>"><?= esc($teacher['name']) ?> (<?= esc($teacher['email']) ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_acad_year_id" class="form-label">Academic Year</label>
                            <select class="form-select" id="edit_course_acad_year_id" name="acad_year_id">
                                <option value="">-- Select Academic Year (Optional) --</option>
                                <?php if (!empty($acadYears ?? [])): ?>
                                    <?php foreach ($acadYears ?? [] as $year): ?>
                                        <option value="<?= esc($year['id']) ?>"><?= esc($year['acad_year']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_semester_id" class="form-label">Semester</label>
                            <select class="form-select" id="edit_course_semester_id" name="semester_id">
                                <option value="">-- Select Semester (Optional) --</option>
                                <?php if (!empty($semesters ?? [])): ?>
                                    <?php foreach ($semesters ?? [] as $semester): ?>
                                        <option value="<?= esc($semester['id']) ?>">
                                            <?= esc($semester['semester']) ?>
                                            <?php if (!empty($semester['acad_year'])): ?>
                                                (<?= esc($semester['acad_year']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_term_id" class="form-label">Term</label>
                            <select class="form-select" id="edit_course_term_id" name="term_id">
                                <option value="">-- Select Term (Optional) --</option>
                                <?php if (!empty($terms ?? [])): ?>
                                    <?php foreach ($terms ?? [] as $term): ?>
                                        <option value="<?= esc($term['id']) ?>" data-semester-id="<?= esc($term['semester_id'] ?? '') ?>">
                                            <?= esc($term['term']) ?>
                                            <?php if (!empty($term['semester'])): ?>
                                                - <?= esc($term['semester']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_department_id" class="form-label">Department</label>
                            <select class="form-select" id="edit_course_department_id" name="department_id">
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
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_program_id" class="form-label">Program</label>
                            <select class="form-select" id="edit_course_program_id" name="program_id">
                                <option value="">-- Select Program (Optional) --</option>
                                <?php if (!empty($programs ?? [])): ?>
                                    <?php foreach ($programs ?? [] as $prog): ?>
                                        <option value="<?= esc($prog['id']) ?>" data-department-id="<?= esc($prog['department_id'] ?? '') ?>">
                                            <?= esc($prog['program_code']) ?> - <?= esc($prog['program_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Update Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Course Form Submit
document.getElementById('editCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');
    const courseId = document.getElementById('edit_course_id').value;
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    const formData = new FormData(form);
    
    fetch('<?= site_url('admin/courses/update') ?>/' + courseId, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        
        if (data.status === 'success') {
            // Refresh notifications if function exists
            if (typeof fetchNotifications === 'function') {
                fetchNotifications();
            }
            
            // Update CSRF token
            if (data.csrf_token && data.csrf_hash) {
                const csrfInput = document.querySelector('input[name="' + data.csrf_token + '"]');
                if (csrfInput) {
                    csrfInput.value = data.csrf_hash;
                }
            }
            
            alert('✓ ' + data.message);
            bootstrap.Modal.getInstance(document.getElementById('editCourseModal')).hide();
            
            // Reload page after 1 second to show updated course
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('✗ Error: ' + (data.message || 'Failed to update course.'));
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>

<?= $this->include('template/footer') ?>
