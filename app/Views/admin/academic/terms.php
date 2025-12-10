<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Terms Management</h2>

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
                    <h5 class="mb-0">Add/Edit Term</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/academic/terms') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" id="semester_id" class="form-select" required>
                                <option value="">-- Select Semester --</option>
                                <?php foreach ($semesters as $semester): ?>
                                    <option value="<?= $semester['id'] ?>" data-year="<?= esc($semester['acad_year']) ?>">
                                        <?= esc($semester['acad_year']) ?> - <?= esc($semester['semester']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Term Name</label>
                            <input type="text" name="term" id="term" class="form-control" required placeholder="e.g., Prelim, Midterm, Finals">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Term Code</label>
                            <input type="text" name="term_code" id="term_code" class="form-control" required placeholder="e.g., PRELIM, MIDTERM, FINALS">
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

                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">Create Term</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetForm()" id="cancelBtn" style="display:none;">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Terms List</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Term</th>
                                <th>Code</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($terms)): ?>
                                <?php foreach ($terms as $term): ?>
                                    <tr>
                                        <td><?= esc($term['acad_year']) ?></td>
                                        <td><?= esc($term['semester']) ?></td>
                                        <td><?= esc($term['term']) ?></td>
                                        <td><?= esc($term['term_code']) ?></td>
                                        <td><?= esc($term['start_date']) ?></td>
                                        <td><?= esc($term['end_date']) ?></td>
                                        <td>
                                            <?php if ($term['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editTerm(<?= htmlspecialchars(json_encode($term)) ?>)">Edit</button>
                                            <form method="post" action="<?= site_url('admin/academic/terms') ?>" style="display:inline;" onsubmit="return confirm('Delete this term?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $term['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No terms found</td>
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
function editTerm(term) {
    document.getElementById('action').value = 'update';
    document.getElementById('edit_id').value = term.id;
    document.getElementById('semester_id').value = term.semester_id;
    document.getElementById('term').value = term.term;
    document.getElementById('term_code').value = term.term_code;
    document.getElementById('start_date').value = term.start_date;
    document.getElementById('end_date').value = term.end_date;
    document.getElementById('is_active').checked = term.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Update Term';
    document.getElementById('cancelBtn').style.display = 'block';
    document.getElementById('term').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('action').value = 'create';
    document.getElementById('edit_id').value = '';
    document.getElementById('semester_id').value = '';
    document.getElementById('term').value = '';
    document.getElementById('term_code').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('is_active').checked = false;
    document.getElementById('submitBtn').textContent = 'Create Term';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?= $this->include('template/footer') ?>

