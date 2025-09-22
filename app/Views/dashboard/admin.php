<?= $this->include('templates/header') ?>

<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card p-3 text-center">
            <h5>Total Users</h5>
            <p class="fs-4 fw-bold"><?= esc($total_users ?? 0) ?></p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card p-3 text-center">
            <h5>Projects</h5>
            <p class="fs-4 fw-bold"><?= esc($total_projects ?? 0) ?></p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card p-3 text-center">
            <h5>Notifications</h5>
            <p class="fs-4 fw-bold"><?= esc($total_notifications ?? 0) ?></p>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
