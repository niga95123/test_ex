<?php

require __DIR__ . '/../vendor/autoload.php';

use App\DataBaseConf;

$d = new DataBaseConf();

try {
    $pdo = new PDO($d->getDsn(), $d->getUsername(), $d->getPassword());
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение категорий
    $stmt = $pdo->query("SELECT * FROM Category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получение твитов
    $stmt = $pdo->query("SELECT Twits.*, Category.title AS category_title FROM Twits JOIN Category ON Twits.CategoryId = Category.id ORDER BY CreatedAt DESC");
    $tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tweets</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
</head>
<body>
    <h1>Tweets</h1>

    <form id="twitForm">
        <select id="category" name="category">
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="content" name="content" placeholder="Enter your tweet">
        <input type="text" id="username" name="username" placeholder="Enter your username">
        <button type="submit">Tweet</button>
    </form>

    <div id="twits">
        <?php foreach ($tweets as $tweet): ?>
            <div>
                <strong><?= $tweet['category_title'] ?></strong>
                <p><?= $tweet['Content'] ?></p>
                <small>by <?= $tweet['Username'] ?> at <?= $tweet['CreatedAt'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        const form = document.getElementById('twitForm');
        const socket = io('http://localhost:3000');


        axios.post('/listen.php')
            .then(response => {
                console.log(response.data);
                if (response.data.status === 'success') {
                    console.log('Listening for tweets...');
                } else {
                    console.error('Error:', response.data.message);
                }
            })
            .catch(error => {
                console.error('Request failed:', error);
            });


        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const category = document.getElementById('category').value;
            const content = document.getElementById('content').value;
            const username = document.getElementById('username').value;

            const params = new URLSearchParams();
            params.append('category_id', category);
            params.append('content', content);
            params.append('username', username);

            axios.post('/TweetPublisher.php', params)
                .then(response => {
                    console.log(response.data);
                })
                .catch(error => {
                    console.error(error);
                });


            const socket = io('http://localhost:3000');
            socket.on('connect', () => {
                console.log('Connected to server');
                socket.emit('new_twit', {});
            });

        });

        socket.on('new_twit', (tweet) => {
            axios.post('/getLastTweet.php')
                .then(response => {
                    const tweetsDiv = document.getElementById('twits');
                    if (tweetsDiv) {
                        const tweetDiv = document.createElement('div');
                        tweetDiv.innerHTML = `<strong>${response.data.category_title}</strong><p>${response.data.content}</p><small>by ${response.data.user_name} at ${response.data.createdAt}</small>`;
                        tweetsDiv.prepend(tweetDiv);
                    } else {
                        console.error('Element with id "twits" not found');
                    }

                })
                .catch(error => {
                    console.error('Request failed:', error);
                });
        });



    </script>
</body>
</html>