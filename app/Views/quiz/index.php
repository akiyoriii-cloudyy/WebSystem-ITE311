<?= $this->include('template/header') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Quizzes - <?= esc($course['title']) ?></h2>
        <a href="<?= site_url('quiz/create/' . $course['id']) ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Quiz
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($quizzes)): ?>
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
                            <?php foreach ($quizzes as $index => $quiz): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= esc($quiz['title']) ?></strong></td>
                                    <td><?= esc($quiz['description'] ?? 'N/A') ?></td>
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
                                            <a href="<?= site_url('quiz/delete/' . $quiz['id']) ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this quiz?')">
                                                Delete
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
                    <p class="mb-0">No quizzes found for this course.</p>
                    <a href="<?= site_url('quiz/create/' . $course['id']) ?>" class="btn btn-primary mt-2">Create First Quiz</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= site_url('teacher/courses') ?>" class="btn btn-secondary">Back to Courses</a>
    </div>
</div>

<?= $this->include('template/footer') ?>

