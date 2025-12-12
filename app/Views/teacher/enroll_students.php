<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Enroll Students - <?= esc($course['title']) ?></h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Enroll New Student</h5>
                </div>
                <div class="card-body">
                    <form id="enrollForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Student</label>
                            <select name="student_id" id="student_id" class="form-select" required>
                                <option value="">-- Select Student --</option>
                                <?php 
                                // Get enrolled user IDs (use user_id from enrollments or id from users)
                                $enrolledIds = [];
                                foreach ($enrolled_students as $enrolled) {
                                    $enrolledIds[] = $enrolled['user_id'] ?? $enrolled['id'] ?? null;
                                }
                                $enrolledIds = array_filter($enrolledIds); // Remove null values
                                foreach ($students as $student): 
                                    if (!in_array($student['id'], $enrolledIds)):
                                ?>
                                    <option value="<?= $student['id'] ?>">
                                        <?= esc($student['name']) ?> 
                                        <?php if (!empty($student['student_id'])): ?>
                                            (ID: <?= esc($student['student_id']) ?>)
                                        <?php endif; ?>
                                        - <?= esc($student['email']) ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Enroll Student</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Course Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Course:</strong> <?= esc($course['title']) ?></p>
                    <?php if (!empty($course['course_number'])): ?>
                        <p><strong>Course Number:</strong> <?= esc($course['course_number']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($course['description'])): ?>
                        <p><strong>Description:</strong> <?= esc($course['description']) ?></p>
                    <?php endif; ?>
                    <p><strong>Enrolled Students:</strong> <?= count($enrolled_students) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Enrolled Students (<?= count($enrolled_students) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($enrolled_students)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Enrollment Status</th>
                            <th>Final Grade</th>
                            <th>Enrolled At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrolled_students as $student): ?>
                            <tr>
                                <td><?= esc($student['student_id'] ?? 'N/A') ?></td>
                                <td><?= esc($student['name']) ?></td>
                                <td><?= esc($student['email']) ?></td>
                                <td>
                                    <?php
                                    $status = $student['completion_status'] ?? 'ENROLLED';
                                    $badgeClass = 'bg-secondary';
                                    if ($status === 'COMPLETED') $badgeClass = 'bg-success';
                                    elseif ($status === 'FAILED') $badgeClass = 'bg-danger';
                                    elseif ($status === 'IN_PROGRESS') $badgeClass = 'bg-warning';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc($status) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($student['final_grade'])): ?>
                                        <strong><?= number_format($student['final_grade'], 2) ?>%</strong>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($student['enrolled_at'] ?? $student['enrollment_date'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="<?= site_url('teacher/courses/remove-student/' . $course['id'] . '/' . ($student['enrollment_id'] ?? $student['id']) . '?from=enroll') ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to remove this student from the course?')">
                                        <i class="bi bi-trash"></i> Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted text-center">No students enrolled yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#enrollForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        
        submitBtn.prop('disabled', true).text('Enrolling...');
        
        $.ajax({
            url: '<?= site_url('teacher/courses/' . $course['id'] . '/enroll-student') ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Refresh notifications before reloading page
                    if (typeof fetchNotifications === 'function') {
                        fetchNotifications();
                    }
                    alert(response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error: ' + response.message);
                    submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
                alert('Error: ' + errorMessage);
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

<?= $this->include('template/footer') ?>

