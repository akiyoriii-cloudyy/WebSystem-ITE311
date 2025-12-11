<?= $this->include('template/header') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Assignments - <?= esc($course['title']) ?></h2>
        <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
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
            <?php if (!empty($assignments)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Assignment Type</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Max Score</th>
                                <th>Due Date</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $index => $assignment): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><span class="badge bg-info"><?= esc($assignment['assignment_type']) ?></span></td>
                                    <td><strong><?= esc($assignment['title']) ?></strong></td>
                                    <td><?= esc($assignment['description'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-secondary"><?= number_format($assignment['max_score'] ?? 100, 2) ?></span></td>
                                    <td>
                                        <?php if (!empty($assignment['due_date'])): ?>
                                            <?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">No due date</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($assignment['score'])): ?>
                                            <strong class="<?= ($assignment['percentage'] ?? 0) >= 75 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($assignment['score'], 2) ?>/<?= number_format($assignment['max_score'] ?? 100, 2) ?>
                                            </strong>
                                            <br><small>
                                                (<?= number_format($assignment['percentage'] ?? 0, 2) ?>%)
                                                <?php if (!empty($assignment['remarks'])): ?>
                                                    - <?= esc($assignment['remarks']) ?>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Not graded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($assignment['score'])): ?>
                                            <span class="badge bg-success">Graded</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <p class="mb-0">No assignments available for this course yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->include('template/footer') ?>

