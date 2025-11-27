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

        /* 🔔 Notification Bell Styling */
        .notification-bell {
            position: relative;
            cursor: pointer;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.2s ease;
            color: rgba(255,255,255,0.85);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notification-bell:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }

        .notification-dropdown {
            display: none;
            position: fixed;
            top: auto;
            left: 20px;
            bottom: 120px;
            width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            max-height: 500px;
            overflow-y: auto;
            z-index: 9998;
        }

        .notification-dropdown.show {
            display: block;
            animation: slideUpFade 0.3s ease-out;
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-item {
            padding: 18px;
            border-bottom: 1px solid #e9ecef;
            color: #333;
            transition: background 0.2s ease;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-message {
            font-size: 0.95rem;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            line-height: 1.5;
            word-wrap: break-word;
            white-space: normal;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 8px;
            display: block;
        }

        .notification-mark-read {
            display: inline-block;
            color: #007bff;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            font-weight: 500;
            padding: 4px 8px;
            border: 1px solid #007bff;
            border-radius: 4px;
            background: transparent;
            transition: all 0.2s;
        }

        .notification-mark-read:hover {
            background: #007bff;
            color: white;
            text-decoration: none;
        }

        .notification-empty {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }

        /* Custom scrollbar for notification dropdown */
        .notification-dropdown::-webkit-scrollbar {
            width: 8px;
        }

        .notification-dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .notification-dropdown::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .notification-dropdown::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Notification dropdown header */
        .notification-dropdown-header {
            padding: 12px 15px;
            border-bottom: 2px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* 🔔 Toast Notification Popup */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-left: 4px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            display: none;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification-toast.show {
            display: block;
        }

        .notification-toast-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .notification-toast-title {
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }

        .notification-toast-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #999;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 24px;
            height: 24px;
        }

        .notification-toast-close:hover {
            color: #333;
        }

        .notification-toast-message {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .notification-toast-actions {
            display: flex;
            gap: 10px;
        }

        .notification-toast-btn {
            padding: 6px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .notification-toast-btn-primary {
            background: #007bff;
            color: white;
        }

        .notification-toast-btn-primary:hover {
            background: #0056b3;
        }

        .notification-toast-btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }

        .notification-toast-btn-secondary:hover {
            background: #e9ecef;
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
            <a href="<?= site_url('/courses') ?>">🔍 Browse & Search Courses</a>
            <a href="<?= site_url('/admin/reports') ?>">📑 Reports</a>
        <?php elseif ($user_role === 'teacher'): ?>
            
            <a href="<?= site_url('/teacher/courses') ?>">📘 My Courses</a>
            <a href="<?= site_url('/courses') ?>">🔍 Browse & Search Courses</a>
            <a href="<?= site_url('/teacher/students') ?>">👨‍🎓 My Students</a>
            <a href="<?= site_url('/teacher/deadlines') ?>">⏰ Deadlines</a>
        <?php elseif ($user_role === 'student'): ?>

            <a href="<?= site_url('/student/enrollments') ?>">📚 My Enrollments</a>
            <a href="<?= site_url('/courses') ?>">🔍 Browse & Search Courses</a>
            <a href="<?= site_url('/student/deadlines') ?>">⏰ Deadlines</a>
        <?php endif; ?>
    </div>

    <div>
        <!-- 🔔 Notification Bell -->
        <div class="notification-bell" id="notification-bell" style="position: relative;">
            <span>🔔 Notifications</span>
            <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
            
            <!-- Dropdown for notifications -->
            <div class="notification-dropdown" id="notification-dropdown">
                <div class="notification-dropdown-header">
                    <button class="btn btn-sm btn-primary w-100" id="markAllAsRead" style="display: none; font-size: 0.9rem;">
                        ✓ Mark All as Read
                    </button>
                </div>
                <div id="notification-list">
                    <div class="notification-empty">No new notifications</div>
                </div>
            </div>
        </div>

        <a href="<?= site_url('/auth/logout') ?>" class="text-white">🚪 Logout</a>
    </div>
</div>

<!-- 🔔 Toast Notification Container -->
<div class="notification-toast" id="notificationToast">
    <div class="notification-toast-header">
        <div class="notification-toast-title">
            <span style="font-size: 1.2rem; margin-right: 5px;">🔔</span>
            <span id="toastTitle">New Notification</span>
        </div>
        <button class="notification-toast-close" id="toastClose" aria-label="Close">&times;</button>
    </div>
    <div class="notification-toast-message" id="toastMessage">
        You have a new notification!
    </div>
    <div class="notification-toast-actions">
        <button class="notification-toast-btn notification-toast-btn-primary" id="toastMarkRead">
            Mark as Read
        </button>
        <button class="notification-toast-btn notification-toast-btn-secondary" id="toastDismiss">
            Dismiss
        </button>
    </div>
</div>

<div class="content">
