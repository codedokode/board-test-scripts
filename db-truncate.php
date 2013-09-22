<?php

require_once __DIR__ . '/lib/lib.php';
if (empty($argv[1]) || $argv[1] != 'yes') {
    die("Usage: script yes\ntruncates post & comment tables\n");
}

$pdo = getPdo();
$pdo->exec("TRUNCATE post");
$pdo->exec("TRUNCATE coments");
echo "All records truncated\n";