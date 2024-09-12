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
        $dbc = new DataBaseConf();
        $this->pdo = new PDO($dbc->getDsn(), $dbc->getUsername(), $dbc->getPassword());
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $this->pdo->query("SELECT * FROM Category WHERE id = ". $category);
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($response)) {
            return false;
        }
        return !empty($category) && !empty($content) && !empty($username);
    }

    private function handleError($message)
    {
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}