<?php
// Forces an ad render to download (attachment) rather than play inline.
//
// ?v= selects which one. The value is only ever used as a key into the allowlist
// below and is never concatenated into a path, so there is nothing to traverse.
// Omitting it keeps the original ear-test link working.
$files = [
    'eartest'   => 'harmoniva-eartest-40s.mp4',
    'teachers'  => 'harmoniva-teachers-40s.mp4',
    'challenge' => 'harmoniva-challenge-15s.mp4',
    'challenge2' => 'harmoniva-challenge2-15s.mp4',
    'mascots'   => 'harmoniva-mascots-24s.mp4',
    'mascots-tr' => 'harmoniva-mascots-tr-29s.mp4',
];

$key = isset($_GET['v']) ? (string) $_GET['v'] : 'eartest';
if (!isset($files[$key])) { http_response_code(404); exit('not found'); }

$name = $files[$key];
$file = __DIR__ . '/' . $name;
if (!is_file($file)) { http_response_code(404); exit('not found'); }

header('Content-Type: video/mp4');
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
readfile($file);
