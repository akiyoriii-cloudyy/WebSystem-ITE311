<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* 🌈 Role-Specific Colors */
            --admin-bg: linear-gradient(135deg, #007bff, #6610f2);
            --teacher-bg: linear-gradient(135deg, #28a745, #20c997);
            --student-bg: linear-gradient(135deg, #0dcaf0, #6610f2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }

        /* 🧭 Sidebar */
        .sidebar {
            height: 100vh;
            color: #fff;
            padding: 25px 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        /* 🎨 Apply role-based colors */
        body.admin .sidebar { background: var(--admin-bg); }
        body.teacher .sidebar { background: var(--teacher-bg); }
        body.student .sidebar { background: var(--student-bg); }

        .sidebar h4 {
            font-weight: 700;
            font-size: 1.4rem;
            text-align: center;
            margin-bottom: 40px;
        }

        .sidebar a {
            display: block;
            color: rgba(255,255,255,0.85);
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .content {
            margin-left: 270px;
            padding: 40px;
            background: #f8f9fc;
            min-height: 100vh;
        }

        /* 💎 Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-3px);
        }

        /* 🧩 Dashboard Title */
        h2.text-center {
            font-weight: 700;
            color: #2c3e50;
        }

        /* 💬 Alerts */
        .alert {
            border-radius: 10px;
        }

        /* ✨ Role-specific header gradient for the welcome card */
        .welcome-card {
            color: white;
            border-radius: 20px;
            padding: 30px;
        }
        body.admin .welcome-card { background: var(--admin-bg); }
        body.teacher .welcome-card { background: var(--teacher-bg); }
        body.student .welcome-card { background: var(--student-bg); }

        /* 🧠 Table Styling */
        table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: #343a40;
            color: #fff;
        }
    </style>
</head>
<body class="<?= esc($user_role ?? 'student') ?>">

<div class="sidebar">
    <div>
        <h4>🎓 LMS Portal</h4>

        <?php if ($user_role === 'admin'): ?>
            <a href="<?= site_url('/admin_dashboard') ?>" 
               class="<?= (current_url() == site_url('/admin_dashboard')) ? 'active' : '' ?>">🏠 Dashboard</a>
        <?php elseif ($user_role === 'teacher'): ?>
            <a href="<?= site_url('/teacher_dashboard') ?>" 
               class="<?= (current_url() == site_url('/teacher_dashboard')) ? 'active' : '' ?>">🏠 Dashboard</a>
        <?php else: ?>
            <a href="<?= site_url('/dashboard') ?>" 
               class="<?= (current_url() == site_url('/dashboard')) ? 'active' : '' ?>">🏠 Dashboard</a>
        <?php endif; ?>

        <?php if ($user_role === 'admin'): ?>
            <a href="<?= site_url('/admin/users') ?>">👥 Manage Users</a>
            <a href="<?= site_url('/admin/courses') ?>">📘 Manage Courses</a>
            <a href="<?= site_url('/admin/reports') ?>">📑 Reports</a>
        <?php elseif ($user_role === 'teacher'): ?>
            
            <a href="<?= site_url('/teacher/courses') ?>">📘 My Courses</a>
            <a href="<?= site_url('/teacher/students') ?>">👨‍🎓 My Students</a>
            <a href="<?= site_url('/teacher/deadlines') ?>">⏰ Deadlines</a>
        <?php elseif ($user_role === 'student'): ?>

            <a href="<?= site_url('/student/enrollments') ?>">📚 My Enrollments</a>
            <a href="<?= site_url('/student/courses') ?>">🧾 Available Courses</a>
            <a href="<?= site_url('/student/deadlines') ?>">⏰ Deadlines</a>
        <?php endif; ?>
    </div>

    <div>
        <a href="<?= site_url('/auth/logout') ?>" class="text-white">🚪 Logout</a>
    </div>
</div>

<div class="content">
