<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';

use PDO;
use RedisException;

class TweetPublisher
{
    private $pdo;
    private $redis;

    public function __construct()
    {
        $this->initializeDatabase();
        $this->initializeRedis();
    }

    private function initializeDatabase()
    {
        try {
            $dbc = new DataBaseConf();
            $this->pdo = new PDO($dbc->getDsn(), $dbc->getUsername(), $dbc->getPassword());
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->handleError('Database Error: ' . $e->getMessage());
        }
    }

    private function initializeRedis()
    {
        try {
            $r = new RedisQueue();
            $this->redis = $r->getRedisCon();
        } catch (RedisException $e) {
            $this->handleError('Redis Error: ' . $e->getMessage());
        }
    }

    public function publishTweet($category, $content, $username)
    {
        if ($this->isValidTweet($category, $content, $username)) {
            $message = json_encode([
                'CategoryId' => $category,
                'Content' => $content,
                'Username' => $username
            ]);

            try {
                $this->redis->publish('new_twit', $message);
                echo json_encode(['status' => 'success']);
            } catch (RedisException $e) {
                $this->handleError('Redis Error: ' . $e->getMessage());
            }
        } else {
            $this->handleError('Invalid tweet data');
        }
    }

    private function isValidTweet($category, $content, $username)
    {
        return !empty($category) && !empty($content) && !empty($username);
    }

    private function handleError($message)
    {
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}

// Инициализация и публикация твита
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category_id'] ?? null;
    $content = $_POST['content'] ?? null;
    $username = $_POST['username'] ?? null;

    $tweetPublisher = new TweetPublisher();
    $tweetPublisher->publishTweet($category, $content, $username);
}