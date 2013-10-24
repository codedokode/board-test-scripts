<?php

// Board-specific functions
function getPdo() {
    return Core_DbConnection::getInstance();
}

function bootstrapApp() {
    static $boostrapped = false;
    if ($boostrapped) {
        return;
    }

    set_include_path(implode(PATH_SEPARATOR, array(
        get_include_path(),
        getAppDir()
    )));

    chdir(getAppDir());
    require_once('Application/Core/Autoload.php');
    spl_autoload_register(array('Core_Autoload', 'loadClass'));

    $boostrapped = true;
}

function boardTruncate()
{
    $pdo = getPdo();
    $pdo->exec("TRUNCATE comment");
    $pdo->exec("TRUNCATE post");
}

function boardCreatePost($generator, $fakeTime = null)
{
    $post = new Core_Post();
    $post->title = '';
    $post->content = $generator->generateText();

    if (mt_rand(1, 100) < 60) {
        $post->title = $generator->generateTopic();
    }

    $post->name = mt_rand(0, 100) < 15 ? $generator->getRandomName() : "Anonimous";    
    $post->createTime = $post->bumped = $fakeTime ? $fakeTime : time();
    return $post; 
}

function boardCreateComment($generator, $postId, $fakeTime = null)
{
    $comment = new core_Comment();
    $comment->content = $generator->generateCommentText();
    $comment->postId = $postId;
    $comment->createTime = $fakeTime ? $fakeTime : time();
    $comment->name = mt_rand(0, 100) < 15 ? $generator->getRandomName() : "Anonimous"; 

    return $comment;
}

function boardInsertPost($pdo, $post)
{
    $model = new Model_Post();
    $model->createPost($post);

    return $pdo->lastInsertId();
}

function boardInsertComment($pdo, $comment)
{
    $model = new Model_Comment();
    $model->createComment($comment);

    return $pdo->lastInsertId();
}

function boardGetBaseUrl()
{
    return '/board2/';
}

function boardRun()
{
    $frontController = new Core_FrontController();
    $frontController->run();
}
