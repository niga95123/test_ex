<?php

require __DIR__ . '/../../src/TweetPublisher.php';

use App\TweetPublisher;

// Инициализация и публикация твита
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_STRING) ?? null;
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING) ?? null;
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? null;

    try {
        $tweetPublisher = new TweetPublisher();
        $tweetPublisher->publishTweet($category, $content, $username);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
