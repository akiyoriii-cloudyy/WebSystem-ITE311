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

            // Build query with academic structure
            $query = $db->table('courses')
                ->select('courses.id, courses.title, courses.description, courses.course_number, courses.instructor_id')
                ->select('users.name as instructor_name', false)
                ->join('users', 'courses.instructor_id = users.id', 'left');
            
            // Add academic structure joins
            try {
                if ($db->query("SHOW TABLES LIKE 'acad_years'")->getNumRows() > 0) {
                    $query->select('acad_years.acad_year as acad_year', false);
                    $query->join('acad_years', 'courses.acad_year_id = acad_years.id', 'left');
                }
                if ($db->query("SHOW TABLES LIKE 'semesters'")->getNumRows() > 0) {
                    $query->select('semesters.semester as semester_name', false);
                    $query->join('semesters', 'courses.semester_id = semesters.id', 'left');
                }
                if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                    $query->select('terms.term as term_name', false);
                    $query->join('terms', 'courses.term_id = terms.id', 'left');
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to join academic tables in search: ' . $e->getMessage());
            }

            // Apply search filter if term is provided
            if (!empty($searchTerm)) {
                $query->groupStart()
                    ->like('courses.title', $searchTerm)
                    ->orLike('courses.description', $searchTerm)
                    ->orLike('courses.course_number', $searchTerm);
                
                // Only search code column if it exists
                if ($hasCodeColumn) {
                    $query->orLike('courses.code', $searchTerm);
                }
                
                $query->groupEnd();
            }

            $result = $query->orderBy('courses.title', 'ASC')->get();
            
            if ($result === false || !is_object($result)) {
                // Try simpler query without joins if main query fails
                log_message('error', 'Course search query failed, trying fallback query');
                try {
                    $fallbackQuery = $db->table('courses')
                        ->select('courses.id, courses.title, courses.description, courses.course_number, courses.instructor_id');
                    
                    if (!empty($searchTerm)) {
                        $fallbackQuery->groupStart()
                            ->like('courses.title', $searchTerm)
                            ->orLike('courses.description', $searchTerm)
                            ->orLike('courses.course_number', $searchTerm)
                            ->groupEnd();
                    }
                    
                    $result = $fallbackQuery->orderBy('courses.title', 'ASC')->get();
                    
                    if ($result === false || !is_object($result)) {
                        throw new \Exception('Database query failed');
                    }
                } catch (\Exception $fallbackError) {
                    log_message('error', 'Course search fallback query also failed: ' . $fallbackError->getMessage());
                    throw new \Exception('Database query failed: ' . $fallbackError->getMessage());
                }
            }
            
            $courses = $result->getResultArray();

            // Get enrolled courses for students
            $enrolledCourses = [];
            $enrolledCourseIds = [];
            if ($userRole === 'student' && $db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                try {
                    $enrolledResult = $enrollmentModel->select('courses.id')
                        ->join('courses', 'enrollments.course_id = courses.id')
                        ->where('enrollments.user_id', $userId)
                        ->findAll();
                    
                    if (!empty($enrolledResult)) {
                        $enrolledCourses = $enrolledResult;
                        $enrolledCourseIds = array_column($enrolledCourses, 'id');
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to fetch enrolled courses in search: ' . $e->getMessage());
                    $enrolledCourseIds = [];
                }
            }

            // Format results
            $results = [];
            foreach ($courses as $course) {
                $results[] = [
                    'id' => $course['id'],
                    'title' => $course['title'] ?? $course['name'] ?? 'Untitled Course',
                    'description' => $course['description'] ?? '',
                    'code' => $course['code'] ?? '',
                    'course_number' => $course['course_number'] ?? '',
                    'instructor_name' => !empty($course['instructor_name']) ? $course['instructor_name'] : 'N/A',
                    'acad_year' => !empty($course['acad_year']) ? $course['acad_year'] : null,
                    'semester_name' => !empty($course['semester_name']) ? $course['semester_name'] : null,
                    'term_name' => !empty($course['term_name']) ? $course['term_name'] : null,
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
        // Set response type to JSON
        $this->response->setContentType('application/json');
        
        // ✅ Check session
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You must be logged in to enroll.'
            ])->setStatusCode(401);
        }

        // ✅ Prevent students from self-enrolling - only teachers and admins can enroll students
        $userRole = strtolower(session()->get('user_role') ?? '');
        if ($userRole === 'student') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Students cannot self-enroll. Please contact your teacher or admin to be enrolled in courses.'
            ])->setStatusCode(403);
        }

        $user_id = session()->get('user_id');
        $course_id = $this->request->getPost('course_id') ?? $this->request->getJSON(true)['course_id'] ?? null;

        // ✅ Validate course_id
        if (empty($course_id)) {
            log_message('error', 'Enrollment failed: No course_id provided. User: ' . $user_id);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No course selected. Please select a course to enroll.'
            ])->setStatusCode(400);
        }

        // Cast to integer
        $course_id = (int)$course_id;
        $user_id = (int)$user_id;

        $enrollmentModel = new EnrollmentModel();

        // ✅ Check if already enrolled
        if ($enrollmentModel->isAlreadyEnrolled($user_id, $course_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You are already enrolled in this course.'
            ])->setStatusCode(400);
        }

        // ✅ Validate department/program match for students
        $userModel = new UserModel();
        $user = $userModel->find($user_id);
        $db = \Config\Database::connect();
        $course = $db->table('courses')->where('id', $course_id)->get()->getRowArray();
        
        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found'
            ])->setStatusCode(400);
        }
        
        if (!$course) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course not found'
            ])->setStatusCode(400);
        }
        
        if (strtolower($user['role']) === 'student') {
            $userDeptId = $user['department_id'] ?? null;
            $userProgId = $user['program_id'] ?? null;
            $courseDeptId = $course['department_id'] ?? null;
            $courseProgId = $course['program_id'] ?? null;
            
            // If course has department/program specified, student must match
            if ($courseDeptId || $courseProgId) {
                $errors = [];
                
                // Check department match
                if ($courseDeptId && $userDeptId != $courseDeptId) {
                    $deptModel = new \App\Models\DepartmentModel();
                    $userDept = $deptModel->find($userDeptId);
                    $courseDept = $deptModel->find($courseDeptId);
                    $userDeptName = $userDept ? $userDept['department_name'] : 'Unknown';
                    $courseDeptName = $courseDept ? $courseDept['department_name'] : 'Unknown';
                    $errors[] = "You belong to '{$userDeptName}' but this course belongs to '{$courseDeptName}'.";
                }
                
                // Check program match (if both course and student have programs)
                if ($courseProgId && $userProgId && $userProgId != $courseProgId) {
                    $progModel = new \App\Models\ProgramModel();
                    $userProg = $progModel->find($userProgId);
                    $courseProg = $progModel->find($courseProgId);
                    $userProgName = $userProg ? $userProg['program_name'] : 'Unknown';
                    $courseProgName = $courseProg ? $courseProg['program_name'] : 'Unknown';
                    $errors[] = "You are in '{$userProgName}' program but this course is for '{$courseProgName}' program.";
                }
                
                // If student doesn't have department/program set, but course requires it
                if ($courseDeptId && !$userDeptId) {
                    $errors[] = "You must have a department assigned. Please contact admin to update your department.";
                }
                
                if ($courseProgId && !$userProgId) {
                    $errors[] = "You must have a program assigned. Please contact admin to update your program.";
                }
                
                if (!empty($errors)) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Enrollment failed: ' . implode(' ', $errors)
                    ])->setStatusCode(400);
                }
            }
        }

        // ✅ Insert enrollment with all required fields
        $data = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrolled_at' => date('Y-m-d H:i:s'),
            'enrollment_date' => date('Y-m-d H:i:s'),
            'completion_status' => 'ENROLLED'
        ];

        try {
            // Skip validation temporarily to avoid timestamp issues
            $enrollmentModel->skipValidation(true);
            $enrollmentModel->protect(false);
            
            $enrollmentId = $enrollmentModel->insert($data);
            
            // Re-enable validation and protection
            $enrollmentModel->skipValidation(false);
            $enrollmentModel->protect(true);
            
            if ($enrollmentId) {
                // ✅ Create notification for the student
                try {
                    $db = \Config\Database::connect();
                    $course = $db->table('courses')->where('id', $course_id)->get()->getRowArray();
                    $courseName = $course ? $course['title'] : 'a course';
                    
                    $notificationModel = new NotificationModel();
                    if (method_exists($notificationModel, 'createNotification')) {
                        $notificationModel->createNotification(
                            $user_id,
                            "You have been successfully enrolled in {$courseName}!"
                        );
                    }
                } catch (\Exception $notifError) {
                    // Don't fail enrollment if notification fails
                    log_message('warning', 'Notification creation failed: ' . $notifError->getMessage());
                }
                
                log_message('info', "User {$user_id} successfully enrolled in course {$course_id}");
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Enrollment successful! You have been enrolled in the course.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                $errors = $enrollmentModel->errors();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Enrollment failed. Please try again.';
                log_message('error', 'Enrollment insert failed. User: ' . $user_id . ', Course: ' . $course_id . ', Errors: ' . $errorMsg);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMsg
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Enrollment exception: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enrollment failed: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}