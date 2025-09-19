<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public pages
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// Auth routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::loginPost');

$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::registerPost');

$routes->get('logout', 'Auth::logout');

// Dashboard
$routes->get('dashboard', 'Auth::dashboard');

