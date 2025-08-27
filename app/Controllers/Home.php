<?php
namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        helper('url');
        return view('index', ['title' => 'Home']);
    }

    public function about()
    {
        helper('url');
        return view('about', ['title' => 'About']);
    }

    public function contact()
    {
        helper('url');
        return view('contact', ['title' => 'Contact']);
    }
}
