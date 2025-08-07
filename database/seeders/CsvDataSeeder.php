<?php

namespace Database\Seeders;

use App\Models\SkillSheet;
use App\Models\Staff;
use App\Models\StaffProject;
use App\Models\StaffResponsibility;
use App\Models\TechStackProficiency;
use Illuminate\Database\Seeder;

class CsvDataSeeder extends Seeder
{

    public function run(): void
    {
        $this->createStaffs();
        $this->createStaffProjects();
        $this->createStaffResponsibilities();
        $this->createSkillSheets();
        $this->createTechStackProficiencies();
    }

    public function createStaffs()
    {
        $csvData = $this->csvToData('csvData/staffs.csv');
        foreach ($csvData as $row) {
            Staff::create([
                'staff_no'        => $row['staff_no'],
                'eng_name'        => $row['eng_name'],
                'jp_name'         => $row['jp_name'],
                'username'        => $row['username'],
                'password'        => $row['password'],
                'address'         => $row['address'],
                'ph_number'       => $row['ph_number'],
                'position'        => $row['position'],
                'role'            => $row['role'],
                'email'           => $row['email'],
                'permanent_date'  => $row['permanent_date'],
                'ref_person'      => $row['ref_person'],
                'position'        => $row['position'],
                'sort_key'        => $row['sort_key'],
            ]);
        }
    }

    public function createStaffResponsibilities()
    {
        $csvData = $this->csvToData('csvData/staff_responsibilities.csv');

        foreach ($csvData as $row) {
            StaffResponsibility::create([
                'staff_id'            => $row['staff_id'],
                'responsibility_id'   => $row['responsibility_id'],
            ]);
        }
    }

    public function createStaffProjects()
    {
        $csvData = $this->csvToData('csvData/staff_projects.csv');

        foreach ($csvData as $row) {
            StaffProject::create([
                'staff_id'      => $row['staff_id'],
                'project_id'    => $row['project_id'],
            ]);
        }
    }

    public function createSkillSheets()
    {
        $csvData = $this->csvToData('csvData/skill_sheets.csv');

        foreach ($csvData as $row) {
            SkillSheet::create([
                'staff_id'            => $row['staff_id'],
                'position_id'         => $row['position_id'],
                'grade_id'            => $row['grade_id'],
                'join_date'           => $row['join_date'],
                'sg_experience'       => $row['sg_experience'],
                'prev_experience'     => $row['prev_experience'],
                'total_experience'    => $row['total_experience'],
                'japanese_level_id'   => $row['japanese_level_id'],
                'major_tech_stack_id' => $row['major_tech_stack_id'],

            ]);
        }
    }

    public function createTechStackProficiencies()
    {
        $csvData = $this->csvToData('csvData/tech_stack_proficiencies.csv');

        foreach ($csvData as $row) {
            TechStackProficiency::create([
                'staff_id'             => $row['staff_id'],
                'tech_stack_id'        => $row['tech_stack_id'],
                'proficiency_level_id' => $row['proficiency_level_id'],

            ]);
        }
    }

    private function csvToData($filename, $delimiter = ',')
    {
        $data = [];
        $filePath = database_path($filename);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return $data;
        }

        $header = null;

        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }
}
