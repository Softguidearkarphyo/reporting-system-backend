<?php

namespace Database\Seeders\Task;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            [
                'cd' => '01',
                'eng_name' => 'Meeting',
                'jp_name' => '会議'
            ],
            [
                'cd' => '02',
                'eng_name' => 'Coding',
                'jp_name' => 'コーディング'
            ],
            [
                'cd' => '03',
                'eng_name' => 'Documentation',
                'jp_name' => 'ドキュメント'
            ],
            [
                'cd' => '04',
                'eng_name' => 'Support',
                'jp_name' => 'サポート'
            ],
            [
                'cd' => '05',
                'eng_name' => 'PCL',
                'jp_name' => 'PCL'
            ],
            [
                'cd' => '06',
                'eng_name' => 'Screen Capture',
                'jp_name' => '画面エビデンス'
            ],
            [
                'cd' => '07',
                'eng_name' => 'Change List',
                'jp_name' => '変更一覧'
            ],
            [
                'cd' => '08',
                'eng_name' => 'Read Documentation',
                'jp_name' => 'ドキュメントの勉強'
            ],
            [
                'cd' => '09',
                'eng_name' => 'Environment Setting',
                'jp_name' => '環境の設定'
            ],
            [
                'cd' => '10',
                'eng_name' => 'Source Learning',
                'jp_name' => 'ソースの勉強'
            ],
            [
                'cd' => '11',
                'eng_name' => 'Test',
                'jp_name' => 'テスト'
            ],
            [
                'cd' => '12',
                'eng_name' => 'Inquiry Countermeasure',
                'jp_name' => '問い合わせ対応'
            ],
            [
                'cd' => '13',
                'eng_name' => 'Research',
                'jp_name' => '調査'
            ],
            [
                'cd' => '17',
                'eng_name' => 'Learning',
                'jp_name' => '勉強'
            ],
            [
                'cd' => '18',
                'eng_name' => 'Backup Website',
                'jp_name' => 'ウェブサイトのバックアップ'
            ],
            [
                'cd' => '19',
                'eng_name' => 'Check Website',
                'jp_name' => 'ウェブサイトのチェック'
            ],
            [
                'cd' => '20',
                'eng_name' => 'Domain Hosting',
                'jp_name' => 'ドメインホスティング'
            ],
        ];

        Task::insert($tasks);
    }
}
