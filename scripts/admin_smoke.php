<?php

use App\Models\ContentCategory;
use App\Models\CrmTask;
use App\Models\ExerciseCategory;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Geçici duman testi: tüm parametresiz admin GET rotalarını admin kullanıcısıyla çağırır.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$app->instance('request', Request::create('/'));
$kernel->bootstrap();

$admin = User::where('role', 'admin')->first();
if (! $admin) {
    echo "Admin yok\n";
    exit(1);
}

$routes = [];
foreach (app('router')->getRoutes() as $route) {
    $uri = $route->uri();
    if (str_starts_with($uri, 'admin') && in_array('GET', $route->methods()) && ! str_contains($uri, '{')) {
        $routes[] = '/'.$uri;
    }
}
// Parametreli önemli sayfalar (mevcut kayıtlarla)
$routes[] = '/admin/users/'.$admin->id;
$routes[] = '/admin/users/'.$admin->id.'/edit';
if ($cat = ExerciseCategory::first()) {
    $routes[] = '/admin/exercise-categories/'.$cat->id.'/edit';
    $routes[] = '/admin/exercise-categories/'.($cat->slug ?? $cat->id);
}
if ($cc = ContentCategory::first()) {
    $routes[] = '/admin/content-categories/'.$cc->id.'/edit';
}
if ($t = CrmTask::first()) {
    $routes[] = '/admin/tasks/'.$t->id;
    $routes[] = '/admin/tasks/'.$t->id.'/edit';
}
if ($m = Message::first()) {
    $routes[] = '/admin/messages/'.$m->id;
}

$fail = 0;
foreach (array_unique($routes) as $uri) {
    $request = Request::create($uri, 'GET');
    app('auth')->guard('web')->setUser($admin);
    app('auth')->shouldUse('web');
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
    } catch (Throwable $e) {
        $status = 'EXC: '.substr($e->getMessage(), 0, 120);
    }
    $flag = (is_int($status) && $status < 400) ? 'OK ' : 'FAIL';
    if ($flag === 'FAIL') {
        $fail++;
    }
    printf("%-4s %-55s %s\n", $flag, $uri, $status);
}
exit($fail ? 1 : 0);
