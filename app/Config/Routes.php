<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

$routes->get('/',        'Auth::login');          // default to login
$routes->get('login',    'Auth::login');
$routes->post('login',   'Auth::attemptLogin');

$routes->get('register', 'Auth::register');
$routes->post('register','Auth::attemptRegister');

$routes->get('logout',   'Auth::logout');

$routes->get('dashboard','Dashboard::index');     // shown after login


