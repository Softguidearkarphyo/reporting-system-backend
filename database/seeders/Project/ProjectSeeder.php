<?php

namespace Database\Seeders\Project;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'code' => 'MIRAI-PRJ0001',
                'eng_name' => 'Mirai Project',
                'jp_name' => '未来プロジェクト',
            ],
            [
                'code' => 'KENJA-PRJ0001',
                'eng_name' => 'Kenja Project',
                'jp_name' => '賢者プロジェクト',
            ],
            [
                'code' => 'DB-MAINTENANCE-TOOL0001',
                'eng_name' => 'Kenja Database Maintenance Project',
                'jp_name' => '賢者データベースメンテナンスツールプロジェクト',
            ],
        ];

        foreach ($projects as $project) {
            $project['created_at'] = now();
            $project['updated_at'] = now();
            Project::create($project);
        }
    }
}
