<?= $this->include('template/header') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Quizzes - <?= esc($course['title']) ?></h2>
        <a href="<?= site_url('courses') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Courses
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info"><?= session()->getFlashdata('info') ?></div>
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
                                <th>Status</th>
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
                                            <?= date('M d, Y H:i', strtotime($quiz['due_date'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">No due date</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($quiz['submitted']): ?>
                                            <span class="badge bg-success">Submitted</span>
                                            <?php if (!empty($quiz['submission']['score'])): ?>
                                                <br><small>Score: <?= number_format($quiz['submission']['score'], 2) ?>/<?= number_format($quiz['max_score'] ?? 100, 2) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Not Started</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($quiz['submitted']): ?>
                                                <a href="<?= site_url('student/quiz/result/' . $quiz['id']) ?>" class="btn btn-sm btn-primary">
                                                    View Result
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= site_url('student/quiz/take/' . $quiz['id']) ?>" class="btn btn-sm btn-success">
                                                    Take Quiz
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <p class="mb-0">No quizzes available for this course yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->include('template/footer') ?>

