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
        <h2 class="fw-bold mb-2">Welcome, <?= esc($user_name ?? 'User') ?> 🎉</h2>
        <p class="mb-0">Role: <strong><?= ucfirst(esc($user_role ?? '')) ?></strong></p>
    </div>

    <?php if ($user_role === 'admin'): ?>
        <!-- ✅ ADMIN DASHBOARD -->
        <div class="row mb-4">
            <div class="col-md-3"><div class="card text-center p-3"><h6>Total Users</h6><h3><?= esc($stats['total_users'] ?? 0) ?></h3></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><h6>Total Courses</h6><h3><?= esc($stats['total_courses'] ?? 0) ?></h3></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><h6>Active Students</h6><h3><?= esc($stats['active_students'] ?? 0) ?></h3></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><h6>Active Teachers</h6><h3><?= esc($stats['active_teachers'] ?? 0) ?></h3></div></div>
        </div>

        <div class="card mt-4">
            <div class="card-header fw-bold">System Overview</div>
            <div class="card-body">
                <h6>Total Users: <?= esc($stats['total_users'] ?? 0) ?></h6>
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
        <!-- ✅ TEACHER DASHBOARD -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-center p-3">
                    <h5>My Courses</h5>
                    <h3><?= esc($stats['my_courses'] ?? 0) ?></h3>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header fw-bold">My Courses & Enrolled Students</div>
            <div class="card-body">
                <?php if (!empty($courses)): ?>
                    <table class="table table-bordered table-striped align-middle">
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
                                    ->select('users.name, users.email')
                                    ->join('users', 'users.id = enrollments.user_id')
                                    ->where('enrollments.course_id', $courseId)
                                    ->get()->getResultArray();
                            ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($c['name'] ?? $c['title'] ?? 'Unnamed Course') ?></td>
                                    <td><?= count($students) ?></td>
                                    <td>
                                        <?php if (count($students) > 0): ?>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#students-<?= $courseId ?>">View Students</button>
                                        <?php else: ?>
                                            <span class="text-muted">No Enrolled Students</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <?php if (count($students) > 0): ?>
                                    <tr id="students-<?= $courseId ?>" class="collapse bg-light">
                                        <td colspan="4">
                                            <ul class="mb-0">
                                                <?php foreach ($students as $s): ?>
                                                    <li><?= esc($s['name']) ?> (<?= esc($s['email']) ?>)</li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted mb-0">No courses found.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($user_role === 'student'): ?>
        <!-- ✅ STUDENT DASHBOARD -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-center p-3">
                    <h5>My Enrolled Courses</h5>
                    <h3><?= esc($stats['my_courses'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center p-3">
                    <h5>Upcoming Deadlines</h5>
                    <h3><?= esc(isset($deadlines) ? count($deadlines) : 0) ?></h3>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">My Enrolled Courses</div>
            <ul class="list-group list-group-flush" id="enrolledCourses">
                <?php if (!empty($enrolledCourses)): ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <li class="list-group-item"><?= esc($course['title'] ?? $course['name'] ?? 'Unknown Course') ?></li>
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
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="course-title"><?= esc($course['title'] ?? $course['name'] ?? 'Unknown Course') ?></span>
                            <button class="btn btn-sm btn-success enroll-btn" data-course-id="<?= esc($course['id']) ?>">Enroll</button>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted">No available courses.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div id="alertBox" class="alert mt-3 d-none"></div>

        <div class="alert alert-info mt-4">
            Welcome to your student dashboard! Use the sidebar to navigate to your courses and deadlines.
        </div>
    <?php endif; ?>
</div>

<!-- ✅ jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Enroll AJAX -->
<script>
$(document).ready(function() {
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
                    $('#alertBox').removeClass('d-none alert-danger').addClass('alert alert-success').text(response.message);
                    button.prop('disabled', true).text('Enrolled');
                    $('.no-enrollment-msg').remove();
                    $('#enrolledCourses').append('<li class="list-group-item">' + courseTitle + '</li>');
                } else {
                    $('#alertBox').removeClass('d-none alert-success').addClass('alert alert-danger').text(response.message);
                }
            },
            error: function() {
                $('#alertBox').removeClass('d-none alert-success').addClass('alert alert-danger').text('An error occurred. Please try again.');
            }
        });
    });
});
</script>

<!-- ✅ Role Change AJAX (Now linked to Auth::dashboard) -->
<script>
$(document).on('change', '.role-select', function() {
    const userId = $(this).data('user-id');
    const newRole = $(this).val();
    const row = $(this).closest('tr');

    $.ajax({
        url: "<?= base_url('dashboard') ?>",
        type: "POST",
        data: {
            id: userId,  // match controller key
            role: newRole,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                row.find('td:nth-child(3)').html('<strong>' + newRole.charAt(0).toUpperCase() + newRole.slice(1) + '</strong>');
                alert('✅ Role updated successfully!');
            } else {
                alert('❌ ' + (res.message || 'Failed to update role.'));
            }
        },
        error: function() {
            alert('❌ Server error. Please try again.');
        }
    });
});
</script>

<?= $this->include('template/footer') ?>
