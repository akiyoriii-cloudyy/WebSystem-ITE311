<?= $this->include('template/header') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Take Quiz - <?= esc($quiz['title']) ?></h2>
        <a href="<?= site_url('student/quiz/course/' . $course['id']) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Quizzes
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Quiz Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Course:</strong> <?= esc($course['title']) ?></p>
            <p><strong>Description:</strong> <?= esc($quiz['description'] ?? 'No description') ?></p>
            <p><strong>Maximum Score:</strong> <?= number_format($quiz['max_score'] ?? 100, 2) ?> points</p>
            <?php if (!empty($quiz['due_date'])): ?>
                <p><strong>Due Date:</strong> <?= date('M d, Y H:i', strtotime($quiz['due_date'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Quiz Questions</h5>
        </div>
        <div class="card-body">
            <form id="quizForm">
                <?= csrf_field() ?>
                <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                
                <!-- Simple Quiz Form - Can be extended with actual questions -->
                <div class="mb-4">
                    <label for="answers" class="form-label fw-bold">Your Answers</label>
                    <textarea class="form-control" id="answers" name="answers" rows="10" 
                              placeholder="Enter your answers here. You can format them as:
Question 1: Your answer
Question 2: Your answer
..."><?= !empty($submission['answer']) ? esc($submission['answer']) : '' ?></textarea>
                    <small class="form-text text-muted">Enter your answers in the text area above.</small>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Note:</strong> Make sure to review your answers before submitting. Once submitted, you cannot change them.
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Submit Quiz
                    </button>
                    <a href="<?= site_url('student/quiz/course/' . $course['id']) ?>" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('quizForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Submitting...';
    
    const formData = new FormData(this);
    const answers = document.getElementById('answers').value;
    formData.set('answers', answers);
    
    fetch('<?= site_url('student/quiz/submit') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the quiz. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>

<?= $this->include('template/footer') ?>

