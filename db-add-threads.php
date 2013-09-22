<?php

require_once __DIR__ . '/lib/lib.php';

list ($dummy, $threads, $minComments, $maxComments) = $argv + array(0, 0, 0, 0);

if (empty($threads) || empty($minComments) || empty($maxComments)) {
    die("Usage: script (threads) (minComments) (maxComments)\nCreates threads with comments\n");
}

$pdo = getPdo();
$generator = new Generator();

$start = microtime(true);
$counter = 0;
$batchSize = 1000;
$posts = 0;
$comments = 0;

$fakeTime = time() - 100000;

$pdo->beginTransaction();

for ($i = 0; $i < $threads; $i++) {

    $postId = createRandomPost($pdo, $generator, $fakeTime);
    $fakeTime += 5;

    $counter ++;
    $posts ++;
    $commentCount = mt_rand($minComments, $maxComments);
    
    assert(!!$postId);

    for ($j = 0; $j < $commentCount; $j++) {
        createRandomComment($pdo, $generator, $postId, $j, $fakeTime);
        $fakeTime += 2;
        $comments ++;
        $counter ++;
    }

    if ($counter > $batchSize) {
        $counter = 0;
        $pdo->commit();
        $pdo->beginTransaction();
        echo "Posts: $posts, comments: $comments\n";
    }
}

$pdo->commit();
echo "Done. Posts: $posts, comments: $comments\n";
$end = microtime(true);
printStats($end - $start, $posts + $comments, "Adding posts");
printMemoryUsage();

function createRandomPost($pdo, $generator, $fakeTime) 
{
    $post = $generator->generatePost();
    $post->time = $fakeTime;
    $model = new Application_Models_Post();
    $model->add($post);

    return $pdo->lastInsertId();
}

function createRandomComment($pdo, $generator, $postId, $number, $fakeTime)
{
    $comment = $generator->generateComment();
    $comment->post_id = $postId;
    $comment->time = $fakeTime;
    $model = new Application_Models_Comment();
    $model->add($comment);

    return $pdo->lastInsertId();
}

