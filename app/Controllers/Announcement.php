<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;

class Announcements extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        $announcementModel = new AnnouncementModel();
        $data = [
            'title' => 'Announcements',
            'user_role' => $session->get('user_role'),
            'user_name' => $session->get('user_name'),
            'announcements' => $announcementModel->getAllAnnouncements(),
        ];

        return view('auth/announcements', $data);
    }

    public function create()
    {
        $session = session();
        if ($session->get('user_role') !== 'teacher') {
            return redirect()->to('/announcements')->with('error', 'Only teachers can post announcements.');
        }

        if ($this->request->getMethod() === 'post') {
            $validation = $this->validate([
                'title'   => 'required|min_length[3]',
                'content' => 'required|min_length[5]',
            ]);

            if (!$validation) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $announcementModel = new AnnouncementModel();
            $announcementModel->insert([
                'title'      => $this->request->getPost('title'),
                'content'    => $this->request->getPost('content'),
                'created_by' => $session->get('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/announcements')->with('success', 'Announcement posted successfully!');
        }
    }

    public function delete($id)
    {
        $session = session();
        if ($session->get('user_role') !== 'teacher') {
            return redirect()->to('/announcements')->with('error', 'Only teachers can delete announcements.');
        }

        $announcementModel = new AnnouncementModel();
        $announcementModel->delete($id);
        return redirect()->to('/announcements')->with('success', 'Announcement deleted.');
    }
}
