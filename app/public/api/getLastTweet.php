<?php

require __DIR__ . '/../../src/TweetListener.php';

use App\TweetListener;

header('Content-Type: application/json');

try {
    $tweetListener = new TweetListener();
    $tweetListener->getLatestTweet();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}