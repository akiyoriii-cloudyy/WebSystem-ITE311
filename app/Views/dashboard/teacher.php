<?= $this->include('templates/header') ?>

<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card p-3 text-center">
            <h5>My Classes</h5>
            <p class="fs-4 fw-bold"><?= esc($my_classes ?? 0) ?></p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card p-3 text-center">
            <h5>Students</h5>
            <p class="fs-4 fw-bold"><?= esc($my_students ?? 0) ?></p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card p-3 text-center">
            <h5>Notifications</h5>
            <p class="fs-4 fw-bold"><?= esc($my_notifications ?? 0) ?></p>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
