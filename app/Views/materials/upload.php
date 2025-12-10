<?php /** @var array $materials */ /** @var int|string $course_id */ ?>
<?php $role = strtolower(session()->get('user_role') ?? '');
$backUrl = base_url($role === 'admin' ? 'admin/manage-courses' : 'teacher/courses'); ?>
<div class="container mt-4">
  <h3>Upload Course Material</h3>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= nl2br(esc($error)) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?php echo nl2br(esc(session()->getFlashdata('error'))); ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?php echo esc(session()->getFlashdata('success')); ?></div>
  <?php endif; ?>

  <form method="post" action="<?= current_url() ?>" enctype="multipart/form-data" class="mb-4">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label for="file" class="form-label">Choose file</label>
      <input type="file" class="form-control" id="file" name="file" accept=".pdf,.ppt,.pptx" required>
      <div class="form-text">Allowed: PDF and PPT files only (pdf, ppt, pptx). Max 50MB.</div>
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
    <a href="<?= $backUrl ?>" class="btn btn-secondary">Back to Courses</a>
  </form>

  <h5>Existing Materials</h5>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>File Name</th>
          <th>Uploaded At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($materials)): $i=1; foreach ($materials as $m): ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><?= esc($m['file_name']); ?></td>
            <td><?= esc($m['created_at']); ?></td>
            <td>
              <a class="btn btn-sm btn-success" href="<?= base_url('materials/download/' . (int)$m['id']) ?>">Download</a>
              <a class="btn btn-sm btn-danger" href="<?= base_url('materials/delete/' . (int)$m['id']) ?>" onclick="return confirm('Delete this material?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4" class="text-center">No materials yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
