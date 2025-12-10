<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Programs Management</h2>

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
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Add/Edit Program</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/programs') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select" required>
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= esc($dept['department_code']) ?> - <?= esc($dept['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Programs must belong to a department</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Program Code</label>
                            <input type="text" name="program_code" id="program_code" class="form-control" required placeholder="e.g., BSIT, BSCS" maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Program Name</label>
                            <input type="text" name="program_name" id="program_name" class="form-control" required placeholder="e.g., Bachelor of Science in Information Technology">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="submitBtn">Create Program</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetForm()" id="cancelBtn" style="display:none;">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Programs List</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Program Code</th>
                                <th>Program Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($programs)): ?>
                                <?php foreach ($programs as $program): ?>
                                    <tr>
                                        <td><strong><?= esc($program['department_code']) ?></strong><br><small><?= esc($program['department_name']) ?></small></td>
                                        <td><strong><?= esc($program['program_code']) ?></strong></td>
                                        <td><?= esc($program['program_name']) ?></td>
                                        <td><?= esc($program['description'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($program['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editProgram(<?= htmlspecialchars(json_encode($program)) ?>)">Edit</button>
                                            <form method="post" action="<?= site_url('admin/programs') ?>" style="display:inline;" onsubmit="return confirm('Delete this program?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $program['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No programs found</td>
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
function editProgram(program) {
    document.getElementById('action').value = 'update';
    document.getElementById('edit_id').value = program.id;
    document.getElementById('department_id').value = program.department_id;
    document.getElementById('program_code').value = program.program_code;
    document.getElementById('program_name').value = program.program_name;
    document.getElementById('description').value = program.description || '';
    document.getElementById('is_active').checked = program.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Update Program';
    document.getElementById('cancelBtn').style.display = 'block';
    document.getElementById('program_code').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('action').value = 'create';
    document.getElementById('edit_id').value = '';
    document.getElementById('department_id').value = '';
    document.getElementById('program_code').value = '';
    document.getElementById('program_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('submitBtn').textContent = 'Create Program';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?= $this->include('template/footer') ?>

