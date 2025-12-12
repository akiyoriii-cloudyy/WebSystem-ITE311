<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;

class Materials extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * Helper method to prepare view data for materials/upload view
     * Ensures user_role and deleted_materials are always included
     */
    private function prepareUploadViewData($course_id, $materials, $additionalData = [])
    {
        $session = session();
        $userRole = strtolower($session->get('user_role') ?? '');
        $materialModel = new MaterialModel();
        
        // For admins and teachers, also get deleted materials
        $deletedMaterials = [];
        if (in_array($userRole, ['admin', 'teacher'])) {
            $deletedMaterials = $materialModel->getDeletedMaterialsByCourse($course_id);
        }
        
        return array_merge([
            'course_id' => $course_id,
            'materials' => $materials,
            'deleted_materials' => $deletedMaterials,
            'user_role' => $userRole,
        ], $additionalData);
    }

    public function upload($course_id)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        // Validate course exists
        $db = \Config\Database::connect();
        $courseRow = null;
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            $courseRow = $db->table('courses')->where('id', (int)$course_id)->get()->getRowArray();
        }
        if (!$courseRow) {
            return redirect()->to(base_url('admin_dashboard'))->with('error', 'Invalid course.');
        }

        $materialModel = new MaterialModel();
        
        // Check if POST - use server var as it's more reliable
        $isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
        log_message('info', 'Upload handler - isPost: {isPost}', ['isPost' => $isPost ? 'YES' : 'NO']);

        if ($isPost) {
            try {
                log_message('info', 'Materials upload POST received for course_id: {id}', ['id' => $course_id]);
            
            $rules = [
                'file' => [
                    'label' => 'Material File',
                    'rules' => 'uploaded[file]|max_size[file,51200]|ext_in[file,pdf,ppt,pptx]'
                ]
            ];

            if (!$this->validate($rules)) {
                $errs = $this->validator ? $this->validator->getErrors() : [];
                $msg = !empty($errs) ? implode("\n", $errs) : 'Validation failed.';
                log_message('error', 'Materials upload validation failed: {msg}', ['msg' => $msg]);
                $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                    'error' => $msg,
                ]));
            }

            $file = $this->request->getFile('file');
            if ($file && $file->isValid()) {
                // ✅ Check for duplicate file name in the same course
                $fileName = $file->getClientName();
                $existingMaterial = $db->table('materials')
                    ->where('course_id', (int)$course_id)
                    ->where('file_name', $fileName)
                    ->groupStart()
                    ->where('status', 'active')
                    ->orWhere('status IS NULL')
                    ->groupEnd()
                    ->get()
                    ->getRowArray();
                
                if ($existingMaterial) {
                    $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                    return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                        'error' => 'Error file causing of duplicate',
                    ]));
                }
                
                $uploadBase = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads';
                $uploadDir  = $uploadBase . DIRECTORY_SEPARATOR . 'materials';
                if (!is_dir($uploadDir)) {
                    if (!@mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                        $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                        return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                            'error' => 'Cannot create upload directory.',
                        ]));
                    }
                }
                if (!is_writable($uploadDir)) {
                    $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                    return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                        'error' => 'Upload directory is not writable: ' . $uploadDir,
                    ]));
                }

                $newName = $file->getRandomName();
                try {
                    if (!$file->move($uploadDir, $newName)) {
                        $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                        return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                            'error' => 'Failed to move uploaded file.',
                        ]));
                    }
                } catch (\Throwable $e) {
                    $err = method_exists($file, 'getErrorString') ? $file->getErrorString() : '';
                    $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                    return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                        'error' => 'File move failed: ' . $e->getMessage() . ($err ? (' | ' . $err) : ''),
                    ]));
                }

                $data = [
                    'course_id' => (int)$course_id,
                    'file_name' => $file->getClientName(),
                    'file_path' => 'uploads/materials/' . $newName,
                    'created_at' => date('Y-m-d H:i:s'),
                    'status' => 'active',
                ];
                
                log_message('info', 'Attempting to insert material: {data}', ['data' => json_encode($data)]);

                try {
                    $builder = $db->table('materials');
                    $ok = $builder->insert($data);
                    $insertID = $db->insertID();
                    $affected = $db->affectedRows();
                    $dbErr = $db->error();
                    
                    log_message('info', 'DB insert result - OK: {ok}, InsertID: {id}, Affected: {aff}, Error: {err}', [
                        'ok' => $ok ? 'true' : 'false',
                        'id' => $insertID,
                        'aff' => $affected,
                        'err' => json_encode($dbErr)
                    ]);
                    
                    if (!$ok || $affected < 1) {
                        $msg = 'Failed to save material record. Affected: ' . $affected;
                        if (!empty($dbErr['message'])) {
                            $msg .= ' DB: ' . $dbErr['message'];
                        }
                        log_message('error', 'Materials insert failed: {msg}', ['msg' => $msg]);
                        $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                        return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                            'error' => $msg,
                        ]));
                    }
                    
                    log_message('info', 'Material inserted successfully with ID: {id}', ['id' => $insertID]);
                    
                    // Get course and file info for notifications
                    $courseTitle = $courseRow['title'] ?? 'Course';
                    $fileName = $file->getClientName();
                    
                    // ✅ Create notification for the admin/teacher who uploaded the material
                    try {
                        $notificationModel = new NotificationModel();
                        $uploaderId = $session->get('user_id');
                        $uploaderRole = strtolower($session->get('user_role') ?? '');
                        if ($uploaderId) {
                            $uploaderNotificationId = $notificationModel->createNotification(
                                (int)$uploaderId,
                                "You have successfully uploaded material '{$fileName}' for '{$courseTitle}'."
                            );
                            if ($uploaderNotificationId) {
                                log_message('info', "{$uploaderRole} notification created successfully for material upload. {$uploaderRole} ID: {$uploaderId}, Notification ID: {$uploaderNotificationId}");
                            } else {
                                log_message('warning', "{$uploaderRole} notification creation returned false for {$uploaderRole} ID: {$uploaderId}");
                            }
                        }
                    } catch (\Exception $uploaderNotifError) {
                        log_message('warning', 'Uploader notification creation failed: ' . $uploaderNotifError->getMessage());
                    }
                    
                    // ✅ Create notifications for all enrolled students
                    try {
                        $notificationModel = new NotificationModel();
                        $enrollmentModel = new EnrollmentModel();
                        $enrolledStudents = $enrollmentModel->getEnrollmentsByCourse($course_id);
                        
                        $notificationCount = 0;
                        foreach ($enrolledStudents as $enrollment) {
                            $studentId = isset($enrollment['user_id']) ? (int)$enrollment['user_id'] : null;
                            if ($studentId) {
                                $notificationId = $notificationModel->createNotification(
                                    $studentId,
                                    "New material '{$fileName}' has been uploaded for {$courseTitle}!"
                                );
                                if ($notificationId) {
                                    $notificationCount++;
                                }
                            }
                        }
                        log_message('info', "Created {$notificationCount} notifications for enrolled students in course {$course_id}");
                    } catch (\Exception $notifError) {
                        log_message('error', 'Student notification creation failed: ' . $notifError->getMessage());
                        log_message('error', 'Notification error trace: ' . $notifError->getTraceAsString());
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Materials upload DB error: {err}', ['err' => $e->getMessage()]);
                    $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                    return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                        'error' => 'Database error: ' . $e->getMessage(),
                    ]));
                }
                log_message('info', 'Material upload completed successfully');
                $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                log_message('info', 'Retrieved {count} materials for course {id}', ['count' => count($materials), 'id' => $course_id]);
                return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                    'success' => 'Material uploaded successfully. Insert ID: ' . ($insertID ?? 'unknown'),
                ]));
            }

            $upErr = $file ? ($file->getErrorString() . ' (code ' . $file->getError() . ')') : 'No file instance available';
            $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
            return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                'error' => 'Invalid file upload: ' . $upErr,
            ]));
            
            } catch (\Throwable $uploadEx) {
                log_message('error', 'FATAL upload error: {err}', ['err' => $uploadEx->getMessage() . ' | ' . $uploadEx->getTraceAsString()]);
                $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                return view('materials/upload', $this->prepareUploadViewData($course_id, $materials, [
                    'error' => 'FATAL ERROR: ' . $uploadEx->getMessage(),
                ]));
            }
        }

        // GET: show form and list existing materials
        $materials = $materialModel->getMaterialsByCourse($course_id);
        return view('materials/upload', $this->prepareUploadViewData($course_id, $materials));
    }

    public function delete($material_id)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $materialModel = new MaterialModel();
        $material = $materialModel->find($material_id);
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        // Soft delete: Set status to 'deleted' instead of deleting the file and record
        // The file is preserved so it can be restored later
        if ($materialModel->softDelete($material_id)) {
            $userRole = strtolower(session()->get('user_role') ?? '');
            $message = in_array($userRole, ['admin', 'teacher']) 
                ? 'Material deleted successfully. It can be restored from the deleted materials section below.'
                : 'Material deleted successfully. It can be restored by admin or teacher.';
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Failed to delete material.');
        }
    }

    public function restore($material_id)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        // Admins and teachers can restore materials
        $userRole = strtolower($session->get('user_role') ?? '');
        if (!in_array($userRole, ['admin', 'teacher'])) {
            return redirect()->back()->with('error', 'Only administrators and teachers can restore materials.');
        }

        $materialModel = new MaterialModel();
        $material = $materialModel->find($material_id);
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        // Check if material is actually deleted
        if (($material['status'] ?? 'active') !== 'deleted') {
            return redirect()->back()->with('error', 'Material is not deleted, so it cannot be restored.');
        }

        // Get course info for notification
        $db = \Config\Database::connect();
        $course = $db->table('courses')->where('id', $material['course_id'])->get()->getRowArray();
        $courseTitle = $course ? $course['title'] : 'Course';
        $fileName = $material['file_name'] ?? 'Material';

        // Restore: Set status back to 'active'
        if ($materialModel->restore($material_id)) {
            // ✅ Create notification for the admin/teacher who restored the material
            try {
                $notificationModel = new NotificationModel();
                $restorerId = $session->get('user_id');
                $restorerRole = strtolower($session->get('user_role') ?? '');
                if ($restorerId) {
                    $restorerNotificationId = $notificationModel->createNotification(
                        (int)$restorerId,
                        "You have successfully restored material '{$fileName}' for '{$courseTitle}'."
                    );
                    if ($restorerNotificationId) {
                        log_message('info', "{$restorerRole} notification created successfully for material restore. {$restorerRole} ID: {$restorerId}, Notification ID: {$restorerNotificationId}");
                    } else {
                        log_message('warning', "{$restorerRole} notification creation returned false for {$restorerRole} ID: {$restorerId}");
                    }
                }
            } catch (\Exception $restorerNotifError) {
                log_message('warning', 'Restorer notification creation failed: ' . $restorerNotifError->getMessage());
            }

            return redirect()->back()->with('success', 'Material restored successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to restore material.');
        }
    }

    public function download($material_id)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userId = (int) $session->get('user_id');

        $materialModel = new MaterialModel();
        $material = $materialModel->find($material_id);
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        // Prevent downloading deleted materials (unless admin)
        $userRole = strtolower($session->get('user_role') ?? '');
        if (($material['status'] ?? 'active') === 'deleted' && $userRole !== 'admin') {
            return redirect()->back()->with('error', 'This material has been deleted and is no longer available.');
        }

        // Access control: ensure the user is enrolled in the course (admins bypass this check)
        if ($userRole !== 'admin') {
            $enrollmentModel = new EnrollmentModel();
            $enrolled = $enrollmentModel
                ->where('user_id', $userId)
                ->where('course_id', (int)$material['course_id'])
                ->countAllResults() > 0;

            if (!$enrolled) {
                return redirect()->back()->with('error', 'You are not enrolled in this course.');
            }
        }

        $fullPath = WRITEPATH . $material['file_path'];
        if (!is_file($fullPath)) {
            return redirect()->back()->with('error', 'File not found on server.');
        }

        return $this->response->download($fullPath, null)->setFileName($material['file_name']);
    }

    public function listByCourse($course_id)
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userId = (int) $session->get('user_id');

        // Ensure the user is enrolled in this course
        $enrollmentModel = new EnrollmentModel();
        $enrolled = $enrollmentModel
            ->where('user_id', $userId)
            ->where('course_id', (int)$course_id)
            ->countAllResults() > 0;

        if (!$enrolled) {
            return redirect()->back()->with('error', 'You are not enrolled in this course.');
        }

        $materialModel = new MaterialModel();
        $materials = $materialModel->getMaterialsByCourse($course_id);

        return view('materials/list', [
            'materials' => $materials,
            'course_id' => $course_id,
        ]);
    }
}
