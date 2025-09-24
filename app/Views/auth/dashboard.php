<?= $this->include('templates/header') ?>


    <!-- ✅ Flash message (short & not duplicate) -->
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= esc(session('success')) ?>
        </div>
    <?php endif; ?>

    <!-- Dashboard cards -->
    <div class="row mt-4">
        <?php 
        $cards = [];

        if ($user_role === 'admin') {
            $cards = [
                ['title' => 'Total Users', 'value' => $total_users ?? 0],
                ['title' => 'Projects', 'value' => $total_projects ?? 0],
                ['title' => 'Notifications', 'value' => $total_notifications ?? 0],
            ];
        } elseif ($user_role === 'teacher') {
            $cards = [
                ['title' => 'My Classes', 'value' => $my_classes ?? 0],
                ['title' => 'Students', 'value' => $my_students ?? 0],
                ['title' => 'Notifications', 'value' => $my_notifications ?? 0],
            ];
        } elseif ($user_role === 'student') {
            $cards = [
                ['title' => 'My Courses', 'value' => $my_courses ?? 0],
                ['title' => 'Notifications', 'value' => $my_notifications ?? 0],
            ];
        }

        foreach ($cards as $card): ?>
            <div class="col-md-4 mb-3">
                <div class="card p-3 text-center">
                    <h5><?= esc($card['title']) ?></h5>
                    <p class="fs-4 fw-bold"><?= esc($card['value']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?= $this->include('templates/footer') ?>
