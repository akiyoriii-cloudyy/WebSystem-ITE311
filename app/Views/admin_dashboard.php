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

        <div class="card mt-4">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span>Courses Overview</span>
                <a href="<?= site_url('admin/courses') ?>" class="btn btn-sm btn-primary">Manage Courses</a>
            </div>
            <div class="card-body">
                <?php if (!empty($courses)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Course Code</th>
                                    <th>Course Title</th>
                                    <th>Instructor</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                            <a class="btn btn-sm btn-info" href="<?= site_url('admin/courses') ?>">Manage</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No courses found. <a href="<?= site_url('admin/courses') ?>">Create a course</a></p>
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
