<?= $this->include('template/header') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Quiz Result - <?= esc($quiz['title']) ?></h2>
        <a href="<?= site_url('student/quiz/course/' . $course['id']) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Quizzes
        </a>
    </div>

    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info"><?= session()->getFlashdata('info') ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Quiz Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Course:</strong> <?= esc($course['title']) ?></p>
            <p><strong>Description:</strong> <?= esc($quiz['description'] ?? 'No description') ?></p>
            <p><strong>Maximum Score:</strong> <?= number_format($quiz['max_score'] ?? 100, 2) ?> points</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Your Results</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($submission['score'])): ?>
                <div class="text-center mb-4">
                    <h3 class="text-primary">
                        Score: <?= number_format($submission['score'], 2) ?> / <?= number_format($quiz['max_score'] ?? 100, 2) ?>
                    </h3>
                    <h4 class="text-<?= ($submission['score'] / ($quiz['max_score'] ?? 100)) * 100 >= 75 ? 'success' : 'danger' ?>">
                        <?= number_format(($submission['score'] / ($quiz['max_score'] ?? 100)) * 100, 2) ?>%
                    </h4>
                    <?php if (($submission['score'] / ($quiz['max_score'] ?? 100)) * 100 >= 75): ?>
                        <span class="badge bg-success fs-6">Passed</span>
                    <?php else: ?>
                        <span class="badge bg-danger fs-6">Failed</span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-hourglass-split"></i> Your quiz has been submitted and is pending grading by your instructor.
                </div>
            <?php endif; ?>
            
            <hr>
            
            <p><strong>Submitted At:</strong> <?= !empty($submission['submitted_at']) ? date('M d, Y H:i:s', strtotime($submission['submitted_at'])) : 'N/A' ?></p>
            
            <?php if (!empty($submission['graded_at'])): ?>
                <p><strong>Graded At:</strong> <?= date('M d, Y H:i:s', strtotime($submission['graded_at'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Your Answers</h5>
        </div>
        <div class="card-body">
            <div class="bg-light p-3 rounded">
                <pre style="white-space: pre-wrap; font-family: inherit;"><?= esc($submission['answer'] ?? 'No answers submitted') ?></pre>
            </div>
        </div>
    </div>
</div>

<?= $this->include('template/footer') ?>

