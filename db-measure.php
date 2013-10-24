<?php

require_once __DIR__ . '/lib/lib.php';

$count = isset($argv[1]) ? $argv[1] : 0;
$url = boardGetBaseUrl(); 

if (empty($count)) {
    die("Usage: script count\nPulls URL and measures response times\n");
}

measureTimes($count, function () use ($url) {

    $get = array(
        'p'     =>  mt_rand(1, 50)
    );
    $post = array(
        'tread_id'  =>  mt_rand(1, 100)
    );

    setupRequest($url, $get, $post);    
    $html = captureOutput(function () {
        boardRun();
    });

    if (strlen($html) < 100) {
        echo "Short HTML\n";
        var_dump($html);
    }
});

printMemoryUsage();
