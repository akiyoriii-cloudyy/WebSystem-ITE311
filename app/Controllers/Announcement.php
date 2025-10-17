<?php namespace App\Controllers;

use App\Models\AnnouncementModel;

class Announcement extends BaseController
{
    public function index()
    {
        $model = new AnnouncementModel();
        $announcements = $model->getAllOrderedDesc();

        return view('announcements', ['announcements' => $announcements]);
    }
}
