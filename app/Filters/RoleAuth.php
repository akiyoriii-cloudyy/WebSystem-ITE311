<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // ✅ If not logged in, redirect to login
        if (!$session->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        // ✅ If roles are specified in routes (like admin, teacher)
        if ($arguments) {
            $allowedRoles = array_map('strtolower', $arguments);
            $userRole = strtolower($session->get('user_role') ?? '');

            // ❌ If user role not in allowed roles
            if (!in_array($userRole, $allowedRoles)) {
                // Redirect based on actual role
                switch ($userRole) {
                    case 'admin':
                        return redirect()->to('/admin/dashboard')->with('error', 'Access denied.');
                    case 'teacher':
                        return redirect()->to('/teacher/dashboard')->with('error', 'Access denied.');
                    case 'student':
                        return redirect()->to('/announcements')->with('error', 'Access denied.');
                    default:
                        return redirect()->to('/auth/login')->with('error', 'Unauthorized access.');
                }
            }
        }

        // Continue request
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to modify after request
    }
}
