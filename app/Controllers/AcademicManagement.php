<?php

namespace App\Controllers;

use App\Models\AcadYearModel;
use App\Models\SemesterModel;
use App\Models\TermModel;
use App\Models\DepartmentModel;
use App\Models\ProgramModel;
use App\Models\NotificationModel;

class AcademicManagement extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ==================== ACADEMIC YEARS ====================
    public function acadYears()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $acadYearModel = new AcadYearModel();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            
            if ($action === 'create') {
                $data = [
                    'acad_year' => $this->request->getPost('acad_year') ?? $this->request->getPost('year'),
                    'start_date' => $this->request->getPost('start_date'),
                    'end_date' => $this->request->getPost('end_date'),
                    'is_active' => $this->request->getPost('is_active') ?? $this->request->getPost('is_current') ?? 0,
                ];

                if ($acadYearModel->save($data)) {
                    // ✅ Create notification for admin
                    try {
                        $notificationModel = new NotificationModel();
                        $adminId = $session->get('user_id');
                        $acadYear = $data['acad_year'] ?? 'Academic Year';
                        $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully created Academic Year '{$acadYear}'."
                        );
                    } catch (\Exception $notifError) {
                        log_message('warning', 'Notification creation failed for academic year: ' . $notifError->getMessage());
                    }
                    
                    return redirect()->back()->with('success', 'Academic Year created successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $acadYearModel->errors());
                }
            } elseif ($action === 'update') {
                $id = $this->request->getPost('id');
                $data = [
                    'acad_year' => $this->request->getPost('acad_year') ?? $this->request->getPost('year'),
                    'start_date' => $this->request->getPost('start_date'),
                    'end_date' => $this->request->getPost('end_date'),
                    'is_active' => $this->request->getPost('is_active') ?? $this->request->getPost('is_current') ?? 0,
                ];

                if ($acadYearModel->update($id, $data)) {
                    return redirect()->back()->with('success', 'Academic Year updated successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $acadYearModel->errors());
                }
            } elseif ($action === 'delete') {
                $id = $this->request->getPost('id');
                if ($acadYearModel->delete($id)) {
                    return redirect()->back()->with('success', 'Academic Year deleted successfully!');
                }
            }
        }

        $data = [
            'title' => 'Academic Years Management',
            'acad_years' => $acadYearModel->getAllAcadYears(),
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('admin/academic/acad_years', $data);
    }

    // ==================== SEMESTERS ====================
    public function semesters()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $semesterModel = new SemesterModel();
        $acadYearModel = new AcadYearModel();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            
            if ($action === 'create') {
                $data = [
                    'acad_year_id' => $this->request->getPost('acad_year_id'),
                    'semester' => $this->request->getPost('semester'),
                    'semester_code' => $this->request->getPost('semester_code'),
                    'start_date' => $this->request->getPost('start_date'),
                    'end_date' => $this->request->getPost('end_date'),
                    'is_active' => $this->request->getPost('is_active') ?? 0,
                ];

                if ($semesterModel->save($data)) {
                    // ✅ Create notification for admin
                    try {
                        $notificationModel = new NotificationModel();
                        $adminId = $session->get('user_id');
                        $semester = $data['semester'] ?? 'Semester';
                        $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully created Semester '{$semester}'."
                        );
                    } catch (\Exception $notifError) {
                        log_message('warning', 'Notification creation failed for semester: ' . $notifError->getMessage());
                    }
                    
                    return redirect()->back()->with('success', 'Semester created successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $semesterModel->errors());
                }
            } elseif ($action === 'update') {
                $id = $this->request->getPost('id');
                $data = [
                    'acad_year_id' => $this->request->getPost('acad_year_id'),
                    'semester' => $this->request->getPost('semester'),
                    'semester_code' => $this->request->getPost('semester_code'),
                    'start_date' => $this->request->getPost('start_date'),
                    'end_date' => $this->request->getPost('end_date'),
                    'is_active' => $this->request->getPost('is_active') ?? 0,
                ];

                if ($semesterModel->update($id, $data)) {
                    return redirect()->back()->with('success', 'Semester updated successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $semesterModel->errors());
                }
            } elseif ($action === 'delete') {
                $id = $this->request->getPost('id');
                if ($semesterModel->delete($id)) {
                    return redirect()->back()->with('success', 'Semester deleted successfully!');
                }
            }
        }

        $data = [
            'title' => 'Semesters Management',
            'semesters' => $semesterModel->select('semesters.*, acad_years.acad_year')
                                         ->join('acad_years', 'acad_years.id = semesters.acad_year_id')
                                         ->orderBy('acad_years.acad_year', 'DESC')
                                         ->orderBy('semesters.start_date', 'ASC')
                                         ->findAll(),
            'acad_years' => $acadYearModel->getAllAcadYears(),
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('admin/academic/semesters', $data);
    }

    // ==================== TERMS ====================
    public function terms()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $termModel = new TermModel();
        $semesterModel = new SemesterModel();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            
            if ($action === 'create') {
                $data = [
                    'semester_id' => $this->request->getPost('semester_id'),
                    'term' => $this->request->getPost('term'),
                    'term_code' => $this->request->getPost('term_code'),
                    'start_date' => $this->request->getPost('start_date'),
                    'end_date' => $this->request->getPost('end_date'),
                    'is_active' => $this->request->getPost('is_active') ?? 0,
                ];

                if ($termModel->save($data)) {
                    // ✅ Create notification for admin
                    try {
                        $notificationModel = new NotificationModel();
                        $adminId = $session->get('user_id');
                        $term = $data['term'] ?? 'Term';
                        $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully created Term '{$term}'."
                        );
                    } catch (\Exception $notifError) {
                        log_message('warning', 'Notification creation failed for term: ' . $notifError->getMessage());
                    }
                    
                    return redirect()->back()->with('success', 'Term created successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $termModel->errors());
                }
            } elseif ($action === 'update') {
                $id = $this->request->getPost('id');
                $data = [
                    'semester_id' => $this->request->getPost('semester_id'),
                    'term' => $this->request->getPost('term'),
                    'term_code' => $this->request->getPost('term_code'),
                    'start_date' => $this->request->getPost('start_date'),
                    'end_date' => $this->request->getPost('end_date'),
                    'is_active' => $this->request->getPost('is_active') ?? 0,
                ];

                if ($termModel->update($id, $data)) {
                    return redirect()->back()->with('success', 'Term updated successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $termModel->errors());
                }
            } elseif ($action === 'delete') {
                $id = $this->request->getPost('id');
                if ($termModel->delete($id)) {
                    return redirect()->back()->with('success', 'Term deleted successfully!');
                }
            }
        }

        $data = [
            'title' => 'Terms Management',
            'terms' => $termModel->select('terms.*, semesters.semester, semesters.semester_code, acad_years.acad_year')
                                 ->join('semesters', 'semesters.id = terms.semester_id')
                                 ->join('acad_years', 'acad_years.id = semesters.acad_year_id')
                                 ->orderBy('acad_years.acad_year', 'DESC')
                                 ->orderBy('terms.start_date', 'ASC')
                                 ->findAll(),
            'semesters' => $semesterModel->select('semesters.*, acad_years.acad_year')
                                         ->join('acad_years', 'acad_years.id = semesters.acad_year_id')
                                         ->orderBy('acad_years.acad_year', 'DESC')
                                         ->findAll(),
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('admin/academic/terms', $data);
    }

    // ==================== DEPARTMENTS ====================
    public function departments()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $departmentModel = new DepartmentModel();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            
            if ($action === 'create') {
                $data = [
                    'department_code' => $this->request->getPost('department_code'),
                    'department_name' => $this->request->getPost('department_name'),
                    'description' => $this->request->getPost('description'),
                    'is_active' => $this->request->getPost('is_active') ?? 1,
                ];

                if ($departmentModel->save($data)) {
                    // ✅ Create notification for admin
                    try {
                        $notificationModel = new NotificationModel();
                        $adminId = $session->get('user_id');
                        $deptName = $data['department_name'] ?? 'Department';
                        $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully created Department '{$deptName}'."
                        );
                    } catch (\Exception $notifError) {
                        log_message('warning', 'Notification creation failed for department: ' . $notifError->getMessage());
                    }
                    
                    return redirect()->back()->with('success', 'Department created successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $departmentModel->errors());
                }
            } elseif ($action === 'update') {
                $id = $this->request->getPost('id');
                $data = [
                    'department_code' => $this->request->getPost('department_code'),
                    'department_name' => $this->request->getPost('department_name'),
                    'description' => $this->request->getPost('description'),
                    'is_active' => $this->request->getPost('is_active') ?? 1,
                ];

                if ($departmentModel->update($id, $data)) {
                    return redirect()->back()->with('success', 'Department updated successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $departmentModel->errors());
                }
            } elseif ($action === 'delete') {
                $id = $this->request->getPost('id');
                if ($departmentModel->delete($id)) {
                    return redirect()->back()->with('success', 'Department deleted successfully!');
                }
            }
        }

        $data = [
            'title' => 'Departments Management',
            'departments' => $departmentModel->orderBy('department_name', 'ASC')->findAll(),
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('admin/academic/departments', $data);
    }

    // ==================== PROGRAMS ====================
    public function programs()
    {
        $session = session();
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $programModel = new ProgramModel();
        $departmentModel = new DepartmentModel();

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            
            if ($action === 'create') {
                $data = [
                    'department_id' => $this->request->getPost('department_id'),
                    'program_code' => $this->request->getPost('program_code'),
                    'program_name' => $this->request->getPost('program_name'),
                    'description' => $this->request->getPost('description'),
                    'is_active' => $this->request->getPost('is_active') ?? 1,
                ];

                if ($programModel->save($data)) {
                    // ✅ Create notification for admin
                    try {
                        $notificationModel = new NotificationModel();
                        $adminId = $session->get('user_id');
                        $programName = $data['program_name'] ?? 'Program';
                        $notificationModel->createNotification(
                            (int)$adminId,
                            "You have successfully created Program '{$programName}'."
                        );
                    } catch (\Exception $notifError) {
                        log_message('warning', 'Notification creation failed for program: ' . $notifError->getMessage());
                    }
                    
                    return redirect()->back()->with('success', 'Program created successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $programModel->errors());
                }
            } elseif ($action === 'update') {
                $id = $this->request->getPost('id');
                $data = [
                    'department_id' => $this->request->getPost('department_id'),
                    'program_code' => $this->request->getPost('program_code'),
                    'program_name' => $this->request->getPost('program_name'),
                    'description' => $this->request->getPost('description'),
                    'is_active' => $this->request->getPost('is_active') ?? 1,
                ];

                if ($programModel->update($id, $data)) {
                    return redirect()->back()->with('success', 'Program updated successfully!');
                } else {
                    return redirect()->back()->withInput()->with('errors', $programModel->errors());
                }
            } elseif ($action === 'delete') {
                $id = $this->request->getPost('id');
                if ($programModel->delete($id)) {
                    return redirect()->back()->with('success', 'Program deleted successfully!');
                }
            }
        }

        $data = [
            'title' => 'Programs Management',
            'programs' => $programModel->select('programs.*, departments.department_name, departments.department_code')
                                       ->join('departments', 'departments.id = programs.department_id')
                                       ->orderBy('departments.department_name', 'ASC')
                                       ->orderBy('programs.program_name', 'ASC')
                                       ->findAll(),
            'departments' => $departmentModel->getActiveDepartments(),
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('admin/academic/programs', $data);
    }

    // AJAX endpoint to get programs by department
    public function getProgramsByDepartment()
    {
        $departmentId = $this->request->getPost('department_id');
        $programModel = new ProgramModel();
        $programs = $programModel->getProgramsByDepartment($departmentId);
        
        return $this->response->setJSON($programs);
    }
}
