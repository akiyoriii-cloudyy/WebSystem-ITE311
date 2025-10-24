<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\EnrollmentModel;

class Materials extends BaseController
{
    protected $helpers = ['form', 'url'];

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
                    'rules' => 'uploaded[file]|max_size[file,51200]|ext_in[file,pdf,ppt,pptx,doc,docx,xls,xlsx,csv,zip,rar,7z,txt,jpg,jpeg,png]'
                ]
            ];

            if (!$this->validate($rules)) {
                $errs = $this->validator ? $this->validator->getErrors() : [];
                $msg = !empty($errs) ? implode("\n", $errs) : 'Validation failed.';
                log_message('error', 'Materials upload validation failed: {msg}', ['msg' => $msg]);
                $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                return view('materials/upload', [
                    'course_id' => $course_id,
                    'materials' => $materials,
                    'error' => $msg,
                ]);
            }

            $file = $this->request->getFile('file');
            if ($file && $file->isValid()) {
                $uploadBase = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads';
                $uploadDir  = $uploadBase . DIRECTORY_SEPARATOR . 'materials';
                if (!is_dir($uploadDir)) {
                    if (!@mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                        $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                        return view('materials/upload', [
                            'course_id' => $course_id,
                            'materials' => $materials,
                            'error' => 'Cannot create upload directory.',
                        ]);
                    }
                }
                if (!is_writable($uploadDir)) {
                    $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                    return view('materials/upload', [
                        'course_id' => $course_id,
                        'materials' => $materials,
                        'error' => 'Upload directory is not writable: ' . $uploadDir,
                    ]);
                }

                $newName = $file->getRandomName();
                try {
                    if (!$file->move($uploadDir, $newName)) {
                        $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                        return view('materials/upload', [
                            'course_id' => $course_id,
                            'materials' => $materials,
                            'error' => 'Failed to move uploaded file.',
                        ]);
                    }
                } catch (\Throwable $e) {
                    $err = method_exists($file, 'getErrorString') ? $file->getErrorString() : '';
                    $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                    return view('materials/upload', [
                        'course_id' => $course_id,
                        'materials' => $materials,
                        'error' => 'File move failed: ' . $e->getMessage() . ($err ? (' | ' . $err) : ''),
                    ]);
                }

                $data = [
                    'course_id' => (int)$course_id,
                    'file_name' => $file->getClientName(),
                    'file_path' => 'uploads/materials/' . $newName,
                    'created_at' => date('Y-m-d H:i:s'),
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
                        return view('materials/upload', [
                            'course_id' => $course_id,
                            'materials' => $materials,
                            'error' => $msg,
                        ]);
                    }
                    
                    log_message('info', 'Material inserted successfully with ID: {id}', ['id' => $insertID]);
                } catch (\Throwable $e) {
                    log_message('error', 'Materials upload DB error: {err}', ['err' => $e->getMessage()]);
                    $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                    return view('materials/upload', [
                        'course_id' => $course_id,
                        'materials' => $materials,
                        'error' => 'Database error: ' . $e->getMessage(),
                    ]);
                }
                log_message('info', 'Material upload completed successfully');
                $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                log_message('info', 'Retrieved {count} materials for course {id}', ['count' => count($materials), 'id' => $course_id]);
                return view('materials/upload', [
                    'course_id' => $course_id,
                    'materials' => $materials,
                    'success' => 'Material uploaded successfully. Insert ID: ' . ($insertID ?? 'unknown'),
                ]);
            }

            $upErr = $file ? ($file->getErrorString() . ' (code ' . $file->getError() . ')') : 'No file instance available';
            $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
            return view('materials/upload', [
                'course_id' => $course_id,
                'materials' => $materials,
                'error' => 'Invalid file upload: ' . $upErr,
            ]);
            
            } catch (\Throwable $uploadEx) {
                log_message('error', 'FATAL upload error: {err}', ['err' => $uploadEx->getMessage() . ' | ' . $uploadEx->getTraceAsString()]);
                $materials = (new MaterialModel())->getMaterialsByCourse($course_id);
                return view('materials/upload', [
                    'course_id' => $course_id,
                    'materials' => $materials,
                    'error' => 'FATAL ERROR: ' . $uploadEx->getMessage(),
                ]);
            }
        }

        // GET: show form and list existing materials
        $materials = $materialModel->getMaterialsByCourse($course_id);
        return view('materials/upload', [
            'course_id' => $course_id,
            'materials' => $materials,
        ]);
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

        $fullPath = WRITEPATH . $material['file_path'];
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $materialModel->delete($material_id);
        return redirect()->back()->with('success', 'Material deleted successfully.');
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

        // Access control: ensure the user is enrolled in the course
        $enrollmentModel = new EnrollmentModel();
        $enrolled = $enrollmentModel
            ->where('user_id', $userId)
            ->where('course_id', (int)$material['course_id'])
            ->countAllResults() > 0;

        if (!$enrolled) {
            return redirect()->back()->with('error', 'You are not enrolled in this course.');
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
