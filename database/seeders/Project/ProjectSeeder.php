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
                'cd'        => 'MIRAI-PRJ0001',
                'eng_name'  => 'Mirai Project',
                'jp_name'   => '未来プロジェクト',
            ],
            [
                'cd'        => 'KENJA-PRJ0001',
                'eng_name'  => 'Kenja Project',
                'jp_name'   => '賢者プロジェクト',
            ],
            [
                'cd'        => 'DB-MAINTENANCE-TOOL0001',
                'eng_name'  => 'Kenja Database Maintenance Project',
                'jp_name'   => '賢者データベースメンテナンスツールプロジェクト',
            ],
        ];

        Project::insert($projects);
    }
}
