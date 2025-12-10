<?= $this->include('template/header') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Submissions - <?= esc($quiz['title']) ?></h2>
        <a href="<?= site_url('quiz/course/' . $quiz['course_id']) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Quizzes
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5>Quiz Details</h5>
            <p><strong>Course:</strong> <?= esc($course['title'] ?? 'N/A') ?></p>
            <p><strong>Description:</strong> <?= esc($quiz['description'] ?? 'N/A') ?></p>
            <p><strong>Max Score:</strong> <?= number_format($quiz['max_score'] ?? 100, 2) ?></p>
            <?php if (!empty($quiz['due_date'])): ?>
                <p><strong>Due Date:</strong> <?= date('Y-m-d H:i', strtotime($quiz['due_date'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Student Submissions (<?= count($submissions) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($submissions)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Answer/Submission</th>
                                <th>Score</th>
                                <th>Submitted At</th>
                                <th>Graded At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $index => $submission): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($submission['user_name'] ?? 'N/A') ?></td>
                                    <td><?= esc($submission['email'] ?? 'N/A') ?></td>
                                    <td>
                                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                            <?= esc(substr($submission['answer'] ?? 'No answer', 0, 100)) ?>
                                            <?= strlen($submission['answer'] ?? '') > 100 ? '...' : '' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (isset($submission['score']) && $submission['score'] !== null): ?>
                                            <strong class="<?= $submission['score'] >= ($quiz['max_score'] * 0.75) ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($submission['score'], 2) ?> / <?= number_format($quiz['max_score'] ?? 100, 2) ?>
                                            </strong>
                                        <?php else: ?>
                                            <span class="text-muted">Not graded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($submission['submitted_at'])): ?>
                                            <?= date('Y-m-d H:i', strtotime($submission['submitted_at'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($submission['graded_at'])): ?>
                                            <?= date('Y-m-d H:i', strtotime($submission['graded_at'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not graded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary grade-submission-btn" 
                                                data-submission-id="<?= $submission['id'] ?>"
                                                data-current-score="<?= esc($submission['score'] ?? '') ?>"
                                                data-max-score="<?= esc($quiz['max_score'] ?? 100) ?>"
                                                data-answer="<?= esc($submission['answer'] ?? '') ?>"
                                                data-student-name="<?= esc($submission['user_name'] ?? '') ?>">
                                            <?= isset($submission['score']) && $submission['score'] !== null ? 'Update Grade' : 'Grade' ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <p class="mb-0">No submissions found for this quiz.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Grade Submission Modal -->
<div class="modal fade" id="gradeSubmissionModal" tabindex="-1" aria-labelledby="gradeSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradeSubmissionModalLabel">Grade Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="gradeSubmissionForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="grade_submission_id" name="submission_id">
                    <div class="mb-3">
                        <label class="form-label"><strong>Student:</strong> <span id="grade_student_name"></span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Answer/Submission:</strong></label>
                        <div class="border p-3 bg-light" style="max-height: 200px; overflow-y: auto;" id="grade_answer_display"></div>
                    </div>
                    <div class="mb-3">
                        <label for="grade_score" class="form-label">Score <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="grade_score" name="score" 
                               step="0.01" min="0" required>
                        <small class="form-text text-muted">Maximum score: <span id="grade_max_score"></span></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.grade-submission-btn').click(function() {
        const submissionId = $(this).data('submission-id');
        const currentScore = $(this).data('current-score');
        const maxScore = $(this).data('max-score');
        const answer = $(this).data('answer');
        const studentName = $(this).data('student-name');

        $('#grade_submission_id').val(submissionId);
        $('#grade_student_name').text(studentName);
        $('#grade_answer_display').text(answer || 'No answer provided');
        $('#grade_max_score').text(maxScore);
        $('#grade_score').val(currentScore || '').attr('max', maxScore);

        $('#gradeSubmissionModal').modal('show');
    });

    $('#gradeSubmissionForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: '<?= site_url('quiz/grade-submission') ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#gradeSubmissionModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMsg);
            }
        });
    });
});
</script>

<?= $this->include('template/footer') ?>

