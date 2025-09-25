<?= $this->include('template/header') ?>

<?php if ($user_role === 'admin'): ?>
    <!-- Admin Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Total Users</h6>
                <h3><?= esc($stats['total_users'] ?? 0) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Total Courses</h6>
                <h3><?= esc($stats['total_courses'] ?? 0) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Active Students</h6>
                <h3><?= esc($stats['active_students'] ?? 0) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h6>Active Teachers</h6>
                <h3><?= esc($stats['active_teachers'] ?? 0) ?></h3>
            </div>
        </div>
    </div>

    <!-- Admin System Overview -->
    <div class="card mt-4">
        <div class="card-header fw-bold">System Overview</div>
        <div class="card-body">
            <h6>Total Users: <?= esc($stats['total_users'] ?? 0) ?></h6>
            <ul class="mb-0">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <li><?= esc($u['name']) ?> (<?= esc($u['email']) ?>) - <strong><?= esc(ucfirst($u['role'])) ?></strong></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No users found.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

<?php elseif ($user_role === 'teacher'): ?>
    <!-- Teacher Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center p-3">
                <h5>My Courses</h5>
                <h3><?= esc($stats['my_courses'] ?? 0) ?></h3>
            </div>
        </div>
    </div>

    <!-- Courses Table -->
    <div class="card mt-4">
        <div class="card-header fw-bold">My Courses</div>
        <div class="card-body">
            <?php if (!empty($courses)): ?>
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Course Name</th>
                            <th>Students Enrolled</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $index => $c): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= esc($c['name'] ?? $c['course_name']) ?></td>
                                <td><?= esc($c['enrolled_students'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted mb-0">No courses found.</p>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($user_role === 'student'): ?>
    <!-- Student Stats -->
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

    <div class="alert alert-info">
        Welcome to your student dashboard! Use the sidebar to navigate to your courses and deadlines.
    </div>
<?php endif; ?>

<?= $this->include('template/footer') ?>
