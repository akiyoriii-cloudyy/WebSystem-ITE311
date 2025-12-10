<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Grade Assignment - <?= esc($assignment['title']) ?></h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Assignment Details</h5>
        </div>
        <div class="card-body">
            <p><strong>Type:</strong> <?= esc($assignment['assignment_type']) ?></p>
            <p><strong>Title:</strong> <?= esc($assignment['title']) ?></p>
            <p><strong>Max Score:</strong> <?= number_format($assignment['max_score'], 2) ?></p>
            <?php if (!empty($assignment['description'])): ?>
                <p><strong>Description:</strong> <?= esc($assignment['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($assignment['due_date'])): ?>
                <p><strong>Due Date:</strong> <?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Student Grades</h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= site_url('teacher/assignments/' . $assignment['id'] . '/grade') ?>">
                <?= csrf_field() ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <?php 
                            $grade = isset($grades[$enrollment['id']]) ? $grades[$enrollment['id']] : null;
                            $score = $grade ? ($grade['score'] ?? '') : '';
                            $percentage = $grade ? ($grade['percentage'] ?? '') : '';
                            $remarks = $grade ? ($grade['remarks'] ?? '') : '';
                            ?>
                            <tr>
                                <td><?= esc($enrollment['student_id'] ?? 'N/A') ?></td>
                                <td><?= esc($enrollment['name']) ?></td>
                                <td><?= esc($enrollment['email']) ?></td>
                                <td>
                                    <input type="hidden" name="enrollment_ids[]" value="<?= $enrollment['id'] ?>">
                                    <input type="number" 
                                           name="scores[<?= $enrollment['id'] ?>]" 
                                           class="form-control score-input" 
                                           step="0.01" 
                                           min="0" 
                                           max="<?= $assignment['max_score'] ?>"
                                           value="<?= $score ?>"
                                           data-max="<?= $assignment['max_score'] ?>"
                                           data-enrollment="<?= $enrollment['id'] ?>"
                                           placeholder="Enter score">
                                </td>
                                <td>
                                    <span class="percentage-display" id="percentage_<?= $enrollment['id'] ?>">
                                        <?= $percentage ? number_format($percentage, 2) . '%' : 'N/A' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="remarks-display" id="remarks_<?= $enrollment['id'] ?>">
                                        <?= $remarks ? esc($remarks) : 'N/A' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success w-100 mt-3">Save All Grades</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.score-input').on('input', function() {
        const score = parseFloat($(this).val()) || 0;
        const maxScore = parseFloat($(this).data('max'));
        const enrollmentId = $(this).data('enrollment');
        
        if (maxScore > 0) {
            const percentage = (score / maxScore) * 100;
            $('#percentage_' + enrollmentId).text(percentage.toFixed(2) + '%');
            
            const remarks = percentage >= 75 ? 'Passed' : (percentage > 0 ? 'Failed' : 'N/A');
            $('#remarks_' + enrollmentId).text(remarks);
        }
    });
});
</script>

<?= $this->include('template/footer') ?>

