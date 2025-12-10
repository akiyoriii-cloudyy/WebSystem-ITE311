<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Departments Management</h2>

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
                    <h5 class="mb-0">Add/Edit Department</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/departments') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Department Code</label>
                            <input type="text" name="department_code" id="department_code" class="form-control" required placeholder="e.g., CS, IT, ENG" maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" id="department_name" class="form-control" required placeholder="e.g., Computer Science">
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

                        <button type="submit" class="btn btn-success w-100" id="submitBtn">Create Department</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetForm()" id="cancelBtn" style="display:none;">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Departments List</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Department Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><strong><?= esc($dept['department_code']) ?></strong></td>
                                        <td><?= esc($dept['department_name']) ?></td>
                                        <td><?= esc($dept['description'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($dept['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editDepartment(<?= htmlspecialchars(json_encode($dept)) ?>)">Edit</button>
                                            <form method="post" action="<?= site_url('admin/departments') ?>" style="display:inline;" onsubmit="return confirm('Delete this department? This will also delete all associated programs.')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $dept['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No departments found</td>
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
function editDepartment(dept) {
    document.getElementById('action').value = 'update';
    document.getElementById('edit_id').value = dept.id;
    document.getElementById('department_code').value = dept.department_code;
    document.getElementById('department_name').value = dept.department_name;
    document.getElementById('description').value = dept.description || '';
    document.getElementById('is_active').checked = dept.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Update Department';
    document.getElementById('cancelBtn').style.display = 'block';
    document.getElementById('department_code').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('action').value = 'create';
    document.getElementById('edit_id').value = '';
    document.getElementById('department_code').value = '';
    document.getElementById('department_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('submitBtn').textContent = 'Create Department';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?= $this->include('template/footer') ?>

