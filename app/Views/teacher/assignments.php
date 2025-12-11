<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Assignments Management - <?= esc($course['title']) ?></h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Create Assignment</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('teacher/courses/' . $course['id'] . '/assignments') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="create">

                        <div class="mb-3">
                            <label class="form-label">Grading Period</label>
                            <select name="grading_period_id" class="form-select" required>
                                <option value="">-- Select Grading Period --</option>
                                <?php foreach ($grading_periods as $period): ?>
                                    <option value="<?= $period['id'] ?>"><?= esc($period['period_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assignment Type</label>
                            <select name="assignment_type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="Quiz">Quiz</option>
                                <option value="Exam">Exam</option>
                                <option value="Project">Project</option>
                                <option value="Lab">Lab</option>
                                <option value="Homework">Homework</option>
                                <option value="Participation">Participation</option>
                                <option value="Final Exam">Final Exam</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Score</label>
                            <input type="number" name="max_score" class="form-control" step="0.01" value="100" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="datetime-local" name="due_date" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Create Assignment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Assignments List</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($assignments)): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Max Score</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><span class="badge bg-info"><?= esc($assignment['assignment_type']) ?></span></td>
                                        <td><?= esc($assignment['title']) ?></td>
                                        <td><?= number_format($assignment['max_score'], 2) ?></td>
                                        <td><?= $assignment['due_date'] ? date('M d, Y H:i', strtotime($assignment['due_date'])) : 'N/A' ?></td>
                                        <td>
                                            <a href="<?= site_url('teacher/assignments/' . $assignment['id'] . '/grade') ?>" class="btn btn-sm btn-success">Grade</a>
                                            <a href="<?= site_url('teacher/assignments/' . $assignment['id'] . '/delete') ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this assignment? This action cannot be undone.')">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted text-center">No assignments created yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('template/footer') ?>

