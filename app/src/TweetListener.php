<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';

use PDO;
use RedisException;

class TweetListener
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

    public function listenForTweets()
    {
        try {
            $pubsub = $this->redis->pubSubLoop();
            $pubsub->subscribe('new_twit');

            foreach ($pubsub as $message) {
                if ($message->kind === 'message') {
                    $this->processTweet($message->payload);
                }
            }
        } catch (RedisException $e) {
            $this->handleError('Redis Error: ' . $e->getMessage());
        }
    }

    private function processTweet($payload)
    {
        $tweet = json_decode($payload, true);

        if ($this->isValidTweet($tweet)) {
            $this->saveTweet($tweet);
        } else {
            error_log('Invalid tweet data: ' . json_encode($tweet));
        }
    }

    private function isValidTweet($tweet)
    {
        return isset($tweet['CategoryId'], $tweet['Content'], $tweet['Username']) && !empty($tweet['Content']);
    }

    private function saveTweet($tweet)
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO Twits (CategoryId, Content, Username) VALUES (?, ?, ?)');
            $stmt->execute([$tweet['CategoryId'], $tweet['Content'], $tweet['Username']]);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
        }
    }

    public function getLatestTweet()
    {
        try {
            $req = $this->pdo->query("SELECT Twits.*, Category.title AS category_title FROM Twits JOIN Category ON Twits.CategoryId = Category.id ORDER BY CreatedAt DESC LIMIT 1");
            $data = $req->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                echo json_encode([
                    'category_title' => $data['category_title'],
                    'user_name' => $data['Username'],
                    'content' => $data['Content'],
                    'createdAt' => $data['CreatedAt']
                ]);
            } else {
                echo json_encode(['error' => 'No tweets found']);
            }
        } catch (PDOException $e) {
            $this->handleError('Database Error: ' . $e->getMessage());
        }
    }

    private function handleError($message)
    {
        echo $message;
        exit;
    }
}