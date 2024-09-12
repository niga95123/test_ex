<?php

require __DIR__ . '/../../src/TweetPublisher.php';

use App\TweetPublisher;

// Инициализация и публикация твита
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category_id'] ?? null;
    $content = $_POST['content'] ?? null;
    $username = $_POST['username'] ?? null;

    try {
        $tweetPublisher = new TweetPublisher();
        $tweetPublisher->publishTweet($category, $content, $username);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
