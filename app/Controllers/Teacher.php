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
            $course = $db->table('courses')
                         ->where('id', $courseId)
                         ->where('instructor_id', $userId)
                         ->get()
                         ->getRowArray();

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

            // Enroll student
            try {
                $enrollmentModel->enrollUser([
                    'user_id' => $studentId,
                    'course_id' => $courseId,
                    'enrolled_at' => date('Y-m-d H:i:s'),
                    'enrollment_date' => date('Y-m-d H:i:s'),
                    'completion_status' => 'ENROLLED',
                ]);

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

            if ($savedCount > 0) {
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
}
