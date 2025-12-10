<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Semesters Management</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach(session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Add/Edit Semester</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/academic/semesters') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <select name="acad_year_id" id="acad_year_id" class="form-select" required>
                                <option value="">-- Select Academic Year --</option>
                                <?php foreach ($acad_years as $year): ?>
                                    <option value="<?= $year['id'] ?>"><?= esc($year['acad_year']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Semester Name</label>
                            <input type="text" name="semester" id="semester" class="form-control" required placeholder="e.g., First Semester">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Semester Code</label>
                            <input type="text" name="semester_code" id="semester_code" class="form-control" required placeholder="e.g., 1ST">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1">
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">Create Semester</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetForm()" id="cancelBtn" style="display:none;">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Semesters List</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Code</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($semesters)): ?>
                                <?php foreach ($semesters as $semester): ?>
                                    <tr>
                                        <td><?= esc($semester['acad_year']) ?></td>
                                        <td><?= esc($semester['semester']) ?></td>
                                        <td><?= esc($semester['semester_code']) ?></td>
                                        <td><?= esc($semester['start_date']) ?></td>
                                        <td><?= esc($semester['end_date']) ?></td>
                                        <td>
                                            <?php if ($semester['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editSemester(<?= htmlspecialchars(json_encode($semester)) ?>)">Edit</button>
                                            <form method="post" action="<?= site_url('admin/academic/semesters') ?>" style="display:inline;" onsubmit="return confirm('Delete this semester?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $semester['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No semesters found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editSemester(semester) {
    document.getElementById('action').value = 'update';
    document.getElementById('edit_id').value = semester.id;
    document.getElementById('acad_year_id').value = semester.acad_year_id;
    document.getElementById('semester').value = semester.semester;
    document.getElementById('semester_code').value = semester.semester_code;
    document.getElementById('start_date').value = semester.start_date;
    document.getElementById('end_date').value = semester.end_date;
    document.getElementById('is_active').checked = semester.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Update Semester';
    document.getElementById('cancelBtn').style.display = 'block';
    document.getElementById('semester').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('action').value = 'create';
    document.getElementById('edit_id').value = '';
    document.getElementById('acad_year_id').value = '';
    document.getElementById('semester').value = '';
    document.getElementById('semester_code').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('is_active').checked = false;
    document.getElementById('submitBtn').textContent = 'Create Semester';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?= $this->include('template/footer') ?>

