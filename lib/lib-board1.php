<?php

function getPdo() {
    return core_BDClient::getInstance()->getDb();
}

function bootstrapApp() {
    static $boostrapped = false;
    if ($boostrapped) {
        return;
    }

    $boostrapped = true;
    $arrayConfig = getConfig();
    spl_autoload_register('autoloader');
    $connect = core_BDClient::getInstance($arrayConfig['dbName'], $arrayConfig['dbUser'], $arrayConfig['dbPass']);    
    $connect->getDb()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $QueryInfoObject = core_QueryInfo::getInstance();
    $QueryInfoObject->boolFlag = false;
}

function autoloader($class_name) {

    $basePathApp = dirname(dirname(__DIR__));
    $basePath = __DIR__;

    $path = explode('_', $class_name);
    $filePath = $basePath. '/' . implode("/", $path) . '.php';
    $filePathApp = $basePathApp . '/' . implode("/", $path) . '.php';

    if (file_exists($filePath)) {
        require_once $filePath;
        return;
    }

    require_once($filePathApp);
}

function boardTruncate()
{
    $pdo = getPdo();
    $pdo->exec("TRUNCATE post");
    $pdo->exec("TRUNCATE coments");
}

function boardCreatePost($generator, $fakeTime = null)
{
    $post = new core_Post();
    $post->title = '';
    $post->content = $generator->generateText();

    if (mt_rand(1, 100) < 60) {
        $post->title = $generator->generateTopic();
    }

    $post->time = $fakeTime ? $fakeTime : time();
    return $post; 
}

function boardCreateComment($generator, $postId, $fakeTime = null)
{
    $comment = new core_Comment();
    $comment->content = $generator->generateCommentText();
    $comment->post_id = $postId;
    $comment->time = $fakeTime ? $fakeTime : time();

    return $comment;
}

function boardInsertPost($pdo, $post)
{
    $model = new Application_Models_Post();
    $model->add($post);

    return $pdo->lastInsertId();
}

function boardInsertComment($pdo, $comment)
{
    $model = new Application_Models_Comment();
    $model->add($comment);

    return $pdo->lastInsertId();
}

function boardGetBaseUrl()
{
    return '/board/';
}

function boardRun()
{
    $route = new core_Router();
    $route->start();
}
