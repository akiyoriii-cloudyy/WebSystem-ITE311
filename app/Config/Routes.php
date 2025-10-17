<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ✅ Public pages
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// ✅ Authentication routes (only 4 main methods)
$routes->match(['get', 'post'], 'auth/login', 'Auth::login');
$routes->match(['get', 'post'], 'auth/register', 'Auth::register');
$routes->get('auth/logout', 'Auth::logout');

// ✅ Aliases (optional shortcuts)
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::register');
$routes->get('logout', 'Auth::logout');

// ✅ Protected routes (require RoleAuth filter)
$routes->group('', ['filter' => 'roleauth'], function ($routes) {

    // 🔹 Unified Dashboard (Admin, Teacher, Student)
    $routes->match(['get', 'post'], 'auth/dashboard', 'Auth::dashboard');
    $routes->get('dashboard', 'Auth::dashboard');

    // 🔹 Announcements (all users can view)
    $routes->get('announcements', 'Announcements::index');

    // 🔹 Admin-only announcement management
    $routes->group('', ['filter' => 'roleauth:admin'], function ($routes) {
        // Create announcement
        $routes->post('announcements/create', 'Announcements::create');
        // Delete announcement
        $routes->get('announcements/delete/(:num)', 'Announcements::delete/$1');
    });

    // 🔹 Courses & Enrollment
    $routes->post('course/enroll', 'Course::enroll');

    // 🔹 User and course management
    $routes->get('manage-users', 'Auth::manageUsers');
    $routes->get('manage-courses', 'Auth::manageCourses');
});
