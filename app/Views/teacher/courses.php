<?= $this->include('template/header') ?>

<div class="container mt-4">
  <h3 class="mb-3">My Courses</h3>

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
            <th>Students</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $i => $c): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($c['title'] ?? $c['name'] ?? ('Course #' . $c['id'])) ?></td>
              <td><span class="badge bg-secondary"><?= (int)($c['student_count'] ?? 0) ?></span></td>
              <td>
                <a class="btn btn-sm btn-success" href="<?= base_url('teacher/course/' . (int)$c['id'] . '/upload') ?>">Upload Materials</a>
                <a class="btn btn-sm btn-outline-primary" href="<?= base_url('teacher/courses/view/' . (int)$c['id']) ?>">View</a>
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
