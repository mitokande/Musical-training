<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache cleared in FPM worker: ' . date('H:i:s');
} else {
    echo 'OPcache not available';
}
