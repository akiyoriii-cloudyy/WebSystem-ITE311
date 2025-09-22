<?= $this->include('templates/header') ?>

<div class="row mt-4">
    <div class="col-md-6 mb-3">
        <div class="card p-3 text-center">
            <h5>My Courses</h5>
            <p class="fs-4 fw-bold"><?= esc($my_courses ?? 0) ?></p>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card p-3 text-center">
            <h5>Notifications</h5>
            <p class="fs-4 fw-bold"><?= esc($my_notifications ?? 0) ?></p>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
