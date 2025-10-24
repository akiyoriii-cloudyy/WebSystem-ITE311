<?= $this->include('template/header') ?>

<div class="container mt-4">
  <h3 class="mb-3">Courses</h3>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <?php if (!empty($courses)): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Course</th>
            <th>Instructor</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $i => $c): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($c['title'] ?? $c['name'] ?? ('Course #' . $c['id'])) ?></td>
              <td><?= esc($c['instructor_name'] ?? '—') ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="<?= base_url('admin/course/' . (int)$c['id'] . '/upload') ?>">Upload Materials</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No courses found.</div>
  <?php endif; ?>
</div>

<?= $this->include('template/footer') ?>
