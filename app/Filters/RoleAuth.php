<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        //  Check if user is logged in (uses existing app keys)
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        //  Get current role and page path
        $role = strtolower($session->get('user_role') ?? ''); // admin, teacher, or student
        $uri  = service('uri')->getPath(); // e.g. "teacher/dashboard"

        //  Determine allowed role from route group argument
        $allowedRole = $arguments[0] ?? null;

        //  Block access if role doesn’t match
        if ($allowedRole && $role !== $allowedRole) {
            return redirect()->to('/announcements')->with('error', 'Access Denied: Insufficient Permissions.');
        }

        return; // proceed if authorized
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No after logic
    }
}
