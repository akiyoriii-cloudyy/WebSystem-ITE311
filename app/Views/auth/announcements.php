<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-3"><?= esc($title) ?></h2>

    <p>Welcome, <b><?= esc($user_name) ?></b> (<?= ucfirst($user_role) ?>)</p>
    <a href="/auth/logout" class="btn btn-danger btn-sm mb-3">Logout</a>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Teacher Create Form -->
    <?php if ($user_role === 'teacher'): ?>
        <div class="card mb-4">
            <div class="card-header">Post a New Announcement</div>
            <div class="card-body">
                <form action="/announcements/create" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea name="content" id="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-primary">Post Announcement</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Announcements List -->
    <div class="card">
        <div class="card-header">All Announcements</div>
        <ul class="list-group list-group-flush">
            <?php if (empty($announcements)): ?>
                <li class="list-group-item text-muted">No announcements yet.</li>
            <?php else: ?>
                <?php foreach ($announcements as $a): ?>
                    <li class="list-group-item">
                        <h5><?= esc($a['title']) ?></h5>
                        <p><?= esc($a['content']) ?></p>
                        <small class="text-muted">Posted on <?= date('M d, Y h:i A', strtotime($a['created_at'])) ?></small>

                        <?php if ($user_role === 'teacher'): ?>
                            <a href="/announcements/delete/<?= $a['id'] ?>" 
                               class="btn btn-sm btn-danger float-end"
                               onclick="return confirm('Are you sure?')">Delete</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

</body>
</html>
