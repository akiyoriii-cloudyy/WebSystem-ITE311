<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;

class Teacher extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ Teacher Dashboard (redirects to unified dashboard)
    public function dashboard()
    {
        // Since Auth::dashboard handles all role-based dashboards,
        // redirect teacher to the unified dashboard at /teacher_dashboard
        return redirect()->to('/teacher_dashboard');
    }

    // ✅ Teacher Courses Management Page
    public function courses()
    {
        $session = session();

        // RoleAuth filter ensures only teacher can access
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        // Get courses assigned to this teacher
        $courses = [];
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            $courses = $db->table('courses')
                          ->where('instructor_id', $userId)
                          ->orderBy('created_at', 'DESC')
                          ->get()
                          ->getResultArray();

            // Add enrolled student count to each course
            foreach ($courses as &$course) {
                if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                    $course['student_count'] = $db->table('enrollments')
                        ->where('course_id', $course['id'])
                        ->countAllResults();
                } else {
                    $course['student_count'] = 0;
                }
            }
        }

        $data = [
            'title'     => 'My Courses',
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
            'courses'   => $courses,
            'stats'     => [
                'total_courses' => count($courses)
            ]
        ];

        return view('teacher/courses', $data);
    }

    // ✅ View Course Details with Enrolled Students
    public function viewCourse($courseId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        // Check if courses table exists
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() === 0) {
            return redirect()->to('/teacher/courses')->with('error', 'Courses table not found.');
        }

        // Get course details (ensure it belongs to this teacher)
        $course = $db->table('courses')
                     ->where('id', $courseId)
                     ->where('instructor_id', $userId)
                     ->get()
                     ->getRowArray();

        if (!$course) {
            return redirect()->to('/teacher/courses')->with('error', 'Course not found or access denied.');
        }

        // Get enrolled students
        $enrolledStudents = [];
        if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
            $enrolledStudents = $db->table('enrollments')
                ->select('users.id, users.name, users.email, enrollments.created_at as enrolled_at')
                ->join('users', 'enrollments.user_id = users.id')
                ->where('enrollments.course_id', $courseId)
                ->orderBy('enrollments.created_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        $data = [
            'title'            => $course['title'] ?? 'Course Details',
            'user_name'        => $session->get('user_name'),
            'user_role'        => $session->get('user_role'),
            'course'           => $course,
            'enrolledStudents' => $enrolledStudents,
            'student_count'    => count($enrolledStudents)
        ];

        return view('teacher/course_details', $data);
    }

    // ✅ Create New Course
    public function createCourse()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        // Show form on GET
        if ($this->request->getMethod() === 'GET') {
            $data = [
                'title'     => 'Create New Course',
                'user_name' => $session->get('user_name'),
                'user_role' => $session->get('user_role')
            ];
            return view('teacher/create_course', $data);
        }

        // Process form on POST
        if ($this->request->getMethod() === 'POST') {
            // Validate input
            if (!$this->validate([
                'title'       => 'required|min_length[3]|max_length[255]',
                'description' => 'required|min_length[10]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $db = \Config\Database::connect();
            $userId = $session->get('user_id');

            // Check if courses table exists
            if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() === 0) {
                return redirect()->to('/teacher/courses')->with('error', 'Courses table not found. Please contact admin.');
            }

            // Insert course
            $db->table('courses')->insert([
                'title'         => $this->request->getPost('title'),
                'description'   => $this->request->getPost('description'),
                'instructor_id' => $userId,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]);

            return redirect()->to('/teacher/courses')->with('success', '✅ Course created successfully!');
        }
    }

    // ✅ Edit Course
    public function editCourse($courseId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        // Check if courses table exists
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() === 0) {
            return redirect()->to('/teacher/courses')->with('error', 'Courses table not found.');
        }

        // Get course (ensure it belongs to this teacher)
        $course = $db->table('courses')
                     ->where('id', $courseId)
                     ->where('instructor_id', $userId)
                     ->get()
                     ->getRowArray();

        if (!$course) {
            return redirect()->to('/teacher/courses')->with('error', 'Course not found or access denied.');
        }

        // Show form on GET
        if ($this->request->getMethod() === 'GET') {
            $data = [
                'title'     => 'Edit Course',
                'user_name' => $session->get('user_name'),
                'user_role' => $session->get('user_role'),
                'course'    => $course
            ];
            return view('teacher/edit_course', $data);
        }

        // Process form on POST
        if ($this->request->getMethod() === 'POST') {
            // Validate input
            if (!$this->validate([
                'title'       => 'required|min_length[3]|max_length[255]',
                'description' => 'required|min_length[10]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Update course
            $db->table('courses')->where('id', $courseId)->update([
                'title'       => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);

            return redirect()->to('/teacher/courses')->with('success', '✅ Course updated successfully!');
        }
    }

    // ✅ Delete Course
    public function deleteCourse($courseId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        // Verify ownership
        $course = $db->table('courses')
                     ->where('id', $courseId)
                     ->where('instructor_id', $userId)
                     ->get()
                     ->getRowArray();

        if (!$course) {
            return redirect()->to('/teacher/courses')->with('error', 'Course not found or access denied.');
        }

        // Delete enrollments first (if table exists) - CASCADE DELETE
        if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
            $db->table('enrollments')->where('course_id', $courseId)->delete();
        }

        // Delete course
        $db->table('courses')->where('id', $courseId)->delete();

        return redirect()->to('/teacher/courses')->with('success', '✅ Course deleted successfully!');
    }

    // ✅ View Announcements
    public function announcements()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $announcementModel = new AnnouncementModel();
        $announcements = $announcementModel
            ->select('announcements.*, users.name as author_name')
            ->join('users', 'announcements.created_by = users.id', 'left')
            ->orderBy('announcements.created_at', 'DESC')
            ->findAll();

        $data = [
            'title'         => 'View Announcements',
            'announcements' => $announcements,
            'user_name'     => $session->get('user_name'),
            'user_role'     => $session->get('user_role')
        ];

        return view('teacher/announcements', $data);
    }

    // ✅ Remove Student from Course (Unenroll)
    public function removeStudent($courseId, $studentId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        // Verify course ownership
        $course = $db->table('courses')
                     ->where('id', $courseId)
                     ->where('instructor_id', $userId)
                     ->get()
                     ->getRowArray();

        if (!$course) {
            return redirect()->to('/teacher/courses')->with('error', 'Course not found or access denied.');
        }

        // Remove enrollment
        if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
            $db->table('enrollments')
               ->where('course_id', $courseId)
               ->where('user_id', $studentId)
               ->delete();
        }

        return redirect()->to('/teacher/courses/view/' . $courseId)->with('success', '✅ Student removed from course!');
    }
}