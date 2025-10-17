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

// ✅ Unified Dashboard (for all roles — admin, teacher, student)
// This replaces /admin/dashboard and /teacher/dashboard routes
$routes->match(['get', 'post'], 'auth/dashboard', 'Auth::dashboard');
$routes->get('dashboard', 'Auth::dashboard');

// ✅ Announcements (for students/teachers/admins)
$routes->get('announcements', 'Announcement::index');
$routes->post('announcements/create', 'Announcement::create');
$routes->get('announcements/delete/(:num)', 'Announcement::delete/$1');

// ✅ Courses & Enrollment (if used in your lab)
$routes->post('course/enroll', 'Course::enroll');
$routes->get('manage-users', 'Auth::manageUsers');
$routes->get('manage-courses', 'Auth::manageCourses');
