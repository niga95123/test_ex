const http = require('http');
const socketIo = require('socket.io');
const server = http.createServer();
const io = socketIo(server, {
    cors: {
        origin: "http://localhost:443",
        methods: ["GET", "POST"],
        allowedHeaders: ["my-custom-header"],
        credentials: true
    }
});

io.on('connection', (socket) => {
    console.log('New client connected');

    socket.on('new_twit', (data) => {
        console.log('Received a new twit:', data);
        io.emit('new_twit', data);
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected');
    });
});

server.listen(3000, () => {
    console.log('WebSocket server is running on port 3000');
});
