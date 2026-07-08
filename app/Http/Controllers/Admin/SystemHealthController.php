<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    public function index()
    {
        $system = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'server_time' => now()->format('Y-m-d H:i:s'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];

        // Database connectivity + key table counts
        $dbOk = true;
        $tableCounts = [];
        try {
            foreach ([
                'users', 'user_practices', 'exercise_sessions', 'ai_coaching_sessions',
                'game_scores', 'feed_items', 'follows', 'articles', 'messages', 'activity_log',
            ] as $table) {
                $tableCounts[$table] = DB::table($table)->count();
            }
        } catch (\Throwable $e) {
            $dbOk = false;
        }

        // Queue health
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $oldestPendingJob = DB::table('jobs')->orderBy('created_at')->value('created_at');

        // Storage
        $diskFree = @disk_free_space(base_path());
        $diskTotal = @disk_total_space(base_path());
        $logPath = storage_path('logs/laravel.log');
        $logSize = is_file($logPath) ? filesize($logPath) : 0;

        $storage = [
            'disk_free' => $diskFree ? $this->formatBytes($diskFree) : 'n/a',
            'disk_total' => $diskTotal ? $this->formatBytes($diskTotal) : 'n/a',
            'disk_used_pct' => ($diskFree && $diskTotal) ? round(($diskTotal - $diskFree) / $diskTotal * 100, 1) : null,
            'log_size' => $this->formatBytes($logSize),
        ];

        $logErrors = $this->recentLogErrors($logPath);

        return view('admin.system-health.index', compact(
            'system', 'dbOk', 'tableCounts', 'pendingJobs', 'failedJobs',
            'oldestPendingJob', 'storage', 'logErrors'
        ));
    }

    /**
     * Parse the tail of laravel.log and group ERROR lines by normalized message.
     */
    private function recentLogErrors(string $path, int $tailBytes = 1048576): array
    {
        if (! is_file($path)) {
            return [];
        }

        $size = filesize($path);
        $handle = fopen($path, 'r');
        if ($size > $tailBytes) {
            fseek($handle, -$tailBytes, SEEK_END);
            fgets($handle); // discard partial line
        }

        $groups = [];
        while (($line = fgets($handle)) !== false) {
            if (! preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(ERROR|CRITICAL|ALERT|EMERGENCY): (.*)$/', $line, $m)) {
                continue;
            }
            [, $timestamp, $level, $message] = $m;
            // Strip the JSON context so identical errors group together
            $message = preg_replace('/\s*\{"(?:userId|exception)".*$/', '', $message);
            $message = mb_substr(trim($message), 0, 200);
            $key = $level.'|'.$message;

            if (! isset($groups[$key])) {
                $groups[$key] = ['level' => $level, 'message' => $message, 'count' => 0, 'last_seen' => $timestamp];
            }
            $groups[$key]['count']++;
            $groups[$key]['last_seen'] = $timestamp;
        }
        fclose($handle);

        usort($groups, fn ($a, $b) => strcmp($b['last_seen'], $a['last_seen']));

        return array_slice($groups, 0, 25);
    }

    private function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
