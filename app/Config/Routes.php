<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --------------------
// Public Pages (No Filter)
// --------------------
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// --------------------
// Authentication Routes (No Filter)
// --------------------
$routes->match(['get', 'post'], 'auth/login', 'Auth::login');
$routes->match(['get', 'post'], 'auth/register', 'Auth::register');
$routes->get('auth/logout', 'Auth::logout');

// Aliases for cleaner URLs
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->match(['get', 'post'], 'register', 'Auth::register');
$routes->get('logout', 'Auth::logout');

// --------------------
// Protected Routes (Require Login + Role Filter)
// --------------------
$routes->group('', ['filter' => 'roleauth'], function ($routes) {

    // ✅ Role-specific dashboard routes (all point to Auth::dashboard) with role filters
    $routes->match(['get', 'post'], 'admin_dashboard', 'Auth::dashboard', ['filter' => 'roleauth:admin']);
    $routes->match(['get', 'post'], 'teacher_dashboard', 'Auth::dashboard', ['filter' => 'roleauth:teacher']);
    $routes->match(['get', 'post'], 'dashboard', 'Auth::dashboard', ['filter' => 'roleauth:student']);
    
    // Legacy route for backward compatibility
    $routes->match(['get', 'post'], 'auth/dashboard', 'Auth::dashboard');


    // ✅ Announcements (All logged-in users can view)
    $routes->get('announcements', 'Announcement::index');

    // --------------------
    // Admin Routes (admin/* prefix)
    // --------------------
    $routes->group('admin', ['filter' => 'roleauth:admin'], function ($routes) {
        // Dashboard (redirects to /admin_dashboard)
        $routes->get('dashboard', 'Admin::dashboard');
        
        // User Management
        $routes->get('users', 'Admin::users');
        $routes->post('users/update-role', 'Admin::updateUserRole');
        $routes->get('users/delete/(:num)', 'Admin::deleteUser/$1');
        $routes->get('users/toggle-status/(:num)', 'Admin::toggleUserStatus/$1');
        
        // Course Management
        $routes->get('courses', 'Admin::manageCourses');
        $routes->get('manage-courses', 'Admin::manageCourses');

        // Materials Upload (Admin)
        $routes->get('course/(:num)/upload', 'Materials::upload/$1');
        $routes->post('course/(:num)/upload', 'Materials::upload/$1');
        
        // Announcement Management
        $routes->get('announcements', 'Admin::announcements');
        $routes->post('announcements/create', 'Announcement::create');

        $routes->get('announcements/delete/(:num)', 'Announcement::delete/$1');
        $routes->get('announcement', 'Announcement::index', ['filter' => 'roleauth:admin']);
    });

    // --------------------
    // Teacher Routes (teacher/* prefix)
    // --------------------
    $routes->group('teacher', ['filter' => 'roleauth:teacher'], function ($routes) {
        // Dashboard (redirects to /teacher_dashboard)
        $routes->get('dashboard', 'Teacher::dashboard');
        
        // Course Management
        $routes->get('courses', 'Teacher::courses');
        $routes->match(['get', 'post'], 'courses/create', 'Teacher::createCourse');
        $routes->match(['get', 'post'], 'courses/edit/(:num)', 'Teacher::editCourse/$1');
        $routes->get('courses/delete/(:num)', 'Teacher::deleteCourse/$1');
        $routes->get('courses/view/(:num)', 'Teacher::viewCourse/$1');
        $routes->get('courses/remove-student/(:num)/(:num)', 'Teacher::removeStudent/$1/$2');
        
        // Materials Upload (Teacher)
        $routes->get('course/(:num)/upload', 'Materials::upload/$1');
        $routes->post('course/(:num)/upload', 'Materials::upload/$1');
        
        // Announcements
        $routes->get('announcements', 'Teacher::announcements');
    });

    // --------------------
    // Student Routes (student/* prefix)
    // --------------------
    $routes->group('student', ['filter' => 'roleauth:student'], function ($routes) {
        // Dashboard (handled by Auth::dashboard via /dashboard route)
        $routes->get('dashboard', 'Auth::dashboard');
        
        // Courses
        $routes->get('courses', 'StudentController::courses');
        $routes->post('courses/enroll', 'StudentController::enroll');
    });

    // --------------------
    // Course Enrollment (All roles can enroll - mainly for students)
    // --------------------
    $routes->post('course/enroll', 'Course::enroll');
    
    // --------------------
    // Course Search (All logged-in users can search)
    // --------------------
    $routes->get('courses', 'Course::index');
    $routes->match(['get', 'post'], 'courses/search', 'Course::search');

    // --------------------
    // Materials (Delete/Download)
    // --------------------
    $routes->get('materials/delete/(:num)', 'Materials::delete/$1');
    $routes->get('materials/download/(:num)', 'Materials::download/$1');
    $routes->get('materials/course/(:num)', 'Materials::listByCourse/$1');

    // --------------------
    // Notifications (AJAX API Endpoints)
    // --------------------
    $routes->get('notifications', 'Notifications::get');
    $routes->post('notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');
    $routes->post('notifications/mark_all_read', 'Notifications::mark_all_as_read');

});

