<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
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
        </select>
        <script>
            axios.get('/api/getTool.php', {params : { methode: 'getCategoryAllData' }})
                .then(response => {
                    const options = response.data.ans;
                    const selectElement = document.getElementById('category');

                    options.forEach(option => {
                        const optionElement = document.createElement('option');
                        optionElement.value = option.id;
                        optionElement.textContent = option.title;
                        selectElement.appendChild(optionElement);
                    });
                })
                .catch(error => {
                    console.error('Request failed:', error);
                });


            // Проверка наличия и заполненности всех необходимых таблиц
            axios.get('/api/getTool.php', {params : { methode: 'getCheckFullnessDataBase' }})
                .then(response => {
                    if (response.data.ans.status === 'success') {
                        console.log('DB check success');
                    } else {
                        console.log('DB check error any table or data was created');
                    }
                })
                .catch(error => {
                    console.error('Request failed:', error);
                });
        </script>
        <input type="text" id="content" name="content" placeholder="Enter your tweet">
        <input type="text" id="username" name="username" placeholder="Enter your username">
        <button type="submit">Tweet</button>
    </form>

    <div id="twits">
    </div>

    <script>
        axios.get('/api/getTool.php', {params : { methode: 'getTweetsAllData' }})
            .then(response => {
                const options = response.data.ans;
                const tweetsDiv = document.getElementById('twits');

                options.forEach(option => {
                    const tweetDiv = document.createElement('div');
                    tweetDiv.innerHTML = `<strong>${option.category_title}</strong><p>${option.Content}</p><small>by ${option.User_name} at ${option.CreatedAt}</small>`;
                    tweetsDiv.prepend(tweetDiv);
                });
            })
            .catch(error => {
                console.error('Request failed:', error);
            });
    </script>

    <script>
        const form = document.getElementById('twitForm');
        const socket = io('http://localhost:3000');

        axios.post('/api/listen.php')
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

            axios.post('/api/getTweetPublish.php', params)
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
            axios.get('/api/getLastTweet.php', [])
                .then(response => {
                    const tweetsDiv = document.getElementById('twits');
                    const tweetDiv = document.createElement('div');
                    tweetDiv.innerHTML = `<strong>${response.data.category_title}</strong><p>${response.data.content}</p><small>by ${response.data.user_name} at ${response.data.createdAt}</small>`;
                    tweetsDiv.prepend(tweetDiv);
                })
                .catch(error => {
                    console.error('Request failed:', error);
                });
        });
    </script>
</body>
</html>