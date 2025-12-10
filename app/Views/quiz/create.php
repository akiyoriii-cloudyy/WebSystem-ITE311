<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">Create Quiz - <?= esc($course['title']) ?></h2>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= site_url('quiz/create/' . $course['id']) ?>">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= old('title') ?>" required minlength="3" maxlength="255">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= old('description') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="max_score" class="form-label">Maximum Score</label>
                        <input type="number" class="form-control" id="max_score" name="max_score" 
                               value="<?= old('max_score', 100) ?>" step="0.01" min="0">
                        <small class="form-text text-muted">Default: 100</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="datetime-local" class="form-control" id="due_date" name="due_date" 
                               value="<?= old('due_date') ?>">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Quiz</button>
                    <a href="<?= site_url('quiz/course/' . $course['id']) ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->include('template/footer') ?>

