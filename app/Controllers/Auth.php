<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AnnouncementModel;
use App\Models\EnrollmentModel;
use App\Models\MaterialModel;
use App\Models\NotificationModel;
use App\Models\OtpTokenModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ LOGIN with Role-Based Redirection
    public function login()
    {
        $session = session();

        // ✅ If already logged in, redirect based on role
        if ($session->get('logged_in')) {
            $role = strtolower($session->get('user_role'));
            if ($role === 'admin') {
                return redirect()->to('/admin_dashboard');
            } elseif ($role === 'teacher') {
                return redirect()->to('/teacher_dashboard');
            } else {
                return redirect()->to('/dashboard');
            }
        }

        // ✅ Show login form if GET request
        if ($this->request->getMethod() === 'GET') {
            // Check if OTP verification step
            $otpStep = $session->get('otp_step');
            $pendingUserId = $session->get('pending_user_id');
            
            if ($otpStep && $pendingUserId) {
                return view('auth/otp_verify');
            }
            
            return view('auth/login');
        }

        // ✅ Process login POST
        if ($this->request->getMethod() === 'POST') {
            $otpStep = $session->get('otp_step');
            $pendingUserId = $session->get('pending_user_id');

            // Step 2: Verify OTP
            if ($otpStep && $pendingUserId) {
                $otpCode = $this->request->getPost('otp_code');
                
                if (empty($otpCode)) {
                    return redirect()->back()->with('error', 'OTP code is required.');
                }

                $otpModel = new OtpTokenModel();
                if ($otpModel->verifyOtp($pendingUserId, $otpCode)) {
                    // OTP verified, complete login
                    $userModel = new UserModel();
                    $user = $userModel->find($pendingUserId);

                    if (!$user || (isset($user['status']) && $user['status'] !== 'active')) {
                        $session->remove(['otp_step', 'pending_user_id']);
                        return redirect()->to('/login')->with('error', 'Your account is not active. Please contact admin.');
                    }

                    // Set session
                    $session->set([
                        'user_id'   => $user['id'],
                        'user_name' => $user['name'],
                        'user_role' => strtolower($user['role']),
                        'logged_in' => true,
                    ]);

                    // Clear OTP session
                    $session->remove(['otp_step', 'pending_user_id']);

                    // Redirect based on role
                    $role = strtolower($user['role']);
                    if ($role === 'admin') {
                        return redirect()->to('/admin_dashboard')->with('success', 'Login successful!');
                    } elseif ($role === 'teacher') {
                        return redirect()->to('/teacher_dashboard')->with('success', 'Login successful!');
                    } else {
                        return redirect()->to('/dashboard')->with('success', 'Login successful!');
                    }
                } else {
                    return redirect()->back()->with('error', 'Invalid or expired OTP code. Please try again.');
                }
            }

            // Step 1: Verify credentials and send OTP
            // Validation
            if (!$this->validate([
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $email     = $this->request->getPost('email');
            $password  = $this->request->getPost('password');
            $user      = $userModel->findUserByEmail($email);

            if (!$user) {
                return redirect()->back()->with('error', 'Email not found.');
            }

            if (!password_verify($password, $user['password'])) {
                return redirect()->back()->with('error', 'Incorrect password.');
            }

            if (isset($user['status']) && $user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Your account is not active. Please contact admin.');
            }

            // Generate and send OTP
            try {
                $otpModel = new OtpTokenModel();
                $otpCode = $otpModel->generateOtp($user['id'], $user['email']);

                // Development mode: If OTP generation fails, allow direct login (remove in production)
                $devMode = (ENVIRONMENT === 'development');
                
                if ($otpCode) {
                    // For development: Display OTP on screen if email fails
                    // In production, remove this and only use email
                    $emailService = \Config\Services::email();
                    $emailService->setFrom('noreply@lms.local', 'LMS System');
                    $emailService->setTo($user['email']);
                    $emailService->setSubject('Your Login OTP Code');
                    $emailService->setMessage("Your OTP code is: {$otpCode}\n\nThis code will expire in 10 minutes.\n\nIf you did not request this code, please ignore this email.");
                    
                    $emailSent = false;
                    try {
                        $emailSent = $emailService->send();
                    } catch (\Exception $e) {
                        log_message('error', 'Email send error: ' . $e->getMessage());
                    }
                    
                    // Set OTP verification step
                    $session->set([
                        'otp_step'      => true,
                        'pending_user_id' => $user['id'],
                    ]);
                    
                    // Always proceed to OTP verification step
                    // Store OTP in session in case email fails
                    $session->set('display_otp', $otpCode);
                    
                    if ($emailSent) {
                        return redirect()->to('/login')->with('success', 'OTP code has been sent to your email. Please check and enter the code.');
                    } else {
                        // If email fails, show OTP on screen for development/testing
                        log_message('warning', 'Email sending failed. OTP: ' . $otpCode);
                        return redirect()->to('/login')->with('warning', 'Email not configured. Your OTP code is: <strong style="font-size: 1.2em; color: #198754;">' . $otpCode . '</strong> - Please enter this code to continue.');
                    }
                } else {
                    // If OTP generation fails and we're in development mode, allow direct login
                    if ($devMode) {
                        log_message('warning', 'OTP generation failed in development mode. Allowing direct login.');
                        // Set session directly
                        $session->set([
                            'user_id'   => $user['id'],
                            'user_name' => $user['name'],
                            'user_role' => strtolower($user['role']),
                            'logged_in' => true,
                        ]);
                        
                        // Redirect based on role
                        $role = strtolower($user['role']);
                        if ($role === 'admin') {
                            return redirect()->to('/admin_dashboard')->with('warning', 'Logged in (OTP bypassed in development mode)');
                        } elseif ($role === 'teacher') {
                            return redirect()->to('/teacher_dashboard')->with('warning', 'Logged in (OTP bypassed in development mode)');
                        } else {
                            return redirect()->to('/dashboard')->with('warning', 'Logged in (OTP bypassed in development mode)');
                        }
                    }
                    
                    $errors = $otpModel->errors();
                    log_message('error', 'OTP generation failed. Errors: ' . json_encode($errors));
                    $db = \Config\Database::connect();
                    $dbError = $db->error();
                    $errorMsg = !empty($dbError) ? json_encode($dbError) : (empty($errors) ? 'Unknown error' : implode(', ', $errors));
                    return redirect()->back()->with('error', 'Failed to generate OTP. Please try again. Error: ' . $errorMsg);
                }
            } catch (\Exception $e) {
                log_message('error', 'OTP generation exception: ' . $e->getMessage());
                log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                
                // Development mode: Allow direct login if OTP fails
                if (ENVIRONMENT === 'development') {
                    log_message('warning', 'OTP exception in development mode. Allowing direct login.');
                    $session->set([
                        'user_id'   => $user['id'],
                        'user_name' => $user['name'],
                        'user_role' => strtolower($user['role']),
                        'logged_in' => true,
                    ]);
                    
                    $role = strtolower($user['role']);
                    if ($role === 'admin') {
                        return redirect()->to('/admin_dashboard')->with('warning', 'Logged in (OTP exception bypassed in development mode)');
                    } elseif ($role === 'teacher') {
                        return redirect()->to('/teacher_dashboard')->with('warning', 'Logged in (OTP exception bypassed in development mode)');
                    } else {
                        return redirect()->to('/dashboard')->with('warning', 'Logged in (OTP exception bypassed in development mode)');
                    }
                }
                
                return redirect()->back()->with('error', 'Failed to generate OTP. Please try again. Error: ' . $e->getMessage());
            }
        }
    }

    // ✅ REGISTER
    public function register()
    {
        $session = session();

        // If already logged in, redirect to dashboard
        if ($session->get('logged_in')) {
            $role = strtolower($session->get('user_role'));
            if ($role === 'admin') {
                return redirect()->to('/admin_dashboard');
            } elseif ($role === 'teacher') {
                return redirect()->to('/teacher_dashboard');
            } else {
                return redirect()->to('/dashboard');
            }
        }

        if ($this->request->getMethod() === 'GET') {
            return view('auth/register');
        }

        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'name'             => 'required|min_length[3]|max_length[255]',
                'email'            => 'required|valid_email|is_unique[users.email]',
                'password'         => 'required|min_length[6]',
                'confirm_password' => 'required|matches[password]',
                'role'             => 'required|in_list[admin,teacher,student]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userModel->createAccount([
                'name'     => $this->request->getPost('name'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role'     => strtolower($this->request->getPost('role')),
            ]);

            return redirect()->to('/login')->with('success', 'Account created successfully! You can now login.');
        }
    }

    // ✅ LOGOUT
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }

    // ✅ SINGLE DASHBOARD for All Roles
    public function dashboard()
    {
        $session = session();

        // 🔐 Check login
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $db                = \Config\Database::connect();
        $userModel         = new UserModel();
        $announcementModel = new AnnouncementModel();
        $enrollmentModel   = new EnrollmentModel();

        $userId   = $session->get('user_id');
        $user     = $userModel->find($userId);
        
        // Check if role changed in database and auto-redirect
        // Always use database role to ensure it's current
        $dbRole = '';
        if ($user) {
            $dbRole = strtolower($user['role'] ?? '');
            $sessionRole = strtolower($session->get('user_role') ?? '');
            
            if ($dbRole !== $sessionRole) {
                $session->set('user_role', $dbRole);
                
                if ($dbRole === 'admin') {
                    return redirect()->to('/admin_dashboard')->with('success', 'Your role has been updated to Admin.');
                } elseif ($dbRole === 'teacher') {
                    return redirect()->to('/teacher_dashboard')->with('success', 'Your role has been updated to Teacher.');
                } else {
                    return redirect()->to('/dashboard')->with('success', 'Your role has been updated to Student.');
                }
            }
        }
        
        // Always use database role (or session as fallback)
        $userRole = $dbRole ?: strtolower($session->get('user_role') ?? '');

        // ✅ Admin: create announcements & update roles
        if ($this->request->getMethod() === 'POST' && $userRole === 'admin') {

            // AJAX role update
            if ($this->request->isAJAX() && $this->request->getPost('id') && $this->request->getPost('role')) {
                $userModel->update($this->request->getPost('id'), [
                    'role' => $this->request->getPost('role')
                ]);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Role updated successfully!']);
            }

            // Create announcement
            if ($this->request->getPost('title') && $this->request->getPost('content')) {
                $announcementModel->insert([
                    'title'      => $this->request->getPost('title'),
                    'content'    => $this->request->getPost('content'),
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                return redirect()->to('/admin_dashboard')->with('success', '✅ Announcement created successfully!');
            }
        }

        // ✅ Student Enrollment
        if ($this->request->getMethod() === 'POST' && $userRole === 'student') {
            $courseId = $this->request->getPost('course_id');
            $exists = $enrollmentModel->where('user_id', $userId)->where('course_id', $courseId)->first();

            if ($exists) {
                return redirect()->to('/dashboard')->with('error', '⚠️ You are already enrolled in this course.');
            }

            $enrollmentModel->insert([
                'user_id'    => $userId,
                'course_id'  => $courseId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // ✅ Create notification for the student
            $notificationModel = new NotificationModel();
            $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
            $courseName = $course ? $course['title'] : 'a course';
            
            $notificationModel->createNotification(
                $userId,
                "You have been successfully enrolled in {$courseName}"
            );

            return redirect()->to('/dashboard')->with('success', '✅ Successfully enrolled in the course!');
        }

        // --- Load dashboard views by role ---
        if ($userRole === 'admin') {
            $users = $userModel->select('id, name, email, role')->findAll();
            
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
                        // If query failed, get courses without join
                        $result = $db->table('courses')
                            ->orderBy('created_at', 'DESC')
                            ->get();
                        if ($result !== false && is_object($result)) {
                            $courses = $result->getResultArray();
                        }
                    }
                } catch (\Exception $e) {
                    // If join fails, get courses without join
                    try {
                        $result = $db->table('courses')
                            ->orderBy('created_at', 'DESC')
                            ->get();
                        if ($result !== false && is_object($result)) {
                            $courses = $result->getResultArray();
                        }
                    } catch (\Exception $e2) {
                        $courses = [];
                        log_message('error', 'Failed to fetch admin courses: ' . $e2->getMessage());
                    }
                }
            }
            
            $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

            // Get enrollment statistics
            $totalEnrollments = 0;
            if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                $totalEnrollments = $db->table('enrollments')->countAllResults();
            }

            $data = [
                'title'         => 'Admin Dashboard',
                'user'          => $user,
                'users'         => $users,
                'courses'       => $courses,
                'announcements' => $announcements,
                'user_name'     => $session->get('user_name'),
                'user_role'     => $userRole,
                'stats'         => [
                    'total_users'     => $userModel->countAll(),
                    'total_courses'   => count($courses),
                    'active_students' => $userModel->where('role', 'student')->countAllResults(),
                    'active_teachers' => $userModel->where('role', 'teacher')->countAllResults(),
                    'total_enrollments' => $totalEnrollments,
                ]
            ];

            // ✅ FIXED: Correct view path
            return view('/admin_dashboard', $data);
        }


        elseif ($userRole === 'teacher') {
            // Check if courses table exists
            $courses = [];
            if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                // First, try to get courses with joins
                $coursesQuery = $db->table('courses')
                    ->select('courses.*')
                    ->where('courses.instructor_id', $userId)
                    ->orderBy('courses.created_at', 'DESC');
                
                // Try to add joins if tables exist
                try {
                    if ($db->query("SHOW TABLES LIKE 'acad_years'")->getNumRows() > 0) {
                        $coursesQuery->select('acad_years.acad_year as acad_year', false);
                        $coursesQuery->join('acad_years', 'courses.acad_year_id = acad_years.id', 'left');
                    }
                    if ($db->query("SHOW TABLES LIKE 'semesters'")->getNumRows() > 0) {
                        $coursesQuery->select('semesters.semester as semester_name', false);
                        $coursesQuery->join('semesters', 'courses.semester_id = semesters.id', 'left');
                    }
                    if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                        $coursesQuery->select('terms.term as term_name', false);
                        $coursesQuery->join('terms', 'courses.term_id = terms.id', 'left');
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to join academic tables: ' . $e->getMessage());
                    // Ignore join errors, continue with basic query
                }
                
                // Execute query and check result
                try {
                    $result = $coursesQuery->get();
                    if ($result !== false && is_object($result)) {
                        $courses = $result->getResultArray();
                    } else {
                        // Query failed, get courses without joins
                        $result = $db->table('courses')
                            ->where('instructor_id', $userId)
                            ->orderBy('created_at', 'DESC')
                            ->get();
                        if ($result !== false && is_object($result)) {
                            $courses = $result->getResultArray();
                        } else {
                            $courses = [];
                        }
                    }
                } catch (\Exception $e) {
                    // If query fails, get courses without joins
                    try {
                        $result = $db->table('courses')
                            ->where('instructor_id', $userId)
                            ->orderBy('created_at', 'DESC')
                            ->get();
                        if ($result !== false && is_object($result)) {
                            $courses = $result->getResultArray();
                        } else {
                            $courses = [];
                        }
                    } catch (\Exception $e2) {
                        // If even basic query fails, set empty array
                        $courses = [];
                        log_message('error', 'Failed to fetch teacher courses: ' . $e2->getMessage());
                    }
                }
                
                // Get enrollment counts and assignment counts for each course
                foreach ($courses as &$course) {
                    // Student count
                    try {
                        if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                            $course['student_count'] = $db->table('enrollments')
                                ->where('course_id', $course['id'])
                                ->countAllResults();
                        } else {
                            $course['student_count'] = 0;
                        }
                    } catch (\Exception $e) {
                        $course['student_count'] = 0;
                    }
                    
                    // Assignment count
                    try {
                        if ($db->query("SHOW TABLES LIKE 'assignments'")->getNumRows() > 0) {
                            $course['assignment_count'] = $db->table('assignments')
                                ->where('course_id', $course['id'])
                                ->countAllResults();
                        } else {
                            $course['assignment_count'] = 0;
                        }
                    } catch (\Exception $e) {
                        $course['assignment_count'] = 0;
                    }
                    
                    // Ensure all fields exist with proper defaults
                    if (!isset($course['course_number'])) {
                        $course['course_number'] = $course['course_code'] ?? $course['code'] ?? '';
                    }
                    // Set academic structure fields - keep null if empty, view will handle display
                    $course['acad_year'] = !empty($course['acad_year']) ? $course['acad_year'] : null;
                    $course['semester_name'] = !empty($course['semester_name']) ? $course['semester_name'] : null;
                    $course['term_name'] = !empty($course['term_name']) ? $course['term_name'] : null;
                }
            }
            
            $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

            $data = [
                'title'         => 'Teacher Dashboard',
                'user'          => $user,
                'courses'       => $courses,
                'announcements' => $announcements,
                'user_name'     => $session->get('user_name'),
                'user_role'     => $userRole,
                'stats'         => [
                    'my_courses' => count($courses),
                    'total_students' => array_sum(array_column($courses, 'student_count')),
                    'total_assignments' => array_sum(array_column($courses, 'assignment_count'))
                ]
            ];

            // ✅ FIXED: Correct view path
            return view('/teacher_dashboard', $data);
        }


        elseif ($userRole === 'student') {
            // Check if courses table exists
            $courses = [];

            
            if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                // Check if code column exists
                $hasCodeColumn = false;
                try {
                    $hasCodeColumn = $db->query("SHOW COLUMNS FROM courses WHERE Field = 'code'")->getNumRows() > 0;
                } catch (\Exception $e) {
                    // Column check failed, assume it doesn't exist
                    $hasCodeColumn = false;
                }
                
                // Build query with all fields and joins
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
                        $query->select('semesters.name as semester_name', false);
                        $query->join('semesters', 'courses.semester_id = semesters.id', 'left');
                    }
                    if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                        $query->select('terms.name as term_name', false);
                        $query->join('terms', 'courses.term_id = terms.id', 'left');
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to join academic tables for available courses: ' . $e->getMessage());
                }
                
                $query->orderBy('courses.title', 'ASC');
                
                try {
                    $result = $query->get();
                    
                    // Check if query was successful
                    if ($result !== false && is_object($result)) {
                        $courses = $result->getResultArray();
                        // Ensure all fields exist with proper defaults
                        foreach ($courses as &$course) {
                            $course['code'] = $course['course_number'] ?? '';
                            $course['course_number'] = $course['course_number'] ?? '';
                            $course['instructor_name'] = !empty($course['instructor_name']) ? $course['instructor_name'] : 'N/A';
                            $course['acad_year'] = !empty($course['acad_year']) ? $course['acad_year'] : null;
                            $course['semester_name'] = !empty($course['semester_name']) ? $course['semester_name'] : null;
                            $course['term_name'] = !empty($course['term_name']) ? $course['term_name'] : null;
                            $course['description'] = $course['description'] ?? '';
                        }
                    } else {
                        // If join fails, try without join
                        try {
                            $result = $db->table('courses')
                                ->select('courses.id, courses.title, courses.description')
                                ->get();
                            
                            if ($result !== false && is_object($result)) {
                                $courses = $result->getResultArray();
                                foreach ($courses as &$course) {
                                    $course['code'] = '';
                                    $course['instructor_name'] = 'N/A';
                                }
                            } else {
                                $courses = [];
                            }
                        } catch (\Exception $e) {
                            // Query failed completely, courses will remain empty array
                            $courses = [];
                            log_message('error', 'Failed to fetch student courses: ' . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    // If query fails, try without join
                    try {
                        $result = $db->table('courses')
                            ->select('courses.id, courses.title, courses.description')
                            ->get();
                        
                        if ($result !== false && is_object($result)) {
                            $courses = $result->getResultArray();
                            foreach ($courses as &$course) {
                                $course['code'] = '';
                                $course['instructor_name'] = 'N/A';
                            }
                        } else {
                            $courses = [];
                        }
                    } catch (\Exception $e2) {
                        // Query failed completely, courses will remain empty array
                        $courses = [];
                        log_message('error', 'Failed to fetch student courses: ' . $e2->getMessage());
                    }
                }
            }
            
            // Get enrolled courses with academic structure and instructor info
            $enrolledCourses = [];
            if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                try {
                    $enrolledQuery = $db->table('enrollments')
                        ->select('enrollments.*, courses.id as course_id, courses.title, courses.description, courses.course_number, courses.instructor_id, users.name as instructor_name')
                        ->join('courses', 'enrollments.course_id = courses.id', 'left')
                        ->join('users', 'courses.instructor_id = users.id', 'left')
                        ->where('enrollments.user_id', $userId)
                        ->orderBy('courses.title', 'ASC');
                    
                    // Add academic structure joins
                    try {
                        if ($db->query("SHOW TABLES LIKE 'acad_years'")->getNumRows() > 0) {
                            $enrolledQuery->select('acad_years.acad_year as acad_year', false);
                            $enrolledQuery->join('acad_years', 'courses.acad_year_id = acad_years.id', 'left');
                        }
                        if ($db->query("SHOW TABLES LIKE 'semesters'")->getNumRows() > 0) {
                            $enrolledQuery->select('semesters.semester as semester_name', false);
                            $enrolledQuery->join('semesters', 'courses.semester_id = semesters.id', 'left');
                        }
                        if ($db->query("SHOW TABLES LIKE 'terms'")->getNumRows() > 0) {
                            $enrolledQuery->select('terms.term as term_name', false);
                            $enrolledQuery->join('terms', 'courses.term_id = terms.id', 'left');
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to join academic tables for student: ' . $e->getMessage());
                    }
                    
                    $result = $enrolledQuery->get();
                    if ($result !== false && is_object($result)) {
                        $enrolledCourses = $result->getResultArray();
                        
                        // Get assignment and quiz counts for each enrolled course
                        foreach ($enrolledCourses as &$course) {
                            $courseId = $course['course_id'];
                            
                            // Assignment count
                            try {
                                if ($db->query("SHOW TABLES LIKE 'assignments'")->getNumRows() > 0) {
                                    $course['assignment_count'] = $db->table('assignments')
                                        ->where('course_id', $courseId)
                                        ->countAllResults();
                                } else {
                                    $course['assignment_count'] = 0;
                                }
                            } catch (\Exception $e) {
                                $course['assignment_count'] = 0;
                            }
                            
                            // Quiz count
                            try {
                                if ($db->query("SHOW TABLES LIKE 'quizzes'")->getNumRows() > 0) {
                                    $course['quiz_count'] = $db->table('quizzes')
                                        ->where('course_id', $courseId)
                                        ->countAllResults();
                                } else {
                                    $course['quiz_count'] = 0;
                                }
                            } catch (\Exception $e) {
                                $course['quiz_count'] = 0;
                            }
                            
                            // Ensure all fields exist with proper defaults
                            $course['acad_year'] = !empty($course['acad_year']) ? $course['acad_year'] : null;
                            $course['semester_name'] = !empty($course['semester_name']) ? $course['semester_name'] : null;
                            $course['term_name'] = !empty($course['term_name']) ? $course['term_name'] : null;
                            $course['course_number'] = !empty($course['course_number']) ? $course['course_number'] : '';
                            $course['instructor_name'] = !empty($course['instructor_name']) ? $course['instructor_name'] : 'N/A';
                            $course['assignment_count'] = $course['assignment_count'] ?? 0;
                            $course['quiz_count'] = $course['quiz_count'] ?? 0;
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to fetch enrolled courses: ' . $e->getMessage());
                    // Fallback to simple query
                    try {
                        $enrolledCourses = $enrollmentModel->select('courses.id, courses.title, courses.description')
                            ->join('courses', 'enrollments.course_id = courses.id')
                            ->where('enrollments.user_id', $userId)
                            ->findAll();
                    } catch (\Exception $e2) {
                        $enrolledCourses = [];
                    }
                }
            }
            
            // Calculate statistics
            $totalEnrolled = count($enrolledCourses);
            $totalAssignments = !empty($enrolledCourses) ? array_sum(array_column($enrolledCourses, 'assignment_count')) : 0;
            $totalQuizzes = !empty($enrolledCourses) ? array_sum(array_column($enrolledCourses, 'quiz_count')) : 0;
            
            // Load materials per enrolled course (optional, for future use)
            $materialsByCourse = [];
            if (!empty($enrolledCourses) && $db->query("SHOW TABLES LIKE 'materials'")->getNumRows() > 0) {
                try {
                    $materialModel = new \App\Models\MaterialModel();
                    foreach ($enrolledCourses as $ec) {
                        $materialsByCourse[$ec['course_id']] = $materialModel->getMaterialsByCourse($ec['course_id']);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to load materials: ' . $e->getMessage());
                    $materialsByCourse = [];
                }
            }
            
            $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

            $data = [
                'title'           => 'Student Dashboard',
                'user'            => $user,
                'courses'         => $courses,
                'enrolledCourses' => $enrolledCourses,
                'materialsByCourse' => $materialsByCourse,
                'announcements'   => $announcements,
                'user_name'       => $session->get('user_name'),
                'user_role'       => $userRole,
                'stats'           => [
                    'my_courses' => $totalEnrolled,
                    'total_assignments' => $totalAssignments,
                    'total_quizzes' => $totalQuizzes
                ]
            ];

            return view('/dashboard', $data);
        }


        else {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Unknown user role detected.');
        }
    }
}
