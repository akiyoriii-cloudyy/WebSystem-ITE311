<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">All Quizzes & Submissions</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Quizzes Overview</h5>
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
                                <th>Description</th>
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
                                            <a href="<?= site_url('quiz/course/' . $quiz['course_id']) ?>" class="btn btn-sm btn-info">
                                                View Course Quizzes
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
                    <p class="mb-0">No quizzes found in the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?= $this->include('template/footer') ?>

