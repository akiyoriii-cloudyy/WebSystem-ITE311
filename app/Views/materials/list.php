<?php /** @var array $materials */ ?>
<div class="container mt-3">
  <h4>Course Materials</h4>
  <ul class="list-group">
    <?php if (!empty($materials)): foreach ($materials as $m): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <span><?= esc($m['file_name']); ?></span>
        <a class="btn btn-sm btn-primary" href="<?= base_url('materials/download/' . (int)$m['id']); ?>">Download</a>
      </li>
    <?php endforeach; else: ?>
      <li class="list-group-item">No materials available.</li>
    <?php endif; ?>
  </ul>
</div>
