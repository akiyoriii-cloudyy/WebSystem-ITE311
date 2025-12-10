<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Academic Years Management</h2>

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
                    <h5 class="mb-0">Add/Edit Academic Year</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/academic/years') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" name="acad_year" id="acad_year" class="form-control" required placeholder="e.g., 2024-2025">
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

                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">Create Academic Year</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetForm()" id="cancelBtn" style="display:none;">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Academic Years List</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Academic Year</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($acad_years)): ?>
                                <?php foreach ($acad_years as $year): ?>
                                    <tr>
                                        <td><?= esc($year['acad_year']) ?></td>
                                        <td><?= esc($year['start_date']) ?></td>
                                        <td><?= esc($year['end_date']) ?></td>
                                        <td>
                                            <?php if ($year['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editYear(<?= htmlspecialchars(json_encode($year)) ?>)">Edit</button>
                                            <form method="post" action="<?= site_url('admin/academic/years') ?>" style="display:inline;" onsubmit="return confirm('Delete this academic year?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $year['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No academic years found</td>
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
function editYear(year) {
    document.getElementById('action').value = 'update';
    document.getElementById('edit_id').value = year.id;
    document.getElementById('acad_year').value = year.acad_year;
    document.getElementById('start_date').value = year.start_date;
    document.getElementById('end_date').value = year.end_date;
    document.getElementById('is_active').checked = year.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Update Academic Year';
    document.getElementById('cancelBtn').style.display = 'block';
    document.getElementById('acad_year').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('action').value = 'create';
    document.getElementById('edit_id').value = '';
    document.getElementById('acad_year').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('is_active').checked = false;
    document.getElementById('submitBtn').textContent = 'Create Academic Year';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?= $this->include('template/footer') ?>

