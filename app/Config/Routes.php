<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ✅ Public routes
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// ✅ Auth routes
$routes->match(['get', 'post'], 'auth/login', 'Auth::login');
$routes->match(['get', 'post'], 'auth/register', 'Auth::register');
$routes->get('auth/logout', 'Auth::logout');

$routes->get('dashboard', 'Auth::dashboard');
$routes->get('manage-users', 'Auth::manageUsers');
$routes->get('manage-courses', 'Auth::manageCourses');

// Aliases
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::register');
$routes->get('logout', 'Auth::logout');

// ✅ Unified Dashboard (view inside views/auth/dashboard.php)
$routes->get('dashboard', 'Auth::dashboard');

$routes->post('/course/enroll', 'Course::enroll');
$routes->match(['get', 'post'], 'dashboard', 'Auth::dashboard');







