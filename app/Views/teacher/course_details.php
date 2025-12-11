<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Course Details - <?= esc($course['title']) ?></h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Course Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Course:</strong> <?= esc($course['title']) ?></p>
                            <?php if (!empty($course['course_number'])): ?>
                                <p><strong>Course Number:</strong> <?= esc($course['course_number']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($course['description'])): ?>
                                <p><strong>Description:</strong> <?= esc($course['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Enrolled Students:</strong> <?= count($enrolledStudents) ?></p>
                            <a href="<?= site_url('teacher/courses/enroll/' . $course['id']) ?>" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Enroll Students
                            </a>
                            <a href="<?= site_url('teacher/courses') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Courses
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Enrolled Students (<?= count($enrolledStudents) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($enrolledStudents)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
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
                            <?php foreach ($enrolledStudents as $student): ?>
                                <tr>
                                    <td><?= esc($student['student_id'] ?? 'N/A') ?></td>
                                    <td><?= esc($student['name'] ?? 'Unknown') ?></td>
                                    <td><?= esc($student['email'] ?? 'N/A') ?></td>
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
                                        <a href="<?= site_url('teacher/courses/remove-student/' . $course['id'] . '/' . ($student['enrollment_id'] ?? $student['id'])) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to remove this student from the course?')">
                                            <i class="bi bi-trash"></i> Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted">No students enrolled yet.</p>
                    <a href="<?= site_url('teacher/courses/enroll/' . $course['id']) ?>" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Enroll Students
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->include('template/footer') ?>

