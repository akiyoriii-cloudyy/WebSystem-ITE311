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
    $routes->get('/', 'Dashboard::index');

    // Role-specific dashboards
    $routes->get('admin', 'Dashboard::admin', ['filter' => 'role:admin']);
    $routes->get('instructor', 'Dashboard::instructor', ['filter' => 'role:instructor']);
    $routes->get('student', 'Dashboard::student', ['filter' => 'role:student']);
});
