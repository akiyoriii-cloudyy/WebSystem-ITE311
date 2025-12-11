<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AnnouncementModel;
use App\Models\EnrollmentModel;
use App\Models\CourseScheduleModel;
use App\Models\NotificationModel;

class Admin extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ Admin Dashboard (redirects to unified dashboard)
    public function dashboard()
    {
        // Since Auth::dashboard handles all role-based dashboards,
        // redirect admin to the unified dashboard at /admin_dashboard
        return redirect()->to('/admin_dashboard');
    }

    // ✅ Manage Users
    public function users()
    {
        helper('security'); // Load security helper for throughly token
        $session = session();

        // RoleAuth filter ensures only admin can access
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $userModel = new UserModel();
        
        // Show ALL users including deleted ones so admins can restore them
        // Users with status = 'deleted' are preserved in the database and can be restored
        $db = \Config\Database::connect();
        try {
            $result = $db->table('users')
                        ->select('id, name, email, role, status, department_id, program_id, student_id, created_at')
                        ->orderBy('status', 'ASC')
                        ->orderBy('created_at', 'DESC')
                        ->get();
            
            if ($result !== false && is_object($result)) {
                $users = $result->getResultArray();
            } else {
                $users = [];
                log_message('error', 'Failed to fetch users in Admin::users()');
            }
        } catch (\Exception $e) {
            $users = [];
            log_message('error', 'Failed to fetch users: ' . $e->getMessage());
        }

        // Fetch departments and programs for student assignment
        $departments = [];
        $programs = [];
        try {
            $deptModel = new \App\Models\DepartmentModel();
            $departments = $deptModel->getActiveDepartments();
            
            $progModel = new \App\Models\ProgramModel();
            $programs = $progModel->getActivePrograms();
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch departments/programs: ' . $e->getMessage());
        }

        $data = [
            'title'     => 'Manage Users',
            'users'     => $users,
            'departments' => $departments,
            'programs'   => $programs,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/manage_users', $data);
    }

    // ✅ Manage Courses
    public function manageCourses()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        
        // Check if courses table exists
        $courses = [];
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            try {
                $result = $db->table('courses')
                          ->select('courses.*, users.name as instructor_name')
                          ->join('users', 'courses.instructor_id = users.id', 'left')
                          ->orderBy('courses.created_at', 'DESC')
                          ->get();
                
                if ($result !== false && is_object($result)) {
                    $courses = $result->getResultArray();
                } else {
                    // If join fails, try without join
                    try {
                        $result = $db->table('courses')
                            ->orderBy('created_at', 'DESC')
                            ->get();
                        if ($result !== false && is_object($result)) {
                            $courses = $result->getResultArray();
                        }
                    } catch (\Exception $e) {
                        $courses = [];
                        log_message('error', 'Failed to fetch courses: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                // If query fails, set empty array
                $courses = [];
                log_message('error', 'Failed to fetch courses: ' . $e->getMessage());
            }
        }

        // Fetch dropdown data for course creation
        $acadYears = [];
        $semesters = [];
        $terms = [];
        $teachers = [];
        $departments = [];
        $programs = [];

        try {
            if ($db->query("SHOW TABLES LIKE 'acad_years'")->getNumRows() > 0) {
                $result = $db->table('acad_years')->orderBy('acad_year', 'DESC')->get();
                if ($result !== false && is_object($result)) {
                    $acadYears = $result->getResultArray();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch academic years: ' . $e->getMessage());
        }

        try {
            if ($db->query("SHOW TABLES LIKE 'semesters'")->getNumRows() > 0) {
                // Join with academic years for better display
                $result = $db->table('semesters')
                    ->select('semesters.id, semesters.semester, semesters.semester_code, semesters.acad_year_id, semesters.is_active')
                    ->select('acad_years.acad_year', false)
                    ->join('acad_years', 'semesters.acad_year_id = acad_years.id', 'left')
                    ->orderBy('semesters.start_date', 'DESC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $semesters = $result->getResultArray();
                } else {
                    // Fallback: try without join
                    $result = $db->table('semesters')
                        ->select('semesters.id, semesters.semester, semesters.semester_code, semesters.acad_year_id, semesters.is_active')
                        ->orderBy('semesters.start_date', 'DESC')
                        ->get();
                    if ($result !== false && is_object($result)) {
                        $semesters = $result->getResultArray();
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch semesters: ' . $e->getMessage());
        }

        try {
            if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                // Join with semesters and academic years for better display
                $result = $db->table('terms')
                    ->select('terms.id, terms.term, terms.term_code, terms.semester_id, terms.is_active')
                    ->select('semesters.semester, semesters.acad_year_id', false)
                    ->select('acad_years.acad_year', false)
                    ->join('semesters', 'terms.semester_id = semesters.id', 'left')
                    ->join('acad_years', 'semesters.acad_year_id = acad_years.id', 'left')
                    ->orderBy('terms.start_date', 'DESC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $terms = $result->getResultArray();
                } else {
                    // Fallback: try without joins
                    $result = $db->table('terms')
                        ->select('terms.id, terms.term, terms.term_code, terms.semester_id, terms.is_active')
                        ->orderBy('terms.start_date', 'DESC')
                        ->get();
                    if ($result !== false && is_object($result)) {
                        $terms = $result->getResultArray();
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch terms: ' . $e->getMessage());
        }

        try {
            // Fetch teachers (users with role 'teacher')
            $result = $db->table('users')
                ->where('role', 'teacher')
                ->orWhere('role', 'Teacher')
                ->orderBy('name', 'ASC')
                ->get();
            if ($result !== false && is_object($result)) {
                $teachers = $result->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch teachers: ' . $e->getMessage());
        }

        try {
            // Fetch departments
            if ($db->query("SHOW TABLES LIKE 'departments'")->getNumRows() > 0) {
                $result = $db->table('departments')
                    ->where('is_active', 1)
                    ->orderBy('department_name', 'ASC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $departments = $result->getResultArray();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch departments: ' . $e->getMessage());
        }

        try {
            // Fetch programs
            if ($db->query("SHOW TABLES LIKE 'programs'")->getNumRows() > 0) {
                $result = $db->table('programs')
                    ->select('programs.*, departments.department_name, departments.department_code')
                    ->join('departments', 'departments.id = programs.department_id', 'left')
                    ->where('programs.is_active', 1)
                    ->orderBy('departments.department_name', 'ASC')
                    ->orderBy('programs.program_name', 'ASC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $programs = $result->getResultArray();
                } else {
                    // Fallback: try without join
                    $result = $db->table('programs')
                        ->where('is_active', 1)
                        ->orderBy('program_name', 'ASC')
                        ->get();
                    if ($result !== false && is_object($result)) {
                        $programs = $result->getResultArray();
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch programs: ' . $e->getMessage());
        }

        $data = [
            'title'      => 'Manage Courses',
            'courses'    => $courses,
            'acadYears'  => $acadYears,
            'semesters'  => $semesters,
            'terms'      => $terms,
            'teachers'   => $teachers,
            'departments' => $departments,
            'programs'   => $programs,
            'user_name'  => $session->get('user_name'),
            'user_role'  => $session->get('user_role')
        ];

        return view('admin/manage_courses', $data);
    }

    // ✅ Create Course
    public function createCourse()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access Denied.'
            ])->setStatusCode(403);
        }

        $this->response->setContentType('application/json');

        $db = \Config\Database::connect();

        // Get form data
        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $course_number = $this->request->getPost('course_number');
        $instructor_id = $this->request->getPost('instructor_id');
        $acad_year_id = $this->request->getPost('acad_year_id') ?: null;
        $semester_id = $this->request->getPost('semester_id') ?: null;
        $term_id = $this->request->getPost('term_id') ?: null;
        $units = $this->request->getPost('units') ?: null;
        $department_id = $this->request->getPost('department_id') ?: null;
        $program_id = $this->request->getPost('program_id') ?: null;

        // Validation
        if (empty($title)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course title is required.'
            ])->setStatusCode(400);
        }

        // Validate instructor if provided
        if (!empty($instructor_id)) {
            $instructor = $db->table('users')
                ->where('id', $instructor_id)
                ->whereIn('role', ['teacher', 'Teacher'])
                ->get()
                ->getRowArray();
            
            if (!$instructor) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid instructor selected.'
                ])->setStatusCode(400);
            }
        }

        // Validate academic structure if provided
        if (!empty($acad_year_id)) {
            if ($db->query("SHOW TABLES LIKE 'acad_years'")->getNumRows() > 0) {
                $acadYear = $db->table('acad_years')->where('id', $acad_year_id)->get()->getRowArray();
                if (!$acadYear) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid academic year selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        if (!empty($semester_id)) {
            if ($db->query("SHOW TABLES LIKE 'semesters'")->getNumRows() > 0) {
                $result = $db->table('semesters')->where('id', $semester_id)->get();
                if ($result !== false && is_object($result)) {
                    $semester = $result->getRowArray();
                    if (!$semester) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid semester selected.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid semester selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        if (!empty($term_id)) {
            if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                $result = $db->table('terms')->where('id', $term_id)->get();
                if ($result !== false && is_object($result)) {
                    $term = $result->getRowArray();
                    if (!$term) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid term selected.'
                        ])->setStatusCode(400);
                    }
                    // Validate that term belongs to selected semester if both are provided
                    if (!empty($semester_id) && !empty($term['semester_id']) && (int)$term['semester_id'] !== (int)$semester_id) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Selected term does not belong to the selected semester.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid term selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        // Validate units if provided
        if (!empty($units)) {
            $units = (int)$units;
            if ($units < 0 || $units > 10) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Units must be between 0 and 10.'
                ])->setStatusCode(400);
            }
        }

        // Validate department if provided
        if (!empty($department_id)) {
            if ($db->query("SHOW TABLES LIKE 'departments'")->getNumRows() > 0) {
                $result = $db->table('departments')->where('id', $department_id)->get();
                if ($result !== false && is_object($result)) {
                    $department = $result->getRowArray();
                    if (!$department) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid department selected.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid department selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        // Validate program if provided
        if (!empty($program_id)) {
            if ($db->query("SHOW TABLES LIKE 'programs'")->getNumRows() > 0) {
                $result = $db->table('programs')->where('id', $program_id)->get();
                if ($result !== false && is_object($result)) {
                    $program = $result->getRowArray();
                    if (!$program) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid program selected.'
                        ])->setStatusCode(400);
                    }
                    // Validate that program belongs to selected department if both are provided
                    if (!empty($department_id) && !empty($program['department_id']) && (int)$program['department_id'] !== (int)$department_id) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Selected program does not belong to the selected department.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid program selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        // Prepare data
        $data = [
            'title' => $title,
            'description' => $description ?: null,
            'course_number' => $course_number ?: null,
            'instructor_id' => !empty($instructor_id) ? (int)$instructor_id : null,
            'acad_year_id' => !empty($acad_year_id) ? (int)$acad_year_id : null,
            'semester_id' => !empty($semester_id) ? (int)$semester_id : null,
            'term_id' => !empty($term_id) ? (int)$term_id : null,
            'units' => !empty($units) ? (int)$units : null,
            'department_id' => !empty($department_id) ? (int)$department_id : null,
            'program_id' => !empty($program_id) ? (int)$program_id : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $courseId = $db->table('courses')->insert($data);
            
            if ($courseId) {
                log_message('info', "Course created successfully. ID: {$courseId}, Title: {$title}");
                
                // ✅ Create notification for admin (course creator)
                try {
                    $session = session();
                    $adminId = $session->get('user_id');
                    if ($adminId) {
                        $notificationModel = new NotificationModel();
                        $notificationModel->createNotification(
                            $adminId,
                            "Course '{$title}' has been created successfully!"
                        );
                    }
                } catch (\Exception $notifError) {
                    log_message('warning', 'Notification creation failed: ' . $notifError->getMessage());
                }
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Course created successfully!',
                    'course_id' => $courseId,
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to create course. Please try again.'
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Course creation failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create course: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    // ✅ Get Course Data (for editing)
    public function getCourse($courseId)
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access Denied.'
            ])->setStatusCode(403);
        }

        $db = \Config\Database::connect();
        $course = $db->table('courses')->where('id', (int)$courseId)->get()->getRowArray();

        if (!$course) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course not found.'
            ])->setStatusCode(404);
        }

        // Also fetch dropdown options for the edit form
        $teachers = [];
        $acadYears = [];
        $semesters = [];
        $terms = [];
        $departments = [];
        $programs = [];

        try {
            // Get teachers
            $result = $db->table('users')
                ->whereIn('role', ['teacher', 'Teacher'])
                ->orderBy('name', 'ASC')
                ->get();
            if ($result !== false && is_object($result)) {
                $teachers = $result->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch teachers: ' . $e->getMessage());
        }

        try {
            // Get academic years
            if ($db->query("SHOW TABLES LIKE 'acad_years'")->getNumRows() > 0) {
                $result = $db->table('acad_years')->orderBy('acad_year', 'DESC')->get();
                if ($result !== false && is_object($result)) {
                    $acadYears = $result->getResultArray();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch academic years: ' . $e->getMessage());
        }

        try {
            // Get semesters
            if ($db->query("SHOW TABLES LIKE 'semesters'")->getNumRows() > 0) {
                $result = $db->table('semesters')
                    ->select('semesters.*, acad_years.acad_year')
                    ->join('acad_years', 'acad_years.id = semesters.acad_year_id', 'left')
                    ->orderBy('semesters.start_date', 'DESC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $semesters = $result->getResultArray();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch semesters: ' . $e->getMessage());
        }

        try {
            // Get terms
            if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                $result = $db->table('terms')
                    ->select('terms.*, semesters.semester')
                    ->join('semesters', 'terms.semester_id = semesters.id', 'left')
                    ->orderBy('terms.start_date', 'DESC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $terms = $result->getResultArray();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch terms: ' . $e->getMessage());
        }

        try {
            // Get departments
            if ($db->query("SHOW TABLES LIKE 'departments'")->getNumRows() > 0) {
                $result = $db->table('departments')
                    ->where('is_active', 1)
                    ->orderBy('department_name', 'ASC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $departments = $result->getResultArray();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch departments: ' . $e->getMessage());
        }

        try {
            // Get programs
            if ($db->query("SHOW TABLES LIKE 'programs'")->getNumRows() > 0) {
                $result = $db->table('programs')
                    ->select('programs.*, departments.department_name, departments.department_code')
                    ->join('departments', 'departments.id = programs.department_id', 'left')
                    ->where('programs.is_active', 1)
                    ->orderBy('departments.department_name', 'ASC')
                    ->orderBy('programs.program_name', 'ASC')
                    ->get();
                if ($result !== false && is_object($result)) {
                    $programs = $result->getResultArray();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch programs: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'status' => 'success',
            'course' => $course,
            'dropdowns' => [
                'teachers' => $teachers,
                'acadYears' => $acadYears,
                'semesters' => $semesters,
                'terms' => $terms,
                'departments' => $departments,
                'programs' => $programs
            ]
        ]);
    }

    // ✅ Update Course
    public function updateCourse($courseId)
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access Denied.'
            ])->setStatusCode(403);
        }

        $this->response->setContentType('application/json');
        $db = \Config\Database::connect();

        // Check if course exists
        $course = $db->table('courses')->where('id', (int)$courseId)->get()->getRowArray();
        if (!$course) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course not found.'
            ])->setStatusCode(404);
        }

        // Get form data
        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $course_number = $this->request->getPost('course_number');
        $instructor_id = $this->request->getPost('instructor_id');
        $acad_year_id = $this->request->getPost('acad_year_id') ?: null;
        $semester_id = $this->request->getPost('semester_id') ?: null;
        $term_id = $this->request->getPost('term_id') ?: null;
        $units = $this->request->getPost('units') ?: null;
        $department_id = $this->request->getPost('department_id') ?: null;
        $program_id = $this->request->getPost('program_id') ?: null;

        // Validation
        if (empty($title)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course title is required.'
            ])->setStatusCode(400);
        }

        // Validate instructor if provided
        if (!empty($instructor_id)) {
            $instructor = $db->table('users')
                ->where('id', $instructor_id)
                ->whereIn('role', ['teacher', 'Teacher'])
                ->get()
                ->getRowArray();
            
            if (!$instructor) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid instructor selected.'
                ])->setStatusCode(400);
            }
        }

        // Validate academic structure if provided
        if (!empty($acad_year_id)) {
            if ($db->query("SHOW TABLES LIKE 'acad_years'")->getNumRows() > 0) {
                $acadYear = $db->table('acad_years')->where('id', $acad_year_id)->get()->getRowArray();
                if (!$acadYear) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid academic year selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        if (!empty($semester_id)) {
            if ($db->query("SHOW TABLES LIKE 'semesters'")->getNumRows() > 0) {
                $result = $db->table('semesters')->where('id', $semester_id)->get();
                if ($result !== false && is_object($result)) {
                    $semester = $result->getRowArray();
                    if (!$semester) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid semester selected.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid semester selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        if (!empty($term_id)) {
            if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                $result = $db->table('terms')->where('id', $term_id)->get();
                if ($result !== false && is_object($result)) {
                    $term = $result->getRowArray();
                    if (!$term) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid term selected.'
                        ])->setStatusCode(400);
                    }
                    // Validate that term belongs to selected semester if both are provided
                    if (!empty($semester_id) && !empty($term['semester_id']) && (int)$term['semester_id'] !== (int)$semester_id) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Selected term does not belong to the selected semester.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid term selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        // Validate units if provided
        if (!empty($units)) {
            $units = (int)$units;
            if ($units < 0 || $units > 10) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Units must be between 0 and 10.'
                ])->setStatusCode(400);
            }
        }

        // Validate department if provided
        if (!empty($department_id)) {
            if ($db->query("SHOW TABLES LIKE 'departments'")->getNumRows() > 0) {
                $result = $db->table('departments')->where('id', $department_id)->get();
                if ($result !== false && is_object($result)) {
                    $department = $result->getRowArray();
                    if (!$department) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid department selected.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid department selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        // Validate program if provided
        if (!empty($program_id)) {
            if ($db->query("SHOW TABLES LIKE 'programs'")->getNumRows() > 0) {
                $result = $db->table('programs')->where('id', $program_id)->get();
                if ($result !== false && is_object($result)) {
                    $program = $result->getRowArray();
                    if (!$program) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid program selected.'
                        ])->setStatusCode(400);
                    }
                    // Validate that program belongs to selected department if both are provided
                    if (!empty($department_id) && !empty($program['department_id']) && (int)$program['department_id'] !== (int)$department_id) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Selected program does not belong to the selected department.'
                        ])->setStatusCode(400);
                    }
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid program selected.'
                    ])->setStatusCode(400);
                }
            }
        }

        // Prepare update data
        $updateData = [
            'title' => $title,
            'description' => $description ?: null,
            'course_number' => $course_number ?: null,
            'instructor_id' => !empty($instructor_id) ? (int)$instructor_id : null,
            'acad_year_id' => !empty($acad_year_id) ? (int)$acad_year_id : null,
            'semester_id' => !empty($semester_id) ? (int)$semester_id : null,
            'term_id' => !empty($term_id) ? (int)$term_id : null,
            'units' => !empty($units) ? (int)$units : null,
            'department_id' => !empty($department_id) ? (int)$department_id : null,
            'program_id' => !empty($program_id) ? (int)$program_id : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $db->table('courses')->where('id', (int)$courseId)->update($updateData);
            
            if ($result) {
                log_message('info', "Course updated successfully. ID: {$courseId}, Title: {$title}");
                
                // ✅ Create notification for admin
                try {
                    $adminId = $session->get('user_id');
                    if ($adminId) {
                        $notificationModel = new NotificationModel();
                        $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully updated course '{$title}'."
                        );
                    }
                } catch (\Exception $notifError) {
                    log_message('warning', 'Notification creation failed: ' . $notifError->getMessage());
                }
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Course updated successfully!',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to update course. Please try again.'
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Course update failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update course: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    // ✅ View All Quizzes (Admin)
    public function quizzes()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $quizModel = new \App\Models\QuizModel();
        $submissionModel = new \App\Models\SubmissionModel();
        $db = \Config\Database::connect();

        // Get all quizzes with course info
        $quizzes = [];
        if ($db->query("SHOW TABLES LIKE 'quizzes'")->getNumRows() > 0) {
            try {
                $result = $db->table('quizzes')
                    ->select('quizzes.*, courses.title as course_title, courses.course_number')
                    ->join('courses', 'courses.id = quizzes.course_id', 'left')
                    ->orderBy('quizzes.created_at', 'DESC')
                    ->get();
                
                if ($result !== false && is_object($result)) {
                    $quizzes = $result->getResultArray();
                    foreach ($quizzes as &$quiz) {
                        $quiz['submission_count'] = $submissionModel->where('quiz_id', $quiz['id'])->countAllResults();
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to fetch quizzes: ' . $e->getMessage());
            }
        }

        $data = [
            'title' => 'All Quizzes',
            'quizzes' => $quizzes,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/quizzes', $data);
    }

    // ✅ Update Course Number (AJAX)
    public function updateCourseNumber()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'POST') {
            $courseId = $this->request->getPost('course_id');
            $courseNumber = trim($this->request->getPost('course_number') ?? '');
            
            if (empty($courseId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Course ID is required'
                ]);
            }

            try {
                $db = \Config\Database::connect();
                
                // Check if course exists
                $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
                if (!$course) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Course not found'
                    ]);
                }
                
                // Update course number (can be empty to clear it)
                $updateData = ['course_number' => $courseNumber ?: null];
                
                // Check if course_number column exists
                $hasCourseNumber = $db->query("SHOW COLUMNS FROM courses WHERE Field = 'course_number'")->getNumRows() > 0;
                if (!$hasCourseNumber) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Course number field does not exist in database'
                    ]);
                }
                
                $db->table('courses')
                   ->where('id', $courseId)
                   ->update($updateData);
                
                $message = $courseNumber 
                    ? "Course number updated to '{$courseNumber}' successfully!" 
                    : "Course number cleared successfully!";
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => $message,
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Failed to update course number: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error updating course number: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Update User Role (AJAX)
    public function updateUserRole()
    {
        $session = session();

        // Ensure only admin can update roles
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
            $userId = $this->request->getPost('id');
            $newRole = $this->request->getPost('role');

            // Validate input
            if (empty($userId) || empty($newRole)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Missing required parameters'
                ]);
            }

            // Validate role - prevent changing to or from admin
            if (!in_array($newRole, ['teacher', 'student'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid role. Admin role cannot be changed.'
                ]);
            }

            $userModel = new UserModel();
            $user = $userModel->find($userId);

            // Check if user exists
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }

            // Protect admin users - prevent changing admin role
            if (strtolower($user['role']) === 'admin') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Admin role is protected and cannot be changed.'
                ]);
            }

            // Prevent changing to admin role
            if (strtolower($newRole) === 'admin') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cannot change user role to admin.'
                ]);
            }

            // Update the role
            try {
                $userModel->update($userId, ['role' => strtolower($newRole)]);
                
                // Get fresh CSRF token for next request
                $security = service('security');
                $newToken = $security->getHash();
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Role updated successfully!',
                    'new_role' => strtolower($newRole),
                    'csrf_token' => $newToken
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to update role: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
    }

    // ✅ Delete User (Soft Delete - Only hides from admin view, doesn't delete from database)
    public function deleteUser($userId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        // Prevent deleting yourself
        if ($userId == $session->get('user_id')) {
            return redirect()->to('/admin/users')->with('error', '⚠️ You cannot delete your own account!');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', '⚠️ User not found.');
        }

        // Prevent deleting admin users - Admin role is protected and cannot be deleted
        if (strtolower($user['role']) === 'admin') {
            return redirect()->to('/admin/users')->with('error', '⚠️ Admin users are protected and cannot be deleted. Only student and teacher users can be deleted.');
        }

        // Soft delete: Set status to 'deleted' instead of actually deleting from database
        // This hides the user from the admin manage users view but keeps them in the database
        // Only applies to student and teacher users
        $userModel->update($userId, ['status' => 'deleted']);

        return redirect()->to('/admin/users')->with('success', '✅ User removed from admin view successfully! (User data preserved in database)');
    }

    // ✅ Restore User (Restore deleted user back to active)
    public function restoreUser($userId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', '⚠️ User not found.');
        }

        // Restore user: Set status back to 'active' from 'deleted'
        // This allows the user to access the system again
        if (($user['status'] ?? '') === 'deleted') {
            $userModel->update($userId, ['status' => 'active']);
            return redirect()->to('/admin/users')->with('success', '✅ User restored successfully! The user can now access the system again.');
        } else {
            return redirect()->to('/admin/users')->with('error', '⚠️ User is not deleted, so it cannot be restored.');
        }
    }

    // ✅ Update User (Edit user details: name, email, role)
    public function updateUser($userId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Access Denied.'
                ])->setStatusCode(403);
            }
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        // Validate "throughly application" token (optional - if provided, validate it)
        // Load helper safely and only validate if function exists
        try {
            helper('security');
            if (function_exists('validate_throughly_token')) {
                $throughlyToken = $this->request->getPost('throughly_token');
                if (!empty($throughlyToken)) {
                    // Only validate if token is provided
                    if (!validate_throughly_token($throughlyToken)) {
                        // If validation fails, log for debugging but don't block the request
                        // This allows the system to work even if token validation has issues
                        log_message('warning', 'Throughly token validation failed for user update: ' . $userId);
                        // Continue with the request - token validation is a security enhancement, not a blocker
                    }
                }
            }
        } catch (\Exception $e) {
            // Helper not found or error loading - continue without token validation
            log_message('debug', 'Security helper not available: ' . $e->getMessage());
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => '⚠️ User not found.'
                ])->setStatusCode(404);
            }
            return redirect()->to('/admin/users')->with('error', '⚠️ User not found.');
        }

        // Get form data
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $department_id = $this->request->getPost('department_id') ?: null;
        $program_id = $this->request->getPost('program_id') ?: null;
        $student_id = $this->request->getPost('student_id') ?: null;
        // Role changes are not allowed via Edit modal

        // Sanitize name - only trim whitespace
        $name = trim($name);
        
        // Reject names with invalid characters - only allow proper name characters
        // Allow: letters (a-z, A-Z, including accented), numbers (0-9), spaces, hyphens (-), apostrophes ('), periods (.), commas (,)
        // Reject: brackets [], semicolons ;, and other special characters that aren't part of proper names
        if (strlen($name) > 0) {
            // Check for invalid characters that aren't part of proper names
            // Pattern: allows letters, numbers, spaces, hyphens, apostrophes, periods, commas
            // Rejects: brackets, semicolons, equals, plus signs, slashes, and other special chars
            if (!preg_match('/^[\p{L}\p{N}\s\-\'\.\,]+$/u', $name)) {
                // Log for debugging
                log_message('info', 'Name rejected due to invalid characters: ' . $name);
                if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid characters detected in name. Only letters, numbers, spaces, hyphens, apostrophes, periods, and commas are allowed.'
                    ])->setStatusCode(400);
                }
                return redirect()->to('/admin/users')->with('error', 'Invalid characters detected in name. Only letters, numbers, spaces, hyphens, apostrophes, periods, and commas are allowed.');
            }
            
            // Also check for specific "throughly application" security patterns
            $throughlyPatterns = [
                'ˈthȯr-',
                'ˈthə-(ˌ)rō',
                'θʌrəθɜːroʊ',
                '=+[\';/.,.\'',
                '[][];;;;[[',
                '[[',
                ']]',
                ';;'
            ];
            
            $nameNormalized = mb_strtolower($name, 'UTF-8');
            foreach ($throughlyPatterns as $pattern) {
                $patternNormalized = mb_strtolower($pattern, 'UTF-8');
                if (mb_strpos($nameNormalized, $patternNormalized) !== false) {
                    log_message('info', 'Name rejected due to throughly pattern: ' . $name . ' (pattern: ' . $pattern . ')');
                    if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid characters detected in name. Security characters are not allowed.'
                        ])->setStatusCode(400);
                    }
                    return redirect()->to('/admin/users')->with('error', 'Invalid characters detected in name. Security characters are not allowed.');
                }
            }
        }
        
        // Name passed validation - accept it (log for debugging only)
        log_message('debug', 'Name validation passed: ' . $name);

        // Validation - VERY lenient, only basic checks (no pattern restrictions)
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => [
                'label' => 'Name',
                'rules' => 'required|min_length[1]|max_length[255]',
                'errors' => [
                    'required' => 'Name is required.',
                    'min_length' => 'Name must be at least 1 character.',
                    'max_length' => 'Name cannot exceed 255 characters.'
                ]
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|max_length[255]',
                'errors' => [
                    'required' => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                    'max_length' => 'Email cannot exceed 255 characters.'
                ]
            ],
            // Role not editable in Edit modal
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            $errorMessage = '⚠️ Validation failed: ' . implode(' ', $errors);
            // Log validation errors for debugging
            log_message('debug', 'Validation failed for user update: ' . json_encode($errors) . ' | Name: ' . $name);
            if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMessage,
                    'errors' => $errors
                ])->setStatusCode(400);
            }
            return redirect()->to('/admin/users')->with('error', $errorMessage);
        }

        // Check if email already exists for another user
        $existingUser = $userModel->where('email', $email)->where('id !=', $userId)->first();
        if ($existingUser) {
            $errorMessage = '⚠️ Email already exists for another user.';
            if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMessage
                ])->setStatusCode(400);
            }
            return redirect()->to('/admin/users')->with('error', $errorMessage);
        }

        // Role changes are not processed via this endpoint

        // ✅ Validate department/program for students
        if (strtolower($user['role']) === 'student') {
            if ($program_id && $department_id) {
                // Verify program belongs to department
                $progModel = new \App\Models\ProgramModel();
                $program = $progModel->find($program_id);
                if (!$program || $program['department_id'] != $department_id) {
                    $errorMessage = 'Selected program does not belong to the selected department.';
                    if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => $errorMessage
                        ])->setStatusCode(400);
                    }
                    return redirect()->to('/admin/users')->with('error', $errorMessage);
                }
            }
        } else {
            // Clear department/program for non-students
            $department_id = null;
            $program_id = null;
            $student_id = null;
        }

        // Update user
        $updateData = [
            'name' => $name,
            'email' => $email,
            'department_id' => $department_id ? (int)$department_id : null,
            'program_id' => $program_id ? (int)$program_id : null,
            'student_id' => $student_id ?: null
        ];

        // Do not update role here

        try {
            // Skip model validation for updates (we've already validated in controller)
            $userModel->skipValidation(true);
            $result = $userModel->update($userId, $updateData);
            $userModel->skipValidation(false);
            
            if (!$result) {
                $errorMessage = '⚠️ Failed to update user. ' . implode(', ', $userModel->errors());
                if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $errorMessage
                    ])->setStatusCode(500);
                }
                return redirect()->to('/admin/users')->with('error', $errorMessage);
            }
            
            // Generate new CSRF token
            $security = service('security');
            $newToken = $security->getHash();
            
            $successMessage = '✅ User updated successfully!';
            
            if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => $successMessage,
                    'csrf_token' => $newToken
                ]);
            }
            
            return redirect()->to('/admin/users')->with('success', $successMessage);
        } catch (\Exception $e) {
            $errorMessage = '⚠️ Failed to update user: ' . $e->getMessage();
            if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMessage
                ])->setStatusCode(500);
            }
            return redirect()->to('/admin/users')->with('error', $errorMessage);
        }
    }

    // ✅ View All Announcements
    public function announcements()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $announcementModel = new AnnouncementModel();
        $announcements = $announcementModel
            ->select('announcements.*, users.name as author_name')
            ->join('users', 'announcements.created_by = users.id', 'left')
            ->orderBy('announcements.created_at', 'DESC')
            ->findAll();

        $data = [
            'title'         => 'Manage Announcements',
            'announcements' => $announcements,
            'user_name'     => $session->get('user_name'),
            'user_role'     => $session->get('user_role')
        ];

        return view('admin/manage_announcements', $data);
    }

    // ✅ Settings Page (Password Change)
    public function settings()
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $data = [
            'title'     => 'Settings',
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/settings', $data);
    }

    // ✅ User Management Page (Create Users - Admin Only)
    public function userManagement()
    {
        helper('security'); // Load security helper for throughly token
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        // Fetch departments and programs for student assignment
        $departments = [];
        $programs = [];
        try {
            $deptModel = new \App\Models\DepartmentModel();
            $departments = $deptModel->getActiveDepartments();
            
            $progModel = new \App\Models\ProgramModel();
            $programs = $progModel->getActivePrograms();
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch departments/programs: ' . $e->getMessage());
        }

        $data = [
            'title'     => 'User Management',
            'departments' => $departments,
            'programs'   => $programs,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/user_management', $data);
    }

    // ✅ Create User (Admin only)
    public function createUser()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        // Validate "throughly application" token (optional - if provided, validate it)
        // Load helper safely and only validate if function exists
        try {
            helper('security');
            if (function_exists('validate_throughly_token')) {
                $throughlyToken = $this->request->getPost('throughly_token');
                if (!empty($throughlyToken)) {
                    // Only validate if token is provided
                    if (!validate_throughly_token($throughlyToken)) {
                        // If validation fails, log for debugging but don't block the request
                        log_message('warning', 'Throughly token validation failed for user creation');
                        // Continue with the request - token validation is a security enhancement, not a blocker
                    }
                }
            }
        } catch (\Exception $e) {
            // Helper not found or error loading - continue without token validation
            log_message('debug', 'Security helper not available: ' . $e->getMessage());
        }

        if ($this->request->getMethod() === 'POST') {
            $userModel = new UserModel();

            $name = trim($this->request->getPost('name'));
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $role = $this->request->getPost('role');
            $status = $this->request->getPost('status') ?? 'active';
            $department_id = $this->request->getPost('department_id') ?: null;
            $program_id = $this->request->getPost('program_id') ?: null;
            $student_id = $this->request->getPost('student_id') ?: null;

            // Validation
            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'All fields are required'
                ]);
            }

            // Sanitize name - only trim whitespace
            $name = trim($name);
            
            // Reject names with invalid characters - only allow proper name characters
            // Allow: letters (a-z, A-Z, including accented), numbers (0-9), spaces, hyphens (-), apostrophes ('), periods (.), commas (,)
            // Reject: brackets [], semicolons ;, and other special characters that aren't part of proper names
            if (strlen($name) > 0) {
                // Check for invalid characters that aren't part of proper names
                // Pattern: allows letters, numbers, spaces, hyphens, apostrophes, periods, commas
                // Rejects: brackets, semicolons, equals, plus signs, slashes, and other special chars
                if (!preg_match('/^[\p{L}\p{N}\s\-\'\.\,]+$/u', $name)) {
                    // Log for debugging
                    log_message('info', 'Name rejected due to invalid characters: ' . $name);
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid characters detected in name. Only letters, numbers, spaces, hyphens, apostrophes, periods, and commas are allowed.'
                    ])->setStatusCode(400);
                }
                
                // Also check for specific "throughly application" security patterns
                $throughlyPatterns = [
                    'ˈthȯr-',
                    'ˈthə-(ˌ)rō',
                    'θʌrəθɜːroʊ',
                    '=+[\';/.,.\'',
                    '[][];;;;[[',
                    '[[',
                    ']]',
                    ';;'
                ];
                
                $nameNormalized = mb_strtolower($name, 'UTF-8');
                foreach ($throughlyPatterns as $pattern) {
                    $patternNormalized = mb_strtolower($pattern, 'UTF-8');
                    if (mb_strpos($nameNormalized, $patternNormalized) !== false) {
                        log_message('info', 'Name rejected due to throughly pattern: ' . $name . ' (pattern: ' . $pattern . ')');
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Invalid characters detected in name. Security characters are not allowed.'
                        ])->setStatusCode(400);
                    }
                }
            }
            
            // Name passed validation - accept it (log for debugging only)
            log_message('debug', 'Name validation passed: ' . $name);

            // Validate role
            if (!in_array(strtolower($role), ['admin', 'teacher', 'student'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid role'
                ]);
            }

            // Check if email already exists
            $existingUser = $userModel->findUserByEmail($email);
            if ($existingUser) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Email already exists'
                ]);
            }

            // Validate password length
            if (strlen($password) < 6) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Password must be at least 6 characters'
                ]);
            }

            // ✅ Validate department/program for students
            if (strtolower($role) === 'student') {
                if ($program_id && $department_id) {
                    // Verify program belongs to department
                    $progModel = new \App\Models\ProgramModel();
                    $program = $progModel->find($program_id);
                    if (!$program || $program['department_id'] != $department_id) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Selected program does not belong to the selected department.'
                        ]);
                    }
                }
            } else {
                // Clear department/program for non-students
                $department_id = null;
                $program_id = null;
                $student_id = null;
            }

            // Create user
            try {
                // Don't hash password here - UserModel::createAccount() will hash it
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password, // Plain password - will be hashed in UserModel
                    'role' => strtolower($role),
                    'status' => $status,
                    'department_id' => $department_id ? (int)$department_id : null,
                    'program_id' => $program_id ? (int)$program_id : null,
                    'student_id' => $student_id ?: null
                ];

                $userId = $userModel->createAccount($userData);

                if ($userId) {
                    // ✅ Create notification for the newly created user
                    try {
                        $notificationModel = new NotificationModel();
                        $notificationModel->createNotification(
                            $userId,
                            "Welcome! Your account has been created. You can now log in to the system."
                        );
                    } catch (\Exception $notifError) {
                        log_message('warning', 'Notification creation failed: ' . $notifError->getMessage());
                    }
                    
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'User created successfully!',
                        'csrf_token' => csrf_token(),
                        'csrf_hash' => csrf_hash()
                    ]);
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to create user: ' . implode(', ', $userModel->errors())
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error creating user: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Change Password (All users)
    public function changePassword()
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please login first'
            ])->setStatusCode(401);
        }

        if ($this->request->getMethod() === 'POST') {
            $userModel = new UserModel();
            $userId = $session->get('user_id');

            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');
            $confirmPassword = $this->request->getPost('confirm_password');

            // Validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'All fields are required'
                ]);
            }

            // Get current user
            $user = $userModel->find($userId);
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Current password is incorrect'
                ]);
            }

            // Check if new password matches confirmation
            if ($newPassword !== $confirmPassword) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'New password and confirmation do not match'
                ]);
            }

            // Validate password length
            if (strlen($newPassword) < 6) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Password must be at least 6 characters'
                ]);
            }

            // Check if new password is same as current
            if (password_verify($newPassword, $user['password'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'New password must be different from current password'
                ]);
            }

            // Update password
            try {
                $userModel->update($userId, [
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT)
                ]);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Password changed successfully!',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error changing password: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Enrollment Management (Admin)
    public function enrollments()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $enrollmentModel = new EnrollmentModel();
        $userModel = new UserModel();
        
        // Get all enrollments with course and user info
        $enrollments = [];
        if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
            try {
                // Build select with all necessary fields - NO ROLE FILTERING
                $selectFields = 'enrollments.id, enrollments.user_id, enrollments.course_id, 
                                enrollments.enrolled_at, enrollments.enrollment_date,
                                enrollments.completion_status, enrollments.final_grade,
                                users.name as user_name, users.email, users.role,
                                courses.title as course_title';
                
                // Add course_number field (check if it exists)
                try {
                    $hasCourseNumber = $db->query("SHOW COLUMNS FROM courses WHERE Field = 'course_number'")->getNumRows() > 0;
                    if ($hasCourseNumber) {
                        $selectFields .= ', courses.course_number';
                    } else {
                        $selectFields .= ', courses.code as course_number';
                    }
                } catch (\Exception $e) {
                    $selectFields .= ', NULL as course_number';
                }
                
                $result = $db->table('enrollments')
                    ->select($selectFields)
                    ->join('users', 'users.id = enrollments.user_id', 'inner')
                    ->join('courses', 'courses.id = enrollments.course_id', 'inner')
                    ->orderBy('enrollments.enrolled_at', 'DESC')
                    ->orderBy('enrollments.id', 'DESC')
                    ->get();
                
                if ($result !== false && is_object($result)) {
                    $enrollments = $result->getResultArray();
                    // Ensure all fields exist
                    foreach ($enrollments as &$enrollment) {
                        $enrollment['course_number'] = $enrollment['course_number'] ?? 'N/A';
                        $enrollment['completion_status'] = $enrollment['completion_status'] ?? 'ENROLLED';
                        $enrollment['enrolled_at'] = $enrollment['enrolled_at'] ?? $enrollment['enrollment_date'] ?? 'N/A';
                        // Ensure role is properly set
                        if (!isset($enrollment['role'])) {
                            $enrollment['role'] = 'student'; // Default fallback
                        }
                    }
                } else {
                    // If join fails, try without joins
                    try {
                        $result = $db->table('enrollments')
                            ->orderBy('enrolled_at', 'DESC')
                            ->orderBy('id', 'DESC')
                            ->get();
                        if ($result !== false && is_object($result)) {
                            $enrollments = $result->getResultArray();
                        }
                    } catch (\Exception $e) {
                        $enrollments = [];
                        log_message('error', 'Failed to fetch enrollments: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                // If query fails, set empty array
                $enrollments = [];
                log_message('error', 'Failed to fetch enrollments: ' . $e->getMessage());
            }
        }

        // Get all students and teachers for enrollment
        $students = $userModel->where('role', 'student')->where('status', 'active')->findAll();
        $teachers = $userModel->where('role', 'teacher')->where('status', 'active')->findAll();
        
        // Get all courses
        $courses = [];
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            try {
                $result = $db->table('courses')
                    ->select('courses.*, users.name as instructor_name')
                    ->join('users', 'users.id = courses.instructor_id', 'left')
                    ->get();
                
                if ($result !== false && is_object($result)) {
                    $courses = $result->getResultArray();
                } else {
                    // If join fails, try without join
                    try {
                        $result = $db->table('courses')
                            ->get();
                        if ($result !== false && is_object($result)) {
                            $courses = $result->getResultArray();
                        }
                    } catch (\Exception $e) {
                        $courses = [];
                        log_message('error', 'Failed to fetch courses: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                // If query fails, set empty array
                $courses = [];
                log_message('error', 'Failed to fetch courses: ' . $e->getMessage());
            }
        }

        $data = [
            'title' => 'Enrollment Management',
            'enrollments' => $enrollments,
            'students' => $students,
            'teachers' => $teachers,
            'courses' => $courses,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('admin/enrollments', $data);
    }

    // ✅ Enroll User (Admin - can enroll students and teachers)
    public function enrollUser()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'POST') {
            $enrollmentModel = new EnrollmentModel();
            
            $userId = $this->request->getPost('user_id');
            $courseId = $this->request->getPost('course_id');

            if (empty($userId) || empty($courseId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User and Course are required'
                ]);
            }

            // Check if already enrolled
            if ($enrollmentModel->isAlreadyEnrolled($userId, $courseId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User is already enrolled in this course'
                ]);
            }

            // Get user and course info for validation
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            $db = \Config\Database::connect();
            $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
            
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }
            
            if (!$course) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Course not found'
                ]);
            }

            // ✅ Validate department/program match for students
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
                        $errors[] = "Student belongs to '{$userDeptName}' but course belongs to '{$courseDeptName}'.";
                    }
                    
                    // Check program match (if both course and student have programs)
                    if ($courseProgId && $userProgId && $userProgId != $courseProgId) {
                        $progModel = new \App\Models\ProgramModel();
                        $userProg = $progModel->find($userProgId);
                        $courseProg = $progModel->find($courseProgId);
                        $userProgName = $userProg ? $userProg['program_name'] : 'Unknown';
                        $courseProgName = $courseProg ? $courseProg['program_name'] : 'Unknown';
                        $errors[] = "Student is in '{$userProgName}' program but course is for '{$courseProgName}' program.";
                    }
                    
                    // If student doesn't have department/program set, but course requires it
                    if ($courseDeptId && !$userDeptId) {
                        $errors[] = "Student must have a department assigned. Please update student's department first.";
                    }
                    
                    if ($courseProgId && !$userProgId) {
                        $errors[] = "Student must have a program assigned. Please update student's program first.";
                    }
                    
                    if (!empty($errors)) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Enrollment failed: ' . implode(' ', $errors)
                        ]);
                    }
                }
            }

            // Enroll user
            try {
                $userId = (int)$userId;
                $courseId = (int)$courseId;
                
                log_message('info', "Admin::enrollUser: Starting enrollment process for user_id: {$userId}, course_id: {$courseId}");
                
                $enrollmentData = [
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'enrolled_at' => date('Y-m-d H:i:s'),
                    'enrollment_date' => date('Y-m-d H:i:s'),
                    'completion_status' => 'ENROLLED',
                ];
                
                // Skip validation temporarily to avoid issues
                $enrollmentModel->skipValidation(true);
                
                // Use insert directly to avoid timestamp issues
                $enrollmentId = $enrollmentModel->insert($enrollmentData);
                
                // Re-enable validation
                $enrollmentModel->skipValidation(false);
                
                if (!$enrollmentId) {
                    $errors = $enrollmentModel->errors();
                    $errorMsg = 'Failed to create enrollment: ' . (empty($errors) ? 'Unknown error' : implode(', ', $errors));
                    log_message('error', 'Enrollment failed: ' . $errorMsg);
                    throw new \Exception($errorMsg);
                }
                
                log_message('info', "Admin::enrollUser: Enrollment successful. Enrollment ID: {$enrollmentId}");

                // Get user info for success message (already fetched above)
                $userName = $user ? $user['name'] : 'User';
                $userRole = $user ? ucfirst($user['role']) : 'User';
                
                log_message('info', "Admin::enrollUser: Enrolled user info - Name: {$userName}, Role: {$userRole}, User ID: {$userId}");
                
                // Get course info (already fetched above)
                $courseTitle = $course ? $course['title'] : 'Course';
                
                log_message('info', "Admin::enrollUser: Course info - Title: {$courseTitle}, Course ID: {$courseId}");

                // ✅ Create notification for the enrolled user
                log_message('info', "Admin::enrollUser: Attempting to create notification for user_id: {$userId}");
                try {
                    $notificationModel = new NotificationModel();
                    $notificationMessage = "You have been successfully enrolled in {$courseTitle}!";
                    log_message('info', "Admin::enrollUser: Notification message: {$notificationMessage}");
                    
                    $notificationId = $notificationModel->createNotification(
                        $userId,
                        $notificationMessage
                    );
                    
                    if ($notificationId) {
                        log_message('info', "Admin::enrollUser: ✅ Notification created successfully! User ID: {$userId}, Notification ID: {$notificationId}, Message: {$notificationMessage}");
                    } else {
                        log_message('error', "Admin::enrollUser: ❌ Notification creation returned FALSE for user_id: {$userId}");
                    }
                } catch (\Exception $notifError) {
                    // Don't fail enrollment if notification fails
                    log_message('error', 'Admin::enrollUser: ❌ Notification creation EXCEPTION for user ' . $userId . ': ' . $notifError->getMessage());
                    log_message('error', 'Admin::enrollUser: Notification error trace: ' . $notifError->getTraceAsString());
                }
                
                // ✅ Also notify the admin that they successfully enrolled a user
                try {
                    $adminId = $session->get('user_id');
                    if ($adminId && $adminId != $userId) {
                        $notificationModel = new NotificationModel();
                        $adminNotificationId = $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully enrolled {$userRole} '{$userName}' in '{$courseTitle}'."
                        );
                        if ($adminNotificationId) {
                            log_message('info', "Admin::enrollUser: ✅ Admin notification created! Admin ID: {$adminId}, Notification ID: {$adminNotificationId}");
                        }
                    }
                } catch (\Exception $adminNotifError) {
                    log_message('warning', 'Admin::enrollUser: Admin notification creation failed: ' . $adminNotifError->getMessage());
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "{$userRole} '{$userName}' enrolled in '{$courseTitle}' successfully!",
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error enrolling user: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Assign Teacher to Course (Admin)
    public function assignTeacher()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'POST') {
            $db = \Config\Database::connect();
            
            $courseId = $this->request->getPost('course_id');
            $teacherId = $this->request->getPost('teacher_id');

            if (empty($courseId) || empty($teacherId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Course and Teacher are required'
                ]);
            }

            // Verify teacher role
            $userModel = new UserModel();
            $teacher = $userModel->find($teacherId);
            if (!$teacher || strtolower($teacher['role']) !== 'teacher') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Selected user is not a teacher'
                ]);
            }

            // Update course instructor and create enrollment
            try {
                $enrollmentModel = new EnrollmentModel();
                
                // Check if teacher is already enrolled
                if ($enrollmentModel->isAlreadyEnrolled($teacherId, $courseId)) {
                    // If already enrolled, just update the instructor_id
                    $db->table('courses')
                       ->where('id', $courseId)
                       ->update(['instructor_id' => $teacherId]);
                    
                    // Get teacher and course info for message
                    $teacherName = $teacher['name'];
                    $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
                    $courseTitle = $course ? $course['title'] : 'Course';
                    
                    // ✅ Create notification for the assigned teacher (even if already enrolled)
                    try {
                        $notificationModel = new NotificationModel();
                        $notificationId = $notificationModel->createNotification(
                            (int)$teacherId,
                            "You have been assigned as instructor for '{$courseTitle}'!"
                        );
                        if ($notificationId) {
                            log_message('info', "Notification created successfully for teacher {$teacherId}: Notification ID {$notificationId}");
                        } else {
                            log_message('warning', "Notification creation returned false for teacher {$teacherId}");
                        }
                    } catch (\Exception $notifError) {
                        log_message('error', 'Notification creation failed for teacher ' . $teacherId . ': ' . $notifError->getMessage());
                    }
                    
                    // ✅ Also notify the admin that they successfully assigned a teacher
                    try {
                        $adminId = $session->get('user_id');
                        if ($adminId && $adminId != $teacherId) {
                            $notificationModel = new NotificationModel();
                            $adminNotificationId = $notificationModel->createNotification(
                                (int)$adminId,
                                "You have successfully assigned '{$teacherName}' as instructor for '{$courseTitle}'."
                            );
                            if ($adminNotificationId) {
                                log_message('info', "Admin::assignTeacher: ✅ Admin notification created! Admin ID: {$adminId}, Notification ID: {$adminNotificationId}");
                            }
                        }
                    } catch (\Exception $adminNotifError) {
                        log_message('warning', 'Admin::assignTeacher: Admin notification creation failed: ' . $adminNotifError->getMessage());
                    }
                    
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => "Teacher '{$teacherName}' is already enrolled and assigned to '{$courseTitle}'!",
                        'csrf_token' => csrf_token(),
                        'csrf_hash' => csrf_hash()
                    ]);
                }
                
                // Update course instructor_id
                $db->table('courses')
                   ->where('id', $courseId)
                   ->update(['instructor_id' => $teacherId]);
                
                // Create enrollment record for the teacher
                $enrollmentData = [
                    'user_id' => (int)$teacherId,
                    'course_id' => (int)$courseId,
                    'enrolled_at' => date('Y-m-d H:i:s'),
                    'enrollment_date' => date('Y-m-d H:i:s'),
                    'completion_status' => 'ENROLLED',
                ];
                
                // Skip validation temporarily to avoid issues
                $enrollmentModel->skipValidation(true);
                $enrollmentId = $enrollmentModel->insert($enrollmentData);
                $enrollmentModel->skipValidation(false);
                
                if (!$enrollmentId) {
                    $errors = $enrollmentModel->errors();
                    log_message('error', 'Failed to create enrollment for teacher: ' . implode(', ', $errors));
                    // Still return success for instructor assignment, but note enrollment issue
                    $teacherName = $teacher['name'];
                    $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
                    $courseTitle = $course ? $course['title'] : 'Course';
                    
                    return $this->response->setJSON([
                        'status' => 'warning',
                        'message' => "Teacher '{$teacherName}' assigned to '{$courseTitle}', but enrollment record creation had issues.",
                        'csrf_token' => csrf_token(),
                        'csrf_hash' => csrf_hash()
                    ]);
                }
                
                // Get teacher and course info for success message
                $teacherName = $teacher['name'];
                $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
                $courseTitle = $course ? $course['title'] : 'Course';

                // ✅ Create notification for the assigned teacher
                try {
                    $notificationModel = new NotificationModel();
                    $notificationId = $notificationModel->createNotification(
                        (int)$teacherId,
                        "You have been assigned as instructor for '{$courseTitle}'!"
                    );
                    if ($notificationId) {
                        log_message('info', "Notification created successfully for teacher {$teacherId}: Notification ID {$notificationId}");
                    } else {
                        log_message('warning', "Notification creation returned false for teacher {$teacherId}");
                    }
                } catch (\Exception $notifError) {
                    // Don't fail assignment if notification fails
                    log_message('error', 'Notification creation failed for teacher ' . $teacherId . ': ' . $notifError->getMessage());
                    log_message('error', 'Notification error trace: ' . $notifError->getTraceAsString());
                }
                
                // ✅ Also notify the admin that they successfully assigned a teacher
                try {
                    $adminId = $session->get('user_id');
                    if ($adminId && $adminId != $teacherId) {
                        $notificationModel = new NotificationModel();
                        $adminNotificationId = $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully assigned '{$teacherName}' as instructor for '{$courseTitle}'."
                        );
                        if ($adminNotificationId) {
                            log_message('info', "Admin::assignTeacher: ✅ Admin notification created! Admin ID: {$adminId}, Notification ID: {$adminNotificationId}");
                        }
                    }
                } catch (\Exception $adminNotifError) {
                    log_message('warning', 'Admin::assignTeacher: Admin notification creation failed: ' . $adminNotifError->getMessage());
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Teacher '{$teacherName}' assigned to '{$courseTitle}' and enrolled successfully!",
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Error assigning teacher: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error assigning teacher: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Unenroll User (Admin) - AJAX
    public function unenrollUser()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'POST') {
            $enrollmentId = $this->request->getPost('enrollment_id');
            
            if (empty($enrollmentId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Enrollment ID is required'
                ]);
            }

            try {
                $enrollmentModel = new EnrollmentModel();
                
                // Get enrollment info before deleting for success message
                $enrollment = $enrollmentModel->find($enrollmentId);
                if (!$enrollment) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Enrollment not found'
                    ]);
                }
                
                // Get user and course info
                $userModel = new UserModel();
                $user = $userModel->find($enrollment['user_id']);
                $userName = $user ? $user['name'] : 'User';
                
                $db = \Config\Database::connect();
                $course = $db->table('courses')->where('id', $enrollment['course_id'])->get()->getRowArray();
                $courseTitle = $course ? $course['title'] : 'Course';
                
                if ($enrollmentModel->delete($enrollmentId)) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => "User '{$userName}' unenrolled from '{$courseTitle}' successfully!",
                        'csrf_token' => csrf_token(),
                        'csrf_hash' => csrf_hash()
                    ]);
                } else {
                    $errors = $enrollmentModel->errors();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to unenroll user: ' . (empty($errors) ? 'Unknown error' : implode(', ', $errors))
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Unenroll failed: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error unenrolling user: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Schedule Management - List All Schedules
    public function schedules()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        // Handle AJAX request for single schedule (for edit)
        if ($this->request->isAJAX() && $this->request->getGet('schedule_id')) {
            $scheduleId = $this->request->getGet('schedule_id');
            $scheduleModel = new CourseScheduleModel();
            $schedule = $scheduleModel->find($scheduleId);
            
            if ($schedule) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'schedule' => $schedule
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ]);
            }
        }

        $db = \Config\Database::connect();
        $scheduleModel = new CourseScheduleModel();

        // Get all schedules with course and instructor info
        $schedules = [];
        try {
            $result = $db->table('course_schedules')
                ->select('course_schedules.*, courses.title as course_title, courses.course_number, users.name as instructor_name')
                ->join('courses', 'courses.id = course_schedules.course_id', 'left')
                ->join('users', 'users.id = courses.instructor_id', 'left')
                ->orderBy('courses.title', 'ASC')
                ->orderBy('course_schedules.day_of_week', 'ASC')
                ->orderBy('course_schedules.start_time', 'ASC')
                ->get();

            if ($result !== false && is_object($result)) {
                $schedules = $result->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch schedules: ' . $e->getMessage());
        }

        // Get all courses for dropdown
        $courses = [];
        try {
            $result = $db->table('courses')
                ->select('courses.id, courses.title, courses.course_number, users.name as instructor_name')
                ->join('users', 'users.id = courses.instructor_id', 'left')
                ->orderBy('courses.title', 'ASC')
                ->get();

            if ($result !== false && is_object($result)) {
                $courses = $result->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch courses: ' . $e->getMessage());
        }

        $data = [
            'title' => 'Course Schedules',
            'schedules' => $schedules,
            'courses' => $courses,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('admin/schedules', $data);
    }

    // ✅ Create Schedule
    public function createSchedule()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access Denied.'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'course_id' => 'required|integer',
            'class_type' => 'required|in_list[online,face_to_face]',
            'day_of_week' => 'required|max_length[20]',
            'start_time' => 'required',
            'end_time' => 'required',
            'room' => 'permit_empty|max_length[50]',
            'meeting_link' => 'permit_empty|valid_url',
        ]);

        if (!$validation->run($this->request->getPost())) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed: ' . implode(', ', $validation->getErrors())
            ]);
        }

        $scheduleModel = new CourseScheduleModel();
        $data = [
            'course_id' => $this->request->getPost('course_id'),
            'class_type' => $this->request->getPost('class_type'),
            'day_of_week' => $this->request->getPost('day_of_week'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'room' => $this->request->getPost('room') ?: null,
            'meeting_link' => $this->request->getPost('meeting_link') ?: null,
        ];

        if ($scheduleModel->insert($data)) {
            // ✅ Create notification for admin
            try {
                $notificationModel = new NotificationModel();
                $adminId = $session->get('user_id');
                
                // Get course title for notification
                $db = \Config\Database::connect();
                $course = $db->table('courses')->where('id', $data['course_id'])->get()->getRowArray();
                $courseTitle = $course ? $course['title'] : 'Course';
                $classType = ucfirst(str_replace('_', ' ', $data['class_type']));
                
                $notificationModel->createNotification(
                    (int)$adminId,
                    "You have successfully created a {$classType} schedule for '{$courseTitle}'."
                );
            } catch (\Exception $notifError) {
                log_message('warning', 'Notification creation failed for schedule: ' . $notifError->getMessage());
            }
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Schedule created successfully!',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            $errors = $scheduleModel->errors();
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create schedule: ' . (empty($errors) ? 'Unknown error' : implode(', ', $errors))
            ]);
        }
    }

    // ✅ Update Schedule
    public function updateSchedule($scheduleId)
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access Denied.'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
        }

        $scheduleModel = new CourseScheduleModel();
        $schedule = $scheduleModel->find($scheduleId);
        
        if (!$schedule) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Schedule not found.'
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'course_id' => 'required|integer',
            'class_type' => 'required|in_list[online,face_to_face]',
            'day_of_week' => 'required|max_length[20]',
            'start_time' => 'required',
            'end_time' => 'required',
            'room' => 'permit_empty|max_length[50]',
            'meeting_link' => 'permit_empty|valid_url',
        ]);

        if (!$validation->run($this->request->getPost())) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed: ' . implode(', ', $validation->getErrors())
            ]);
        }

        $data = [
            'course_id' => $this->request->getPost('course_id'),
            'class_type' => $this->request->getPost('class_type'),
            'day_of_week' => $this->request->getPost('day_of_week'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'room' => $this->request->getPost('room') ?: null,
            'meeting_link' => $this->request->getPost('meeting_link') ?: null,
        ];

        if ($scheduleModel->update($scheduleId, $data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Schedule updated successfully!',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            $errors = $scheduleModel->errors();
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update schedule: ' . (empty($errors) ? 'Unknown error' : implode(', ', $errors))
            ]);
        }
    }

    // ✅ Delete Schedule
    public function deleteSchedule($scheduleId)
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access Denied.'
            ])->setStatusCode(403);
        }

        $scheduleModel = new CourseScheduleModel();
        $schedule = $scheduleModel->find($scheduleId);
        
        if (!$schedule) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Schedule not found.'
            ]);
        }

        if ($scheduleModel->delete($scheduleId)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Schedule deleted successfully!',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete schedule.'
            ]);
        }
    }
}
