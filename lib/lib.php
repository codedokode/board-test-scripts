<?php

error_reporting(-1);
mb_internal_encoding('utf-8');
ini_set('memory_limit', '256M');
require_once __DIR__.'/lib-melanchthon.php';

spl_autoload_register('tsAutoloader');

bootstrapApp();

function tsAutoloader($class_name) {

    $basePath = __DIR__;
    $path = explode('_', $class_name);
    $filePath = $basePath. '/' . implode("/", $path) . '.php';

    if (file_exists($filePath)) {
        require_once $filePath;
    }
}


function getAppDir() {
    return dirname(dirname(__DIR__));
}

function getConfig() {
    static $config;
    if (!$config) {
        $config = array();
        require_once __DIR__ . '/config.php';
    }

    return $config;
}

function printStats($time, $count, $action = 'Operation') {
    printf("%s took %.3f ms/%d items, %.2f items/sec avg\n",
        $action,
        $time * 1000,
        $count,
        $count / $time
    );
}

function setupRequest($url, $get = array(), $post = array()) {
    chdir(getAppDir());
    $_SERVER['REQUEST_URI'] = $url;
    $_GET = $get;
    $_POST = $post;
}

function measureTimes($count, $fn) {
    $min = 0;
    $max = 0;
    $sum = 0;

    for ($i = 0; $i < $count; $i++) {
        $t = microtime(true);
        $fn();
        $t2 = microtime(true);
        $time = $t2 - $t;

        if ($i == 0) {
            $min = $time;
        } else {
            $min = min($min, $time);
        }

        $max = max($max, $time);
        $sum += $time;
    }

    printf("Took %.3f ms, %d items, avg %.3f ms, min %.3f ms, max %.3f ms\n",
        $sum * 1000,
        $count,
        $sum * 1000 / $count,
        $min * 1000,
        $max * 1000
    );
}

function printMemoryUsage() {
    printf("Took %.3f Mb (peak), %.3f Mb (current) memory\n", 
        memory_get_peak_usage(true) / 1024 / 1024,
        memory_get_usage(true) / 1024 / 1024
    );
}

function captureOutput($fn) {
    ob_start();
    try {
        $fn();
        return ob_get_clean();
    } catch (Exception $e) {
        ob_end_clean();
        throw $e;
    }
}