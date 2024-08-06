<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <div id="user-list">
            <!-- List of users will be dynamically populated here -->
        </div>
        <div id="chat-box" style="display:none;">
            <div id="messages">
                <!-- Messages will be appended here -->
            </div>
            <input id="message" type="text" placeholder="Type your message">
            <button id="send">Send</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userList = document.getElementById('user-list');
            const chatBox = document.getElementById('chat-box');
            const messagesContainer = document.getElementById('messages');
            const messageInput = document.getElementById('message');
            const sendButton = document.getElementById('send');
            let conversationId;

            // Fetch and display users
            fetch('/users')
                .then(response => response.json())
                .then(users => {
                    users.forEach(user => {
                        const userElement = document.createElement('div');
                        userElement.textContent = user.name;
                        userElement.dataset.userId = user.id;
                        userElement.style.cursor = 'pointer';
                        userElement.addEventListener('click', () => startConversation(user.id));
                        userList.appendChild(userElement);
                    });
                });

            function startConversation(userId) {
                fetch('/start-conversation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    conversationId = data.id;
                    chatBox.style.display = 'block';
                    loadMessages(conversationId);

                    // Unsubscribe from previous channel if already subscribed
                    if (window.Echo.privateChannel) {
                        window.Echo.leave(`chat.${window.Echo.privateChannel}`);
                    }
                    // Subscribe to the new channel
                    window.Echo.privateChannel = conversationId;
                    subscribeToChannel(conversationId);
                });
            }

            function loadMessages(conversationId) {
                fetch(`/messages/${conversationId}`)
                    .then(response => response.json())
                    .then(messages => {
                        messagesContainer.innerHTML = '';
                        messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            messageElement.textContent = `${message.user.name}: ${message.message}`;
                            messagesContainer.appendChild(messageElement);
                        });
                    });
            }

            function subscribeToChannel(conversationId) {
    //             window.Echo.private(`chat.${conversationId}`)
    // .listen('MessageSent', (e) => {
    //     const messageElement = document.createElement('div');
    //     messageElement.textContent = `${e.message.user.name}: ${e.message.message}`;
    //     document.getElementById('messages').appendChild(messageElement);
    // });

                window.Echo.private(`chat.${conversationId}`)
                    .listen('MessageSent', (e) => {
                        const messageElement = document.createElement('div');
                        messageElement.textContent = `${e.message.user.name}: ${e.message.message}`;
                        document.getElementById('messages').appendChild(messageElement);
                    });
            }

            sendButton.addEventListener('click', () => {
                const message = messageInput.value;
                if (message && conversationId) {
                    fetch('/send-message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            conversation_id: conversationId,
                            message: message
                        })
                    })
                    .then(response => response.json())
                    .then(() => {
                        messageInput.value = '';
                    });
                }
            });
        });
    </script>
</body>
</html>
