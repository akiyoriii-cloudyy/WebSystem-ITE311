<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartmentProgramSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Departments (Colleges)
        $departments = [
            [
                'department_code' => 'CAS',
                'department_name' => 'College of Arts & Sciences',
                'description' => 'Offers BA in Communication, Psychology, Social Work, BS in Biology, Environmental Science, Math, etc.',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'department_code' => 'CBE',
                'department_name' => 'College of Business Education',
                'description' => 'Focuses on business-related courses',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'department_code' => 'CET',
                'department_name' => 'College of Engineering & Technology',
                'description' => 'Provides engineering and tech programs',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'department_code' => 'CCJ',
                'department_name' => 'College of Criminal Justice',
                'description' => 'For aspiring criminal justice professionals',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'department_code' => 'CTE',
                'department_name' => 'College of Teacher Education',
                'description' => 'For future educators',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'department_code' => 'COAHS',
                'department_name' => 'College of Allied Health Sciences',
                'description' => 'Includes Midwifery, Pharmacy, etc., aiming for CHED compliance',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'department_code' => 'SHS',
                'department_name' => 'Senior High School',
                'description' => 'Offers secondary education',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'department_code' => 'GSP',
                'department_name' => 'Graduate School',
                'description' => 'For advanced studies',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert departments and get their IDs
        $departmentIds = [];
        foreach ($departments as $dept) {
            // Check if department already exists
            $existing = $db->table('departments')
                ->where('department_code', $dept['department_code'])
                ->get()
                ->getRowArray();
            
            if (!$existing) {
                $db->table('departments')->insert($dept);
                $departmentIds[$dept['department_code']] = $db->insertID();
            } else {
                $departmentIds[$dept['department_code']] = $existing['id'];
            }
        }

        // Programs for each department
        $programs = [
            // CAS Programs
            'CAS' => [
                ['program_code' => 'BACOMM', 'program_name' => 'Bachelor of Arts in Communication'],
                ['program_code' => 'BAPSYC', 'program_name' => 'Bachelor of Arts in Psychology'],
                ['program_code' => 'BASW', 'program_name' => 'Bachelor of Arts in Social Work'],
                ['program_code' => 'BSBIO', 'program_name' => 'Bachelor of Science in Biology'],
                ['program_code' => 'BSES', 'program_name' => 'Bachelor of Science in Environmental Science'],
                ['program_code' => 'BSMATH', 'program_name' => 'Bachelor of Science in Mathematics'],
            ],
            // CBE Programs
            'CBE' => [
                ['program_code' => 'BSBA', 'program_name' => 'Bachelor of Science in Business Administration'],
                ['program_code' => 'BSA', 'program_name' => 'Bachelor of Science in Accountancy'],
                ['program_code' => 'BSHM', 'program_name' => 'Bachelor of Science in Hospitality Management'],
                ['program_code' => 'BSITM', 'program_name' => 'Bachelor of Science in Information Technology Management'],
            ],
            // CET Programs
            'CET' => [
                ['program_code' => 'BSIT', 'program_name' => 'Bachelor of Science in Information Technology'],
                ['program_code' => 'BSCS', 'program_name' => 'Bachelor of Science in Computer Science'],
                ['program_code' => 'BSCE', 'program_name' => 'Bachelor of Science in Civil Engineering'],
                ['program_code' => 'BSEE', 'program_name' => 'Bachelor of Science in Electrical Engineering'],
                ['program_code' => 'BSME', 'program_name' => 'Bachelor of Science in Mechanical Engineering'],
            ],
            // CCJ Programs
            'CCJ' => [
                ['program_code' => 'BSCRIM', 'program_name' => 'Bachelor of Science in Criminology'],
                ['program_code' => 'BSCJ', 'program_name' => 'Bachelor of Science in Criminal Justice'],
            ],
            // CTE Programs
            'CTE' => [
                ['program_code' => 'BSE', 'program_name' => 'Bachelor of Science in Education'],
                ['program_code' => 'BSEED', 'program_name' => 'Bachelor of Science in Elementary Education'],
                ['program_code' => 'BSEED-SEC', 'program_name' => 'Bachelor of Science in Secondary Education'],
            ],
            // COAHS Programs
            'COAHS' => [
                ['program_code' => 'BSMID', 'program_name' => 'Bachelor of Science in Midwifery'],
                ['program_code' => 'BSPHARM', 'program_name' => 'Bachelor of Science in Pharmacy'],
                ['program_code' => 'BSN', 'program_name' => 'Bachelor of Science in Nursing'],
            ],
            // SHS Programs
            'SHS' => [
                ['program_code' => 'SHS-STEM', 'program_name' => 'Senior High School - Science, Technology, Engineering, and Mathematics'],
                ['program_code' => 'SHS-ABM', 'program_name' => 'Senior High School - Accountancy, Business and Management'],
                ['program_code' => 'SHS-HUMSS', 'program_name' => 'Senior High School - Humanities and Social Sciences'],
                ['program_code' => 'SHS-TVL', 'program_name' => 'Senior High School - Technical-Vocational-Livelihood'],
            ],
            // GSP Programs
            'GSP' => [
                ['program_code' => 'MA', 'program_name' => 'Master of Arts'],
                ['program_code' => 'MS', 'program_name' => 'Master of Science'],
                ['program_code' => 'PhD', 'program_name' => 'Doctor of Philosophy'],
            ],
        ];

        // Insert programs
        foreach ($programs as $deptCode => $deptPrograms) {
            if (!isset($departmentIds[$deptCode])) {
                continue;
            }
            
            $deptId = $departmentIds[$deptCode];
            
            foreach ($deptPrograms as $prog) {
                // Check if program already exists
                $existing = $db->table('programs')
                    ->where('department_id', $deptId)
                    ->where('program_code', $prog['program_code'])
                    ->get()
                    ->getRowArray();
                
                if (!$existing) {
                    $db->table('programs')->insert([
                        'department_id' => $deptId,
                        'program_code' => $prog['program_code'],
                        'program_name' => $prog['program_name'],
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        
        echo "Departments and Programs seeded successfully!\n";
    }
}

