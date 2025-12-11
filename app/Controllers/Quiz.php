<?php

namespace App\Controllers;

use App\Models\QuizModel;
use App\Models\SubmissionModel;
use App\Models\EnrollmentModel;
use App\Models\AssignmentModel;
use App\Models\GradeModel;
use App\Models\GradingPeriodModel;
use App\Models\NotificationModel;
use CodeIgniter\Controller;

class Quiz extends BaseController
{
    protected $quizModel;
    protected $submissionModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->quizModel = new QuizModel();
        $this->submissionModel = new SubmissionModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    // ✅ List Quizzes for a Course (Teacher/Admin)
    public function index($courseId = null)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $userRole = strtolower($session->get('user_role'));
        if (!in_array($userRole, ['teacher', 'admin'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        
        // Get course
        $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
        if (!$course) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        // Get quizzes for this course
        $quizzes = $this->quizModel->getQuizzesByCourse($courseId);
        
        // Get submission counts for each quiz
        foreach ($quizzes as &$quiz) {
            $quiz['submission_count'] = $this->submissionModel->where('quiz_id', $quiz['id'])->countAllResults();
        }

        $data = [
            'title' => 'Quizzes',
            'course' => $course,
            'quizzes' => $quizzes,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
        ];

        return view('quiz/index', $data);
    }

    // ✅ Create Quiz (Teacher/Admin)
    public function create($courseId = null)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $userRole = strtolower($session->get('user_role'));
        if (!in_array($userRole, ['teacher', 'admin'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        
        // Get course
        $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
        if (!$course) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        if ($this->request->getMethod() === 'POST') {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'title' => 'required|min_length[3]|max_length[255]',
                'description' => 'permit_empty',
                'max_score' => 'permit_empty|decimal',
                'due_date' => 'permit_empty|valid_date',
            ]);

            if (!$validation->run($this->request->getPost())) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            // Get grading period for the course (through term or semester)
            $gradingPeriodId = null;
            if ($db->query("SHOW TABLES LIKE 'grading_periods'")->getNumRows() > 0) {
                try {
                    // Get course to find term_id or semester_id
                    $courseData = $db->table('courses')
                        ->select('term_id, semester_id')
                        ->where('id', $courseId)
                        ->get()
                        ->getRowArray();
                    
                    if ($courseData) {
                        $gradingPeriodModel = new GradingPeriodModel();
                        $gradingPeriod = null;
                        
                        // Try to get grading period by term_id first
                        if (!empty($courseData['term_id'])) {
                            $result = $gradingPeriodModel->where('term_id', $courseData['term_id'])->get();
                            if ($result !== false && is_object($result)) {
                                $gradingPeriod = $result->getFirstRow('array');
                            }
                        }
                        
                        // If not found, try semester_id
                        if (!$gradingPeriod && !empty($courseData['semester_id'])) {
                            $result = $gradingPeriodModel->where('semester_id', $courseData['semester_id'])->get();
                            if ($result !== false && is_object($result)) {
                                $gradingPeriod = $result->getFirstRow('array');
                            }
                        }
                        
                        // If still not found, get the first active period or create a default
                        if (!$gradingPeriod) {
                            $result = $gradingPeriodModel->where('is_active', 1)->get();
                            if ($result !== false && is_object($result)) {
                                $gradingPeriod = $result->getFirstRow('array');
                            }
                            
                            // If no active period exists, create a default one
                            if (!$gradingPeriod) {
                                $periodData = [
                                    'term_id' => $courseData['term_id'] ?? null,
                                    'semester_id' => $courseData['semester_id'] ?? null,
                                    'period_name' => 'Default Period',
                                    'start_date' => date('Y-m-d'),
                                    'end_date' => date('Y-m-d', strtotime('+3 months')),
                                    'is_active' => 1,
                                ];
                                $gradingPeriodModel->skipValidation(true);
                                $gradingPeriodId = $gradingPeriodModel->insert($periodData);
                                $gradingPeriodModel->skipValidation(false);
                            } else {
                                $gradingPeriodId = $gradingPeriod['id'];
                            }
                        } else {
                            $gradingPeriodId = $gradingPeriod['id'];
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to get/create grading period: ' . $e->getMessage());
                    // Continue without grading period - use default value
                    $gradingPeriodId = 1; // Default fallback
                }
            } else {
                // Table doesn't exist, use default
                $gradingPeriodId = 1;
            }

            // Convert due_date format if provided
            $dueDate = $this->request->getPost('due_date');
            if (!empty($dueDate)) {
                // Convert datetime-local format (YYYY-MM-DDTHH:mm) to MySQL datetime format
                $dueDate = str_replace('T', ' ', $dueDate);
                if (strlen($dueDate) == 16) {
                    $dueDate .= ':00'; // Add seconds if missing
                }
            } else {
                $dueDate = null;
            }

            // Create assignment first (for grading integration)
            $assignmentModel = new AssignmentModel();
            $assignmentData = [
                'course_id' => (int)$courseId,
                'grading_period_id' => $gradingPeriodId ? (int)$gradingPeriodId : 1, // Default to 1 if no period
                'assignment_type' => 'Quiz',
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description') ?: null,
                'max_score' => $this->request->getPost('max_score') ? (float)$this->request->getPost('max_score') : 100.00,
                'due_date' => $dueDate,
            ];
            
            try {
                $assignmentModel->skipValidation(true);
                $assignmentId = $assignmentModel->insert($assignmentData);
                $assignmentModel->skipValidation(false);

                if (!$assignmentId) {
                    $errors = $assignmentModel->errors();
                    log_message('error', 'Assignment insert failed: ' . json_encode($errors));
                    return redirect()->back()->withInput()->with('error', 'Failed to create assignment for quiz: ' . (empty($errors) ? 'Unknown error' : implode(', ', $errors)));
                }
            } catch (\Exception $e) {
                log_message('error', 'Assignment creation exception: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to create assignment: ' . $e->getMessage());
            }

            // Create quiz linked to assignment
            $data = [
                'lesson_id' => null, // Explicitly set to null
                'course_id' => (int)$courseId,
                'assignment_id' => (int)$assignmentId,
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description') ?: null,
                'max_score' => $this->request->getPost('max_score') ? (float)$this->request->getPost('max_score') : 100.00,
                'due_date' => $dueDate,
            ];

            try {
                // Use direct database insert to avoid model issues
                // Ensure all required fields are present
                // Don't include lesson_id in insert if it's null (let database use default)
                $insertData = [
                    'course_id' => (int)$courseId,
                    'assignment_id' => (int)$assignmentId,
                    'title' => $this->request->getPost('title'),
                    'description' => !empty($this->request->getPost('description')) ? $this->request->getPost('description') : null,
                    'max_score' => $this->request->getPost('max_score') ? (float)$this->request->getPost('max_score') : 100.00,
                    'due_date' => $dueDate,
                ];
                
                log_message('debug', 'Quiz insert data: ' . json_encode($insertData));
                
                $result = $db->table('quizzes')->insert($insertData);
                
                if ($result) {
                    $quizId = $db->insertID();
                    log_message('info', 'Quiz created successfully with ID: ' . $quizId);
                    
                    // ✅ Create notifications for all enrolled students
                    try {
                        $notificationModel = new NotificationModel();
                        $enrollmentModel = new EnrollmentModel();
                        $enrolledStudents = $enrollmentModel->getEnrollmentsByCourse($courseId);
                        $courseTitle = $course['title'] ?? 'Course';
                        $quizTitle = $this->request->getPost('title');
                        
                        $notificationCount = 0;
                        foreach ($enrolledStudents as $enrollment) {
                            $studentId = isset($enrollment['user_id']) ? (int)$enrollment['user_id'] : null;
                            if ($studentId) {
                                $notificationId = $notificationModel->createNotification(
                                    $studentId,
                                    "New quiz '{$quizTitle}' has been posted for {$courseTitle}!"
                                );
                                if ($notificationId) {
                                    $notificationCount++;
                                }
                            }
                        }
                        log_message('info', "Created {$notificationCount} notifications for quiz '{$quizTitle}' in course {$courseId}");
                    } catch (\Exception $notifError) {
                        log_message('error', 'Notification creation failed: ' . $notifError->getMessage());
                        log_message('error', 'Notification error trace: ' . $notifError->getTraceAsString());
                    }
                    
                    // ✅ Create notification for the teacher who created the quiz
                    try {
                        $session = session();
                        $teacherId = $session->get('user_id');
                        if ($teacherId) {
                            $notificationModel = new NotificationModel();
                            $teacherNotificationId = $notificationModel->createNotification(
                                (int)$teacherId,
                                "You have successfully created quiz '{$quizTitle}' for '{$courseTitle}'."
                            );
                            if ($teacherNotificationId) {
                                log_message('info', "✅ Teacher notification created for quiz creation. Teacher ID: {$teacherId}, Notification ID: {$teacherNotificationId}");
                            } else {
                                log_message('warning', "❌ Teacher notification creation returned false for teacher ID: {$teacherId}");
                            }
                        }
                    } catch (\Exception $teacherNotifError) {
                        log_message('warning', 'Teacher notification creation failed: ' . $teacherNotifError->getMessage());
                    }
                    
                    return redirect()->to("quiz/course/{$courseId}")->with('success', 'Quiz created successfully and linked to grading system!');
                } else {
                    $dbError = $db->error();
                    log_message('error', 'Quiz insert failed. Database error: ' . json_encode($dbError));
                    
                    // Rollback: delete assignment if quiz creation fails
                    try {
                        $assignmentModel->delete($assignmentId);
                    } catch (\Exception $e2) {
                        log_message('error', 'Failed to rollback assignment: ' . $e2->getMessage());
                    }
                    
                    $errorMsg = 'Failed to create quiz. ';
                    if (!empty($dbError) && isset($dbError['message'])) {
                        $errorMsg .= 'Database error: ' . $dbError['message'];
                    } else {
                        $errorMsg .= 'Please check the logs for details.';
                    }
                    
                    return redirect()->back()->withInput()->with('error', $errorMsg);
                }
            } catch (\Exception $e) {
                // Rollback: delete assignment if quiz creation fails
                try {
                    $assignmentModel->delete($assignmentId);
                } catch (\Exception $e2) {
                    log_message('error', 'Failed to rollback assignment: ' . $e2->getMessage());
                }
                log_message('error', 'Quiz creation exception: ' . $e->getMessage());
                log_message('error', 'Quiz creation exception trace: ' . $e->getTraceAsString());
                return redirect()->back()->withInput()->with('error', 'Failed to create quiz: ' . $e->getMessage());
            }
        }

        $data = [
            'title' => 'Create Quiz',
            'course' => $course,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
        ];

        return view('quiz/create', $data);
    }

    // ✅ View Quiz Submissions (Teacher/Admin)
    public function submissions($quizId)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $userRole = strtolower($session->get('user_role'));
        if (!in_array($userRole, ['teacher', 'admin'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $quiz = $this->quizModel->find($quizId);
        if (!$quiz) {
            return redirect()->back()->with('error', 'Quiz not found.');
        }

        $submissions = $this->submissionModel->getSubmissionsByQuiz($quizId);

        $db = \Config\Database::connect();
        $course = $db->table('courses')->where('id', $quiz['course_id'])->get()->getRowArray();

        $data = [
            'title' => 'Quiz Submissions',
            'quiz' => $quiz,
            'course' => $course,
            'submissions' => $submissions,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
        ];

        return view('quiz/submissions', $data);
    }

    // ✅ Delete Submission (Teacher/Admin)
    public function deleteSubmission($submissionId)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $userRole = strtolower($session->get('user_role'));
        if (!in_array($userRole, ['teacher', 'admin'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $submission = $this->submissionModel->find($submissionId);
        if (!$submission) {
            return redirect()->back()->with('error', 'Submission not found.');
        }

        // Get quiz and course info for notification
        $quiz = $this->quizModel->find($submission['quiz_id']);
        if (!$quiz) {
            return redirect()->back()->with('error', 'Quiz not found.');
        }

        $db = \Config\Database::connect();
        $course = $db->table('courses')->where('id', $quiz['course_id'])->get()->getRowArray();
        $courseTitle = $course ? $course['title'] : 'Course';
        $quizTitle = $quiz['title'] ?? 'Quiz';

        // Get student info
        $userModel = new \App\Models\UserModel();
        $student = $userModel->find($submission['user_id']);
        $studentName = $student ? $student['name'] : 'Student';

        // Delete submission
        if ($this->submissionModel->delete($submissionId)) {
            // ✅ Create notification for the teacher who deleted the submission
            try {
                $teacherId = $session->get('user_id');
                if ($teacherId) {
                    $notificationModel = new NotificationModel();
                    $teacherNotificationId = $notificationModel->createNotification(
                        (int)$teacherId,
                        "You have successfully deleted '{$studentName}'s submission for quiz '{$quizTitle}' in '{$courseTitle}'."
                    );
                    if ($teacherNotificationId) {
                        log_message('info', "✅ Teacher notification created for submission deletion. Teacher ID: {$teacherId}, Notification ID: {$teacherNotificationId}");
                    }
                }
            } catch (\Exception $teacherNotifError) {
                log_message('warning', 'Teacher notification creation failed for submission deletion: ' . $teacherNotifError->getMessage());
            }

            return redirect()->back()->with('success', 'Submission deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to delete submission.');
        }
    }

    // ✅ Grade Submission (Teacher/Admin) - Normalized with Grades Table
    public function gradeSubmission()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please login first.'
            ])->setStatusCode(401);
        }

        $userRole = strtolower($session->get('user_role'));
        if (!in_array($userRole, ['teacher', 'admin'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access denied.'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'POST') {
            $submissionId = $this->request->getPost('submission_id');
            $score = $this->request->getPost('score');

            if (empty($submissionId) || $score === null) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Submission ID and score are required.'
                ]);
            }

            $submission = $this->submissionModel->find($submissionId);
            if (!$submission) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Submission not found.'
                ]);
            }

            // Get quiz to find assignment_id
            $quiz = $this->quizModel->find($submission['quiz_id']);
            if (!$quiz || empty($quiz['assignment_id'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Quiz or assignment not found. Please ensure quiz is properly linked to an assignment.'
                ]);
            }

            $db = \Config\Database::connect();
            
            // Get enrollment for this user and course
            $enrollment = $db->table('enrollments')
                ->where('user_id', $submission['user_id'])
                ->where('course_id', $quiz['course_id'])
                ->get()
                ->getRowArray();

            if (!$enrollment) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Student enrollment not found for this course.'
                ]);
            }

            // Update submission score
            $updateData = [
                'score' => floatval($score),
                'graded_by' => $session->get('user_id'),
                'graded_at' => date('Y-m-d H:i:s'),
            ];

            if (!$this->submissionModel->update($submissionId, $updateData)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to update submission.'
                ]);
            }

            // Create or update grade in grades table (normalized)
            $gradeModel = new GradeModel();
            $maxScore = $quiz['max_score'] ?? 100.00;
            $percentage = ($score / $maxScore) * 100;
            $remarks = $percentage >= 75 ? 'Passed' : ($percentage > 0 ? 'Failed' : '');

            $gradeData = [
                'enrollment_id' => $enrollment['id'],
                'assignment_id' => $quiz['assignment_id'],
                'score' => floatval($score),
                'percentage' => round($percentage, 2),
                'remarks' => $remarks,
                'graded_by' => $session->get('user_id'),
                'graded_at' => date('Y-m-d H:i:s'),
            ];

            // Check if grade already exists
            $existingGrade = $gradeModel->where('enrollment_id', $enrollment['id'])
                                       ->where('assignment_id', $quiz['assignment_id'])
                                       ->first();

            if ($existingGrade) {
                $gradeModel->update($existingGrade['id'], $gradeData);
            } else {
                $gradeModel->skipValidation(true);
                $gradeModel->insert($gradeData);
                $gradeModel->skipValidation(false);
            }

            // Trigger final grade calculation
            $gradeModel->updateEnrollmentFinalGrade($enrollment['id']);

            // ✅ Create notification for the teacher who graded the submission
            try {
                $db = \Config\Database::connect();
                $course = $db->table('courses')->where('id', $quiz['course_id'])->get()->getRowArray();
                $courseTitle = $course ? $course['title'] : 'Course';
                $quizTitle = $quiz['title'] ?? 'Quiz';
                
                // Get student info
                $userModel = new \App\Models\UserModel();
                $student = $userModel->find($submission['user_id']);
                $studentName = $student ? $student['name'] : 'Student';
                
                $teacherId = $session->get('user_id');
                if ($teacherId) {
                    $notificationModel = new NotificationModel();
                    $teacherNotificationId = $notificationModel->createNotification(
                        (int)$teacherId,
                        "You have successfully graded '{$studentName}'s submission for quiz '{$quizTitle}' in '{$courseTitle}'."
                    );
                    if ($teacherNotificationId) {
                        log_message('info', "✅ Teacher notification created for grading. Teacher ID: {$teacherId}, Notification ID: {$teacherNotificationId}");
                    }
                }
            } catch (\Exception $teacherNotifError) {
                log_message('warning', 'Teacher notification creation failed for grading: ' . $teacherNotifError->getMessage());
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Submission graded successfully and grade recorded in system!',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
    }

    // ✅ Delete Quiz (Teacher/Admin) - Also deletes linked assignment
    public function delete($quizId)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $userRole = strtolower($session->get('user_role'));
        if (!in_array($userRole, ['teacher', 'admin'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $quiz = $this->quizModel->find($quizId);
        if (!$quiz) {
            return redirect()->back()->with('error', 'Quiz not found.');
        }

        // Get course and quiz info for notification
        $db = \Config\Database::connect();
        $course = $db->table('courses')->where('id', $quiz['course_id'])->get()->getRowArray();
        $courseTitle = $course ? $course['title'] : 'Course';
        $quizTitle = $quiz['title'] ?? 'Quiz';
        $assignmentDeleted = false;
        
        // Delete associated assignment if exists
        if (!empty($quiz['assignment_id'])) {
            $assignmentModel = new AssignmentModel();
            try {
                $assignmentModel->delete($quiz['assignment_id']);
                $assignmentDeleted = true;
            } catch (\Exception $e) {
                log_message('error', 'Failed to delete assignment: ' . $e->getMessage());
            }
        }

        if ($this->quizModel->delete($quizId)) {
            // ✅ Create notification for the teacher who deleted the quiz
            try {
                $session = session();
                $teacherId = $session->get('user_id');
                if ($teacherId) {
                    $notificationModel = new NotificationModel();
                    $message = $assignmentDeleted 
                        ? "You have successfully deleted quiz '{$quizTitle}' and its associated assignment from '{$courseTitle}'."
                        : "You have successfully deleted quiz '{$quizTitle}' from '{$courseTitle}'.";
                    
                    $teacherNotificationId = $notificationModel->createNotification(
                        (int)$teacherId,
                        $message
                    );
                    if ($teacherNotificationId) {
                        log_message('info', "✅ Teacher notification created for quiz deletion. Teacher ID: {$teacherId}, Notification ID: {$teacherNotificationId}");
                    }
                }
            } catch (\Exception $teacherNotifError) {
                log_message('warning', 'Teacher notification creation failed for quiz deletion: ' . $teacherNotifError->getMessage());
            }
            
            return redirect()->back()->with('success', 'Quiz and associated assignment deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to delete quiz.');
        }
    }

    // ✅ Student: View Quizzes for a Course
    public function studentIndex($courseId = null)
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

        // Get quizzes for this course
        $quizzes = $this->quizModel->getQuizzesByCourse($courseId);
        
        // Get submission status for each quiz
        foreach ($quizzes as &$quiz) {
            $submission = $this->submissionModel
                ->where('quiz_id', $quiz['id'])
                ->where('user_id', $userId)
                ->first();
            
            $quiz['submitted'] = !empty($submission);
            $quiz['submission'] = $submission;
            $quiz['can_take'] = empty($submission) || empty($submission['submitted_at']);
        }

        $data = [
            'title' => 'Course Quizzes',
            'course' => $course,
            'quizzes' => $quizzes,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
        ];

        return view('quiz/student_index', $data);
    }

    // ✅ Student: Take Quiz
    public function take($quizId)
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
        $quiz = $this->quizModel->find($quizId);
        
        if (!$quiz) {
            return redirect()->back()->with('error', 'Quiz not found.');
        }

        // Verify student is enrolled in this course
        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $quiz['course_id'])
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            return redirect()->to('/dashboard')->with('error', 'You are not enrolled in this course.');
        }

        // Check if already submitted
        $existingSubmission = $this->submissionModel
            ->where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->first();

        if ($existingSubmission && !empty($existingSubmission['submitted_at'])) {
            return redirect()->to("student/quiz/result/{$quizId}")->with('info', 'You have already submitted this quiz.');
        }

        // Get course
        $course = $db->table('courses')->where('id', $quiz['course_id'])->get()->getRowArray();

        $data = [
            'title' => 'Take Quiz',
            'quiz' => $quiz,
            'course' => $course,
            'submission' => $existingSubmission,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
        ];

        return view('quiz/take', $data);
    }

    // ✅ Student: Submit Quiz
    public function submit()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please login first.'
            ])->setStatusCode(401);
        }

        $userRole = strtolower($session->get('user_role'));
        if ($userRole !== 'student') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Access denied.'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
        }

        $userId = $session->get('user_id');
        $quizId = $this->request->getPost('quiz_id');
        $answers = $this->request->getPost('answers'); // JSON string or array

        if (empty($quizId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Quiz ID is required.'
            ]);
        }

        $quiz = $this->quizModel->find($quizId);
        if (!$quiz) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Quiz not found.'
            ]);
        }

        // Verify enrollment
        $db = \Config\Database::connect();
        $enrollment = $db->table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $quiz['course_id'])
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You are not enrolled in this course.'
            ]);
        }

        // Check if already submitted
        $existingSubmission = $this->submissionModel
            ->where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->first();

        if ($existingSubmission && !empty($existingSubmission['submitted_at'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You have already submitted this quiz.'
            ]);
        }

        // Prepare answers (if JSON string, decode it)
        if (is_string($answers)) {
            $answers = json_decode($answers, true);
        }

        // Create or update submission
        $submissionData = [
            'quiz_id' => $quizId,
            'user_id' => $userId,
            'answer' => is_array($answers) ? json_encode($answers) : (is_string($answers) ? $answers : ''),
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        if ($existingSubmission) {
            // Update existing draft
            $this->submissionModel->update($existingSubmission['id'], $submissionData);
            $submissionId = $existingSubmission['id'];
        } else {
            // Create new submission
            $this->submissionModel->skipValidation(true);
            $submissionId = $this->submissionModel->insert($submissionData);
            $this->submissionModel->skipValidation(false);
        }

        if (!$submissionId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to submit quiz.'
            ]);
        }

        // ✅ Create notification for the teacher when a student submits a quiz
        try {
            $db = \Config\Database::connect();
            $course = $db->table('courses')->where('id', $quiz['course_id'])->get()->getRowArray();
            $courseTitle = $course ? $course['title'] : 'Course';
            $quizTitle = $quiz['title'] ?? 'Quiz';
            
            // Get student info
            $userModel = new \App\Models\UserModel();
            $student = $userModel->find($userId);
            $studentName = $student ? $student['name'] : 'Student';
            
            // Get teacher (instructor) for the course
            $teacherId = $course['instructor_id'] ?? null;
            if ($teacherId) {
                $notificationModel = new NotificationModel();
                $teacherNotificationId = $notificationModel->createNotification(
                    (int)$teacherId,
                    "'{$studentName}' has submitted quiz '{$quizTitle}' for '{$courseTitle}'."
                );
                if ($teacherNotificationId) {
                    log_message('info', "✅ Teacher notification created for quiz submission. Teacher ID: {$teacherId}, Notification ID: {$teacherNotificationId}");
                } else {
                    log_message('warning', "❌ Teacher notification creation returned false for teacher ID: {$teacherId}");
                }
            }
        } catch (\Exception $teacherNotifError) {
            log_message('warning', 'Teacher notification creation failed for quiz submission: ' . $teacherNotifError->getMessage());
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Quiz submitted successfully!',
            'redirect' => site_url("student/quiz/result/{$quizId}")
        ]);
    }

    // ✅ Student: View Quiz Result
    public function viewResult($quizId)
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
        $quiz = $this->quizModel->find($quizId);
        
        if (!$quiz) {
            return redirect()->back()->with('error', 'Quiz not found.');
        }

        // Get submission
        $submission = $this->submissionModel
            ->where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->first();

        if (!$submission) {
            return redirect()->to("student/quiz/course/{$quiz['course_id']}")->with('error', 'You have not submitted this quiz yet.');
        }

        // Get course
        $db = \Config\Database::connect();
        $course = $db->table('courses')->where('id', $quiz['course_id'])->get()->getRowArray();

        $data = [
            'title' => 'Quiz Result',
            'quiz' => $quiz,
            'course' => $course,
            'submission' => $submission,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole,
        ];

        return view('quiz/result', $data);
    }
}

