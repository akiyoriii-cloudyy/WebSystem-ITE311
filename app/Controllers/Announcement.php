<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;
use App\Models\UserModel;

class Announcement extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ Show announcements on dashboard
    public function index()
    {
        $session = session();

        // 🔒 Require login
        if (!$session->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        $announcementModel = new AnnouncementModel();

        // Fetch all announcements (newest first)
        $announcements = $announcementModel->getAllAnnouncements();

        // Pass data to unified dashboard
        $data = [
            'title'         => 'Dashboard with Announcements',
            'user_role'     => $session->get('user_role'),
            'user_name'     => $session->get('user_name'),
            'announcements' => $announcements,
        ];

        return view('auth/dashboard', $data);
    }

    // ✅ Create announcement (Admin only)
    public function create()
    {
        $session = session();

        // 🔒 Restrict to admin role
        if ($session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Only admins can post announcements.');
        }

        if ($this->request->getMethod() === 'post') {
            // Validate form input
            $validation = $this->validate([
                'title'   => 'required|min_length[3]|max_length[255]',
                'content' => 'required|min_length[5]',
            ]);

            if (!$validation) {
                // Return back with validation errors
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Save announcement
            $announcementModel = new AnnouncementModel();
            $announcementModel->save([
                'title'      => $this->request->getPost('title'),
                'content'    => $this->request->getPost('content'),
                'created_by' => $session->get('user_id') ?? 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/dashboard')->with('success', '✅ Announcement posted successfully!');
        }

        // Default: redirect to dashboard
        return redirect()->to('/dashboard');
    }

    // ✅ Delete announcement (Admin only)
    public function delete($id)
    {
        $session = session();

        // 🔒 Restrict to admin role
        if ($session->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Only admins can delete announcements.');
        }

        $announcementModel = new AnnouncementModel();
        $announcement = $announcementModel->find($id);

        if (!$announcement) {
            return redirect()->to('/dashboard')->with('error', 'Announcement not found.');
        }

        // Delete announcement
        $announcementModel->delete($id);

        return redirect()->to('/dashboard')->with('success', '🗑️ Announcement deleted successfully!');
    }
}
