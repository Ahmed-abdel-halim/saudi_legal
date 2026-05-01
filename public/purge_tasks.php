<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    \App\Models\LegalTask::truncate();
    \App\Models\ClientQuestion::truncate();
    \App\Models\AiTask::truncate();
    \DB::table('ai_responses_v2')->truncate();
    \DB::table('ai_task_tag')->truncate();
    \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    echo "<h1>تم مسح المهام القديمة الفاسدة بنجاح!</h1>";
    echo "<p>قاعدة البيانات الآن نظيفة وجاهزة. يمكنك العودة ولوحة التحكم ورفع ملف الـ CSV الجديد.</p>";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
