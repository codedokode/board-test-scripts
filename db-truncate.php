<?php

require_once __DIR__ . '/lib/lib.php';
if (empty($argv[1]) || $argv[1] != 'yes') {
    die("Usage: script yes\ntruncates post & comment tables\n");
}

boardTruncate();
echo "All records truncated\n";