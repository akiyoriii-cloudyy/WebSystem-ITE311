<?php

namespace App\Models;

use CodeIgniter\Model;

class GradeModel extends Model
{
    protected $table            = 'grades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['enrollment_id', 'assignment_id', 'score', 'percentage', 'remarks', 'graded_by', 'graded_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [
        'enrollment_id' => 'required|integer',
        'assignment_id' => 'required|integer',
        'score'         => 'permit_empty|decimal',
        'percentage'    => 'permit_empty|decimal',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['calculateFinalGrade'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getGradesByEnrollment($enrollmentId)
    {
        return $this->select('grades.*, assignments.title, assignments.assignment_type, assignments.max_score')
                    ->join('assignments', 'assignments.id = grades.assignment_id')
                    ->where('grades.enrollment_id', $enrollmentId)
                    ->findAll();
    }

    public function getGradeByAssignment($enrollmentId, $assignmentId)
    {
        return $this->where('enrollment_id', $enrollmentId)
                    ->where('assignment_id', $assignmentId)
                    ->first();
    }

    protected function calculateFinalGrade($data)
    {
        if (isset($data['id'])) {
            $enrollmentId = $data['enrollment_id'] ?? $this->find($data['id'])['enrollment_id'];
            $this->updateEnrollmentFinalGrade($enrollmentId);
        }
        return $data;
    }

    public function updateEnrollmentFinalGrade($enrollmentId)
    {
        $enrollmentModel = new EnrollmentModel();
        $gradingWeightModel = new GradingWeightModel();
        
        // Get enrollment with course info
        $enrollment = $enrollmentModel->select('enrollments.*, courses.id as course_id')
                                       ->join('courses', 'courses.id = enrollments.course_id')
                                       ->where('enrollments.id', $enrollmentId)
                                       ->first();
        
        if (!$enrollment) return;

        // Get all grades for this enrollment
        $grades = $this->getGradesByEnrollment($enrollmentId);
        
        // Get grading weights for the course
        $weights = $gradingWeightModel->where('course_id', $enrollment['course_id'])->findAll();
        
        $totalWeightedScore = 0;
        $totalWeight = 0;
        
        foreach ($grades as $grade) {
            if (isset($grade['percentage']) && $grade['percentage'] !== null && $grade['percentage'] >= 0) {
                // Find weight for this assignment type
                $weight = 0;
                foreach ($weights as $w) {
                    if (isset($w['assignment_type']) && isset($grade['assignment_type']) && 
                        $w['assignment_type'] === $grade['assignment_type']) {
                        $weight = floatval($w['weight_percentage'] ?? 0);
                        break;
                    }
                }
                
                // If no weight found, use equal weight distribution
                if ($weight == 0 && count($weights) == 0) {
                    $weight = 100 / max(count($grades), 1); // Equal distribution
                }
                
                if ($weight > 0) {
                    $totalWeightedScore += (floatval($grade['percentage']) * $weight / 100);
                    $totalWeight += $weight;
                }
            }
        }
        
        // Calculate final grade
        $finalGrade = 0;
        if ($totalWeight > 0) {
            $finalGrade = ($totalWeightedScore / $totalWeight) * 100;
        } elseif (count($grades) > 0) {
            // If no weights, calculate simple average
            $totalPercentage = 0;
            $count = 0;
            foreach ($grades as $grade) {
                if (isset($grade['percentage']) && $grade['percentage'] !== null && $grade['percentage'] >= 0) {
                    $totalPercentage += floatval($grade['percentage']);
                    $count++;
                }
            }
            $finalGrade = $count > 0 ? ($totalPercentage / $count) : 0;
        }
        
        // Update enrollment
        $updateData = ['final_grade' => round($finalGrade, 2)];
        
        // Auto-update completion status
        if ($finalGrade >= 75) {
            $updateData['completion_status'] = 'COMPLETED';
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($finalGrade < 75 && $finalGrade > 0) {
            $updateData['completion_status'] = 'FAILED';
        }
        
        $enrollmentModel->update($enrollmentId, $updateData);
    }
}
