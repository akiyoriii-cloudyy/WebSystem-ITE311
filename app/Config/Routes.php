<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public pages
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// Authentication
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');

$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::attemptRegister');

$routes->get('logout', 'Auth::logout');

// Dashboard routes (role-based)
$routes->group('dashboard', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Dashboard::index');  // General dashboard page

    // Role-specific dashboards (admin and user roles only)
    $routes->get('admin', 'Dashboard::admin', ['filter' => 'role:admin']);  // Admin dashboard
    $routes->get('user', 'Dashboard::user', ['filter' => 'role:user']);  // User dashboard
});
