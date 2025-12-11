<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;
use App\Models\EnrollmentModel;
use App\Models\AssignmentModel;
use App\Models\GradeModel;
use App\Models\GradingPeriodModel;
use App\Models\GradingWeightModel;
use App\Models\UserModel;
use App\Models\QuizModel;
use App\Models\SubmissionModel;
use App\Models\NotificationModel;

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
            try {
                $result = $db->table('courses')
                              ->where('instructor_id', $userId)
                              ->orderBy('created_at', 'DESC')
                              ->get();
                
                if ($result !== false && is_object($result)) {
                    $courses = $result->getResultArray();
                } else {
                    $courses = [];
                    log_message('warning', 'Failed to fetch courses for teacher ' . $userId . ': get() returned false');
                }
            } catch (\Exception $e) {
                $courses = [];
                log_message('error', 'Error fetching courses: ' . $e->getMessage());
            }

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
        try {
            $courseResult = $db->table('courses')
                             ->where('id', $courseId)
                             ->where('instructor_id', $userId)
                             ->get();
            
            $course = ($courseResult !== false && is_object($courseResult)) ? $courseResult->getRowArray() : null;
        } catch (\Exception $e) {
            log_message('error', 'Error fetching course in viewCourse: ' . $e->getMessage());
            $course = null;
        }

        if (!$course) {
            return redirect()->to('/teacher/courses')->with('error', 'Course not found or access denied.');
        }

        // Get enrolled students
        $enrolledStudents = [];
        if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
            try {
                $result = $db->table('enrollments')
                    ->select('users.id, users.name, users.email, enrollments.created_at as enrolled_at, enrollments.enrollment_date, enrollments.completion_status, enrollments.final_grade, users.student_id')
                    ->join('users', 'enrollments.user_id = users.id', 'left')
                    ->where('enrollments.course_id', $courseId)
                    ->orderBy('enrollments.created_at', 'DESC')
                    ->get();
                
                if ($result !== false && is_object($result)) {
                    $enrolledStudents = $result->getResultArray();
                } else {
                    $enrolledStudents = [];
                    log_message('warning', 'Failed to fetch enrolled students for course ' . $courseId . ': get() returned false');
                }
            } catch (\Exception $e) {
                $enrolledStudents = [];
                log_message('error', 'Error fetching enrolled students: ' . $e->getMessage());
            }
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
        try {
            $result = $db->table('courses')
                         ->where('id', $courseId)
                         ->where('instructor_id', $userId)
                         ->get();
            
            if ($result === false || !is_object($result)) {
                return redirect()->to('/teacher/courses')->with('error', 'Course not found or access denied.');
            }
            
            $course = $result->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'Error fetching course in removeStudent: ' . $e->getMessage());
            return redirect()->to('/teacher/courses')->with('error', 'Error accessing course.');
        }

        if (!$course) {
            return redirect()->to('/teacher/courses')->with('error', 'Course not found or access denied.');
        }

        // Remove enrollment
        try {
            if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                // The studentId parameter could be either enrollment ID or user_id
                // First, try to find by enrollment ID (primary key)
                $enrollment = $db->table('enrollments')
                                 ->where('id', $studentId)
                                 ->where('course_id', $courseId)
                                 ->get()
                                 ->getRowArray();
                
                // If not found by enrollment ID, try by user_id
                if (!$enrollment) {
                    $enrollment = $db->table('enrollments')
                                     ->where('user_id', $studentId)
                                     ->where('course_id', $courseId)
                                     ->get()
                                     ->getRowArray();
                }
                
                if (!$enrollment) {
                    log_message('warning', "Enrollment not found: course_id={$courseId}, studentId={$studentId}");
                    // Check if coming from enroll page or view page
                    $redirectUrl = $this->request->getGet('from') === 'enroll' 
                        ? '/teacher/courses/' . $courseId . '/enroll-students' 
                        : '/teacher/courses/view/' . $courseId;
                    return redirect()->to($redirectUrl)->with('error', 'Enrollment not found. Student may already be removed.');
                }
                
                // Delete the enrollment using the enrollment ID (most reliable)
                $enrollmentId = $enrollment['id'];
                $actualUserId = $enrollment['user_id'];
                $deleted = $db->table('enrollments')
                               ->where('id', $enrollmentId)
                               ->delete();
                
                // Check if deletion was successful (delete() returns number of affected rows)
                if ($deleted > 0) {
                    log_message('info', "Enrollment ID {$enrollmentId} (User ID: {$actualUserId}) successfully removed from course {$courseId} by teacher {$userId}. Affected rows: {$deleted}");
                    
                    // ✅ Create notification for the teacher who removed the student
                    try {
                        $userModel = new UserModel();
                        $student = $userModel->find($actualUserId);
                        $studentName = $student ? $student['name'] : 'Student';
                        $courseTitle = $course['title'] ?? 'Course';
                        
                        $notificationModel = new NotificationModel();
                        $teacherNotificationId = $notificationModel->createNotification(
                            (int)$userId,
                            "You have successfully removed '{$studentName}' from '{$courseTitle}'."
                        );
                        if ($teacherNotificationId) {
                            log_message('info', "✅ Teacher notification created for student removal. Teacher ID: {$userId}, Notification ID: {$teacherNotificationId}");
                        }
                    } catch (\Exception $teacherNotifError) {
                        log_message('warning', 'Teacher notification creation failed for student removal: ' . $teacherNotifError->getMessage());
                    }
                    
                    // Check if coming from enroll page or view page
                    $redirectUrl = $this->request->getGet('from') === 'enroll' 
                        ? '/teacher/courses/' . $courseId . '/enroll-students' 
                        : '/teacher/courses/view/' . $courseId;
                    return redirect()->to($redirectUrl)->with('success', '✅ Student removed from course!');
                } else {
                    log_message('error', "Failed to delete enrollment: course_id={$courseId}, user_id={$studentId}. Delete returned: {$deleted}");
                    // Check if coming from enroll page or view page
                    $redirectUrl = $this->request->getGet('from') === 'enroll' 
                        ? '/teacher/courses/' . $courseId . '/enroll-students' 
                        : '/teacher/courses/view/' . $courseId;
                    return redirect()->to($redirectUrl)->with('error', 'Failed to remove student from course. Please try again.');
                }
            } else {
                log_message('error', 'Enrollments table does not exist');
                $redirectUrl = $this->request->getGet('from') === 'enroll' 
                    ? '/teacher/courses/' . $courseId . '/enroll-students' 
                    : '/teacher/courses/view/' . $courseId;
                return redirect()->to($redirectUrl)->with('error', 'Enrollments table not found.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error removing enrollment: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $redirectUrl = $this->request->getGet('from') === 'enroll' 
                ? '/teacher/courses/' . $courseId . '/enroll-students' 
                : '/teacher/courses/view/' . $courseId;
            return redirect()->to($redirectUrl)->with('error', 'Error removing student from course: ' . $e->getMessage());
        }
    }

    // ✅ Enroll Students (Teacher - for their assigned courses)
    public function enrollStudents($courseId)
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

        $enrollmentModel = new EnrollmentModel();
        $userModel = new UserModel();

        // Get all students
        $students = $userModel->where('role', 'student')->where('status', 'active')->findAll();
        
        // Get enrolled students
        $enrolledStudents = $enrollmentModel->getEnrollmentsByCourse($courseId);

        $data = [
            'title' => 'Enroll Students',
            'course' => $course,
            'students' => $students,
            'enrolled_students' => $enrolledStudents,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('teacher/enroll_students', $data);
    }

    // ✅ Enroll Student (Teacher - AJAX)
    public function enrollStudent($courseId)
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'POST') {
            $db = \Config\Database::connect();
            $userId = $session->get('user_id');

            // Verify course ownership
            try {
                $courseResult = $db->table('courses')
                                 ->where('id', $courseId)
                                 ->where('instructor_id', $userId)
                                 ->get();
                
                $course = ($courseResult !== false && is_object($courseResult)) ? $courseResult->getRowArray() : null;
            } catch (\Exception $e) {
                log_message('error', 'Error fetching course in enrollStudent: ' . $e->getMessage());
                $course = null;
            }

            if (!$course) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Course not found or access denied'
                ]);
            }

            $studentId = $this->request->getPost('student_id');
            if (empty($studentId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Student is required'
                ]);
            }

            $enrollmentModel = new EnrollmentModel();

            // Check if already enrolled
            if ($enrollmentModel->isAlreadyEnrolled($studentId, $courseId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Student is already enrolled in this course'
                ]);
            }

            // ✅ Validate department/program match for students
            $userModel = new UserModel();
            $student = $userModel->find($studentId);
            $db = \Config\Database::connect();
            
            $courseResult = $db->table('courses')->where('id', $courseId)->get();
            $course = ($courseResult !== false && is_object($courseResult)) ? $courseResult->getRowArray() : null;
            
            if (!$student) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Student not found'
                ]);
            }
            
            if (!$course) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Course not found'
                ]);
            }
            
            if (strtolower($student['role']) === 'student') {
                $userDeptId = $student['department_id'] ?? null;
                $userProgId = $student['program_id'] ?? null;
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

            // Enroll student
            try {
                $enrollmentModel->enrollUser([
                    'user_id' => $studentId,
                    'course_id' => $courseId,
                    'enrolled_at' => date('Y-m-d H:i:s'),
                    'enrollment_date' => date('Y-m-d H:i:s'),
                    'completion_status' => 'ENROLLED',
                ]);

                // Get student and course info for notifications
                $studentName = $student ? $student['name'] : 'Student';
                $courseTitle = $course['title'] ?? 'Course';
                
                // ✅ Create notification for the enrolled student
                try {
                    $notificationModel = new NotificationModel();
                    $studentNotificationId = $notificationModel->createNotification(
                        (int)$studentId,
                        "You have been successfully enrolled in '{$courseTitle}'!"
                    );
                    if ($studentNotificationId) {
                        log_message('info', "✅ Student notification created successfully! Student ID: {$studentId}, Notification ID: {$studentNotificationId}");
                    } else {
                        log_message('warning', "❌ Student notification creation returned false for student ID: {$studentId}");
                    }
                } catch (\Exception $notifError) {
                    log_message('error', '❌ Student notification creation failed for student ' . $studentId . ': ' . $notifError->getMessage());
                    log_message('error', 'Notification error trace: ' . $notifError->getTraceAsString());
                }
                
                // ✅ Create notification for the teacher who enrolled the student
                try {
                    $notificationModel = new NotificationModel();
                    $teacherNotificationId = $notificationModel->createNotification(
                        (int)$userId,
                        "You have successfully enrolled '{$studentName}' in '{$courseTitle}'."
                    );
                    if ($teacherNotificationId) {
                        log_message('info', "✅ Teacher notification created successfully! Teacher ID: {$userId}, Notification ID: {$teacherNotificationId}");
                    } else {
                        log_message('warning', "❌ Teacher notification creation returned false for teacher ID: {$userId}");
                    }
                } catch (\Exception $teacherNotifError) {
                    log_message('warning', 'Teacher notification creation failed: ' . $teacherNotifError->getMessage());
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Student enrolled successfully!',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error enrolling student: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Assignments Management (Teacher)
    public function assignments($courseId)
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

        $assignmentModel = new AssignmentModel();
        $gradingPeriodModel = new GradingPeriodModel();

        // Handle assignment creation
        if ($this->request->getMethod() === 'POST' && $this->request->getPost('action') === 'create') {
            // Get course info first
            $course = $db->table('courses')
                         ->where('id', $courseId)
                         ->where('instructor_id', $userId)
                         ->get()
                         ->getRowArray();
            
            $data = [
                'course_id' => $courseId,
                'grading_period_id' => $this->request->getPost('grading_period_id'),
                'assignment_type' => $this->request->getPost('assignment_type'),
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'max_score' => $this->request->getPost('max_score'),
                'due_date' => $this->request->getPost('due_date') ? date('Y-m-d H:i:s', strtotime($this->request->getPost('due_date'))) : null,
            ];

            if ($assignmentModel->save($data)) {
                // ✅ Create notifications for all enrolled students
                try {
                    $notificationModel = new NotificationModel();
                    $enrollmentModel = new EnrollmentModel();
                    $enrolledStudents = $enrollmentModel->getEnrollmentsByCourse($courseId);
                    $courseTitle = $course['title'] ?? 'Course';
                    $assignmentTitle = $data['title'];
                    
                    $notificationCount = 0;
                    foreach ($enrolledStudents as $enrollment) {
                        $studentId = isset($enrollment['user_id']) ? (int)$enrollment['user_id'] : null;
                        if ($studentId) {
                            $notificationId = $notificationModel->createNotification(
                                $studentId,
                                "New assignment '{$assignmentTitle}' has been posted for {$courseTitle}!"
                            );
                            if ($notificationId) {
                                $notificationCount++;
                            }
                        }
                    }
                    log_message('info', "Created {$notificationCount} notifications for assignment '{$assignmentTitle}' in course {$courseId}");
                } catch (\Exception $notifError) {
                    log_message('warning', 'Notification creation failed: ' . $notifError->getMessage());
                }
                
                // ✅ Create notification for the teacher who created the assignment
                try {
                    $teacherId = $userId;
                    if ($teacherId) {
                        $notificationModel = new NotificationModel();
                        $teacherNotificationId = $notificationModel->createNotification(
                            (int)$teacherId,
                            "You have successfully created assignment '{$assignmentTitle}' for '{$courseTitle}'."
                        );
                        if ($teacherNotificationId) {
                            log_message('info', "✅ Teacher notification created for assignment creation. Teacher ID: {$teacherId}, Notification ID: {$teacherNotificationId}");
                        } else {
                            log_message('warning', "❌ Teacher notification creation returned false for teacher ID: {$teacherId}");
                        }
                    }
                } catch (\Exception $teacherNotifError) {
                    log_message('warning', 'Teacher notification creation failed: ' . $teacherNotifError->getMessage());
                }
                
                return redirect()->back()->with('success', 'Assignment created successfully!');
            } else {
                return redirect()->back()->withInput()->with('errors', $assignmentModel->errors());
            }
        }

        // Get assignments for this course
        $assignments = $assignmentModel->getAssignmentsByCourse($courseId);
        
        // Get grading periods
        $gradingPeriods = [];
        if ($db->query("SHOW TABLES LIKE 'grading_periods'")->getNumRows() > 0) {
            $gradingPeriods = $db->table('grading_periods')
                                  ->where('is_active', 1)
                                  ->orderBy('start_date', 'ASC')
                                  ->get()
                                  ->getResultArray();
        }

        $data = [
            'title' => 'Assignments Management',
            'course' => $course,
            'assignments' => $assignments,
            'grading_periods' => $gradingPeriods,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('teacher/assignments', $data);
    }

    // ✅ Delete Assignment (Teacher)
    public function deleteAssignment($assignmentId)
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        $assignmentModel = new AssignmentModel();

        // Get assignment
        $assignment = $assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        // Verify course ownership
        $course = $db->table('courses')
                     ->where('id', $assignment['course_id'])
                     ->where('instructor_id', $userId)
                     ->get()
                     ->getRowArray();

        if (!$course) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        // Get assignment info for notification
        $assignmentTitle = $assignment['title'] ?? 'Assignment';
        $courseTitle = $course['title'] ?? 'Course';

        // Delete assignment
        if ($assignmentModel->delete($assignmentId)) {
            // ✅ Create notification for the teacher who deleted the assignment
            try {
                $notificationModel = new NotificationModel();
                $teacherNotificationId = $notificationModel->createNotification(
                    (int)$userId,
                    "You have successfully deleted assignment '{$assignmentTitle}' from '{$courseTitle}'."
                );
                if ($teacherNotificationId) {
                    log_message('info', "✅ Teacher notification created for assignment deletion. Teacher ID: {$userId}, Notification ID: {$teacherNotificationId}");
                }
            } catch (\Exception $teacherNotifError) {
                log_message('warning', 'Teacher notification creation failed for assignment deletion: ' . $teacherNotifError->getMessage());
            }

            return redirect()->back()->with('success', 'Assignment deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to delete assignment.');
        }
    }

    // ✅ Grade Assignment (Teacher)
    public function gradeAssignment($assignmentId)
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');

        $assignmentModel = new AssignmentModel();
        $gradeModel = new GradeModel();
        $enrollmentModel = new EnrollmentModel();

        // Get assignment
        $assignment = $assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        // Verify course ownership
        $course = $db->table('courses')
                     ->where('id', $assignment['course_id'])
                     ->where('instructor_id', $userId)
                     ->get()
                     ->getRowArray();

        if (!$course) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        if ($this->request->getMethod() === 'POST') {
            $scores = $this->request->getPost('scores');
            $enrollmentIds = $this->request->getPost('enrollment_ids');

            if (empty($scores) || !is_array($scores)) {
                return redirect()->back()->with('error', 'No scores provided.');
            }

            $savedCount = 0;
            foreach ($scores as $enrollmentId => $score) {
                if ($score === '' || $score === null) {
                    continue; // Skip empty scores
                }

                $score = floatval($score);
                if ($score < 0 || $score > $assignment['max_score']) {
                    continue; // Skip invalid scores
                }

                // Calculate percentage
                $percentage = ($score / $assignment['max_score']) * 100;
                $remarks = $percentage >= 75 ? 'Passed' : ($percentage > 0 ? 'Failed' : '');

                // Check if grade exists
                $existingGrade = $gradeModel->getGradeByAssignment($enrollmentId, $assignmentId);

                $gradeData = [
                    'enrollment_id' => $enrollmentId,
                    'assignment_id' => $assignmentId,
                    'score' => $score,
                    'percentage' => round($percentage, 2),
                    'remarks' => $remarks,
                    'graded_by' => $userId,
                    'graded_at' => date('Y-m-d H:i:s'),
                ];

                if ($existingGrade) {
                    $gradeModel->update($existingGrade['id'], $gradeData);
                } else {
                    $gradeModel->insert($gradeData);
                }

                // Trigger final grade calculation
                $gradeModel->updateEnrollmentFinalGrade($enrollmentId);
                $savedCount++;
            }

            // ✅ Create notification for the teacher who graded the assignment
            if ($savedCount > 0) {
                try {
                    $courseTitle = $course['title'] ?? 'Course';
                    $assignmentTitle = $assignment['title'] ?? 'Assignment';
                    $teacherId = $userId;
                    if ($teacherId) {
                        $notificationModel = new NotificationModel();
                        $teacherNotificationId = $notificationModel->createNotification(
                            (int)$teacherId,
                            "You have successfully graded {$savedCount} student(s) for assignment '{$assignmentTitle}' in '{$courseTitle}'."
                        );
                        if ($teacherNotificationId) {
                            log_message('info', "✅ Teacher notification created for assignment grading. Teacher ID: {$teacherId}, Notification ID: {$teacherNotificationId}");
                        }
                    }
                } catch (\Exception $teacherNotifError) {
                    log_message('warning', 'Teacher notification creation failed for assignment grading: ' . $teacherNotifError->getMessage());
                }
                
                return redirect()->back()->with('success', "Successfully saved {$savedCount} grade(s)!");
            } else {
                return redirect()->back()->with('error', 'No valid grades to save.');
            }
        }

        // Get enrolled students
        $enrollments = $enrollmentModel->getEnrollmentsByCourse($assignment['course_id']);
        
        // Get existing grades
        $grades = [];
        foreach ($enrollments as $enrollment) {
            $grade = $gradeModel->getGradeByAssignment($enrollment['id'], $assignmentId);
            $grades[$enrollment['id']] = $grade;
        }

        $data = [
            'title' => 'Grade Assignment',
            'assignment' => $assignment,
            'course' => $course,
            'enrollments' => $enrollments,
            'grades' => $grades,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('teacher/grade_assignment', $data);
    }

    // ✅ View All Quizzes for Teacher's Courses
    public function quizzes()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'teacher') {
            return redirect()->to('/teacher_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        $userId = $session->get('user_id');
        $quizModel = new QuizModel();
        $submissionModel = new SubmissionModel();

        // Get all courses assigned to this teacher
        $courses = [];
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            try {
                $result = $db->table('courses')
                    ->where('instructor_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->get();
                
                if ($result !== false && is_object($result)) {
                    $courses = $result->getResultArray();
                    log_message('debug', 'Teacher courses found: ' . count($courses));
                } else {
                    log_message('debug', 'No courses found for teacher ID: ' . $userId);
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to fetch courses: ' . $e->getMessage());
            }
        }

        // Get all quizzes for teacher's courses
        $allQuizzes = [];
        if (!empty($courses) && $db->query("SHOW TABLES LIKE 'quizzes'")->getNumRows() > 0) {
            try {
                // Get all course IDs
                $courseIds = array_column($courses, 'id');
                
                if (!empty($courseIds)) {
                    // Get all quizzes for teacher's courses in one query
                    $result = $db->table('quizzes')
                        ->whereIn('course_id', $courseIds)
                        ->orderBy('created_at', 'DESC')
                        ->get();
                    
                    if ($result !== false && is_object($result)) {
                        $allQuizzes = $result->getResultArray();
                        
                        // Add course info and submission counts
                        $courseMap = [];
                        foreach ($courses as $course) {
                            $courseMap[$course['id']] = $course;
                        }
                        
                        foreach ($allQuizzes as &$quiz) {
                            $courseId = $quiz['course_id'] ?? null;
                            if (isset($courseMap[$courseId])) {
                                $quiz['course_title'] = $courseMap[$courseId]['title'];
                                $quiz['course_number'] = $courseMap[$courseId]['course_number'] ?? '';
                            } else {
                                $quiz['course_title'] = 'Unknown Course';
                                $quiz['course_number'] = '';
                            }
                            
                            // Ensure course_id is set
                            if (!isset($quiz['course_id'])) {
                                $quiz['course_id'] = $courseId;
                            }
                            
                            // Get submission count
                            try {
                                if ($db->query("SHOW TABLES LIKE 'submissions'")->getNumRows() > 0) {
                                    $quiz['submission_count'] = $submissionModel->where('quiz_id', $quiz['id'])->countAllResults();
                                } else {
                                    $quiz['submission_count'] = 0;
                                }
                            } catch (\Exception $e) {
                                $quiz['submission_count'] = 0;
                                log_message('error', 'Failed to count submissions: ' . $e->getMessage());
                            }
                        }
                    } else {
                        log_message('debug', 'No course IDs found for teacher');
                    }
                } else {
                    log_message('debug', 'Course IDs array is empty');
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to fetch quizzes: ' . $e->getMessage());
                $allQuizzes = [];
            }
        } else {
            log_message('debug', 'No courses found or quizzes table does not exist. Courses: ' . count($courses));
        }

        $data = [
            'title' => 'Quizzes & Submissions',
            'courses' => $courses,
            'quizzes' => $allQuizzes,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('teacher/quizzes', $data);
    }

    // ✅ Student: View Assignments for a Course
    public function studentAssignments($courseId)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $userRole = strtolower($session->get('user_role'));
        if ($userRole !== 'student') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $userId = $session->get('user_id');
        $db = \Config\Database::connect();
        
        // Verify student is enrolled in this course
        $enrollment = $db->table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            return redirect()->to('/dashboard')->with('error', 'You are not enrolled in this course.');
        }

        // Get course
        $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
        if (!$course) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        // Get assignments for this course
        $assignmentModel = new AssignmentModel();
        $assignments = $assignmentModel->getAssignmentsByCourse($courseId);
        
        // Get grades for each assignment
        $gradeModel = new GradeModel();
        $enrollmentModel = new EnrollmentModel();
        $enrollmentRecord = $enrollmentModel->where('user_id', $userId)
                                            ->where('course_id', $courseId)
                                            ->first();
        
        foreach ($assignments as &$assignment) {
            if ($enrollmentRecord) {
                $grade = $gradeModel->getGradeByAssignment($enrollmentRecord['id'], $assignment['id']);
                $assignment['grade'] = $grade;
                $assignment['score'] = $grade ? $grade['score'] : null;
                $assignment['percentage'] = $grade ? $grade['percentage'] : null;
                $assignment['remarks'] = $grade ? $grade['remarks'] : null;
            } else {
                $assignment['grade'] = null;
                $assignment['score'] = null;
                $assignment['percentage'] = null;
                $assignment['remarks'] = null;
            }
        }

        $data = [
            'title' => 'Course Assignments',
            'course' => $course,
            'assignments' => $assignments,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
        ];

        return view('assignments/student_index', $data);
    }
}
