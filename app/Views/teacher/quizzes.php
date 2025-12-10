<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Quizzes & Submissions</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6>Total Quizzes</h6>
                <h3><?= count($quizzes) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6>Total Submissions</h6>
                <h3><?= array_sum(array_column($quizzes, 'submission_count')) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-3">
                <h6>My Courses</h6>
                <h3><?= count($courses) ?></h3>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Quick Actions</h5>
            <a href="<?= site_url('teacher/courses') ?>" class="btn btn-primary btn-sm me-2">Manage Courses</a>
            <a href="<?= site_url('teacher/announcements') ?>" class="btn btn-info btn-sm me-2">Announcements</a>
        </div>
    </div>

    <!-- Quizzes by Course -->
    <?php if (!empty($courses)): ?>
        <?php foreach ($courses as $course): ?>
            <?php
            $courseQuizzes = [];
            if (!empty($quizzes)) {
                $courseQuizzes = array_filter($quizzes, function($q) use ($course) {
                    return isset($q['course_id']) && $q['course_id'] == $course['id'];
                });
                $courseQuizzes = array_values($courseQuizzes); // Re-index array
            }
            ?>
            <?php if (!empty($courseQuizzes)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <?= esc($course['title']) ?>
                            <?php if (!empty($course['course_number'])): ?>
                                <span class="badge bg-light text-dark ms-2"><?= esc($course['course_number']) ?></span>
                            <?php endif; ?>
                            <a href="<?= site_url('quiz/course/' . $course['id']) ?>" class="btn btn-sm btn-light float-end">
                                Manage Quizzes
                            </a>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Quiz Title</th>
                                        <th>Description</th>
                                        <th>Max Score</th>
                                        <th>Due Date</th>
                                        <th>Submissions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courseQuizzes as $index => $quiz): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= esc($quiz['title']) ?></strong></td>
                                            <td><?= esc(substr($quiz['description'] ?? 'N/A', 0, 50)) ?><?= strlen($quiz['description'] ?? '') > 50 ? '...' : '' ?></td>
                                            <td><span class="badge bg-info"><?= number_format($quiz['max_score'] ?? 100, 2) ?></span></td>
                                            <td>
                                                <?php if (!empty($quiz['due_date'])): ?>
                                                    <?= date('Y-m-d H:i', strtotime($quiz['due_date'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No due date</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $quiz['submission_count'] ?? 0 ?> submission(s)</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= site_url('quiz/' . $quiz['id'] . '/submissions') ?>" class="btn btn-sm btn-primary">
                                                        View Submissions
                                                    </a>
                                                    <a href="<?= site_url('quiz/course/' . $course['id']) ?>" class="btn btn-sm btn-info">
                                                        Manage
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Show course even if no quizzes -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <?= esc($course['title']) ?>
                            <?php if (!empty($course['course_number'])): ?>
                                <span class="badge bg-light text-dark ms-2"><?= esc($course['course_number']) ?></span>
                            <?php endif; ?>
                            <a href="<?= site_url('quiz/course/' . $course['id']) ?>" class="btn btn-sm btn-light float-end">
                                Create Quiz
                            </a>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info text-center mb-0">
                            <p class="mb-2">No quizzes found for this course.</p>
                            <a href="<?= site_url('quiz/create/' . $course['id']) ?>" class="btn btn-primary btn-sm">Create First Quiz</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            <p class="mb-2">You don't have any courses assigned yet.</p>
            <p class="mb-0">Please contact the administrator to assign courses to you.</p>
        </div>
    <?php endif; ?>

    <!-- All Quizzes Table -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">All Quizzes</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($quizzes)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Quiz Title</th>
                                <th>Course</th>
                                <th>Course Number</th>
                                <th>Max Score</th>
                                <th>Due Date</th>
                                <th>Submissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $index => $quiz): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= esc($quiz['title']) ?></strong></td>
                                    <td><?= esc($quiz['course_title'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($quiz['course_number'])): ?>
                                            <span class="badge bg-secondary"><?= esc($quiz['course_number']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info"><?= number_format($quiz['max_score'] ?? 100, 2) ?></span></td>
                                    <td>
                                        <?php if (!empty($quiz['due_date'])): ?>
                                            <?= date('Y-m-d H:i', strtotime($quiz['due_date'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">No due date</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $quiz['submission_count'] ?? 0 ?> submission(s)</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= site_url('quiz/' . $quiz['id'] . '/submissions') ?>" class="btn btn-sm btn-primary">
                                                View Submissions
                                            </a>
                                            <a href="<?= site_url('quiz/course/' . $quiz['course_id']) ?>" class="btn btn-sm btn-info">
                                                Manage
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <p class="mb-0">No quizzes found. Create quizzes for your courses to get started.</p>
                    <a href="<?= site_url('teacher/courses') ?>" class="btn btn-primary mt-2">Go to Courses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= site_url('teacher/dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?= $this->include('template/footer') ?>

