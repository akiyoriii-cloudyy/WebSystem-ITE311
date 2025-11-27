<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\NotificationModel;
use CodeIgniter\Controller;

class Course extends BaseController
{
    // ✅ Display Courses Listing Page
    public function index()
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $db = \Config\Database::connect();
        $enrollmentModel = new EnrollmentModel();
        
        $userId = $session->get('user_id');
        $userRole = strtolower($session->get('user_role'));

        // Get all courses
        $courses = [];
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            $courses = $db->table('courses')
                ->select('courses.*, users.name as instructor_name')
                ->join('users', 'courses.instructor_id = users.id', 'left')
                ->orderBy('courses.created_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        // Get enrolled courses for students
        $enrolledCourses = [];
        if ($userRole === 'student' && $db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
            $enrolledCourses = $enrollmentModel->select('courses.id, courses.title, courses.description')
                ->join('courses', 'enrollments.course_id = courses.id')
                ->where('enrollments.user_id', $userId)
                ->findAll();
        }

        $data = [
            'title' => 'Courses',
            'courses' => $courses,
            'enrolledCourses' => $enrolledCourses,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole
        ];

        return view('courses/index', $data);
    }

    // ✅ Server-Side Search Method
    public function search()
    {
        $session = session();
        $isAJAX = $this->request->isAJAX() || $this->request->hasHeader('X-Requested-With');

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            if ($isAJAX) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Please login first.'
                ])->setStatusCode(401);
            }
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        try {
            $db = \Config\Database::connect();
            $enrollmentModel = new EnrollmentModel();
            
            $userId = $session->get('user_id');
            $userRole = strtolower($session->get('user_role'));

            // Get search term from GET or POST
            $searchTerm = $this->request->getGet('q') ?? $this->request->getPost('q') ?? '';

            // Check if code column exists
            $hasCodeColumn = false;
            try {
                $hasCodeColumn = $db->query("SHOW COLUMNS FROM courses WHERE Field = 'code'")->getNumRows() > 0;
            } catch (\Exception $e) {
                $hasCodeColumn = false;
            }

            // Build query
            $query = $db->table('courses')
                ->select('courses.*, users.name as instructor_name')
                ->join('users', 'courses.instructor_id = users.id', 'left');

            // Apply search filter if term is provided
            if (!empty($searchTerm)) {
                $query->groupStart()
                    ->like('courses.title', $searchTerm)
                    ->orLike('courses.description', $searchTerm);
                
                // Only search code column if it exists
                if ($hasCodeColumn) {
                    $query->orLike('courses.code', $searchTerm);
                }
                
                $query->groupEnd();
            }

            $result = $query->orderBy('courses.created_at', 'DESC')->get();
            
            if ($result === false) {
                throw new \Exception('Database query failed');
            }
            
            $courses = $result->getResultArray();

            // Get enrolled courses for students
            $enrolledCourses = [];
            if ($userRole === 'student' && $db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                $enrolledCourses = $enrollmentModel->select('courses.id')
                    ->join('courses', 'enrollments.course_id = courses.id')
                    ->where('enrollments.user_id', $userId)
                    ->findAll();
            }

            // Create enrolled course IDs array for quick lookup
            $enrolledCourseIds = array_column($enrolledCourses, 'id');

            // Format results
            $results = [];
            foreach ($courses as $course) {
                $results[] = [
                    'id' => $course['id'],
                    'title' => $course['title'] ?? $course['name'] ?? 'Untitled Course',
                    'description' => $course['description'] ?? '',
                    'code' => $course['code'] ?? '',
                    'instructor_name' => $course['instructor_name'] ?? 'N/A',
                    'is_enrolled' => in_array($course['id'], $enrolledCourseIds)
                ];
            }

            // Return JSON for AJAX requests
            if ($isAJAX) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'results' => $results,
                    'count' => count($results),
                    'search_term' => $searchTerm
                ]);
            }
        } catch (\Exception $e) {
            // Return error as JSON for AJAX requests
            if ($isAJAX) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Search failed: ' . $e->getMessage()
                ])->setStatusCode(500);
            }
            throw $e;
        }

        // Return view for regular requests
        $data = [
            'title' => 'Search Results',
            'courses' => $courses,
            'enrolledCourses' => $enrolledCourses,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
            'search_term' => $searchTerm
        ];

        return view('courses/index', $data);
    }

    public function enroll()
    {
        // ✅ Check session
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You must be logged in to enroll.'
            ]);
        }

        $user_id = session()->get('user_id');
        $course_id = $this->request->getPost('course_id');

        // ✅ Validate course_id
        if (empty($course_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No course selected.'
            ]);
        }

        $enrollmentModel = new EnrollmentModel();

        // ✅ Check if already enrolled
        if ($enrollmentModel->isAlreadyEnrolled($user_id, $course_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You are already enrolled in this course.'
            ]);
        }

        // ✅ Insert enrollment
        $data = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrolled_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($enrollmentModel->insert($data)) {
                // ✅ Create notification for the student
                $db = \Config\Database::connect();
                $course = $db->table('courses')->where('id', $course_id)->get()->getRowArray();
                $courseName = $course ? $course['title'] : 'a course';
                
                $notificationModel = new NotificationModel();
                $notificationModel->createNotification(
                    $user_id,
                    "You have been successfully enrolled in {$courseName}!"
                );
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Enrollment successful!'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Enrollment failed. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}