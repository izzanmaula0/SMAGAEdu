<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .chat-container {
            max-width: 800px;
            margin: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 40px);
            /* Adjust based on padding/margin */
            max-height: 700px;
            /* Optional: set a max height */
        }

        .chat-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            text-align: center;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            line-height: 1.4;
        }

        .message.user {
            background-color: #007bff;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }

        .message.ai {
            background-color: #e9ecef;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }

        .message .sender {
            font-size: 0.8em;
            margin-bottom: 3px;
            color: #f0f0f0;
        }

        .message.ai .sender {
            color: #555;
        }

        .chat-input-area {
            padding: 15px;
            border-top: 1px solid #ddd;
            background-color: #f8f9fa;
        }

        .chat-input-area .form-control {
            border-radius: 20px;
        }

        .chat-input-area .btn {
            border-radius: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chat-container {
                margin: 10px;
                height: calc(100vh - 20px);
            }

            .message {
                max-width: 85%;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid d-flex flex-column p-0">
        <div class="chat-container my-3">
            <div class="chat-header">
                <h5>AI Assistant</h5>
            </div>
            <div class="chat-messages" id="chatMessages">
                <!-- Contoh Pesan AI -->
                <div class="message ai">
                    <div class="sender">AI</div>
                    Halo! Ada yang bisa saya bantu hari ini?
                </div>

                <!-- Contoh Pesan User -->
                <div class="message user">
                    <div class="sender">Anda</div>
                    Ya, saya ingin bertanya tentang Bootstrap.
                </div>

                <div class="message ai">
                    <div class="sender">AI</div>
                    Tentu, apa yang ingin Anda ketahui tentang Bootstrap?
                </div>
                <div class="message user">
                    <div class="sender">Anda</div>
                    Bagaimana cara membuat layout responsif?
                </div>
                <div class="message ai">
                    <div class="sender">AI</div>
                    Bootstrap menggunakan sistem grid berbasis flexbox yang kuat untuk membangun layout responsif. Anda bisa menggunakan class seperti `.container`, `.row`, dan `.col-*` untuk mengatur tata letak elemen Anda.
                </div>

            </div>
            <div class="chat-input-area">
                <form id="chatForm">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Ketik pesan Anda..." id="userInput" aria-label="Ketik pesan Anda">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Kirim</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Optional: Script sederhana untuk menambahkan pesan baru (hanya untuk demo UI)
        // dan auto-scroll ke pesan terbaru
        const chatForm = document.getElementById('chatForm');
        const userInput = document.getElementById('userInput');
        const chatMessages = document.getElementById('chatMessages');

        chatForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const messageText = userInput.value.trim();
            if (messageText) {
                // Tambah pesan user
                addMessage(messageText, 'user');
                userInput.value = '';

                // Simulasi balasan AI setelah 1 detik
                setTimeout(() => {
                    addMessage('Ini adalah balasan otomatis dari AI.', 'ai');
                }, 1000);
            }
        });

        function addMessage(text, senderType) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', senderType);

            const senderNameDiv = document.createElement('div');
            senderNameDiv.classList.add('sender');
            senderNameDiv.textContent = senderType === 'user' ? 'Anda' : 'AI';

            messageDiv.appendChild(senderNameDiv);
            messageDiv.append(document.createTextNode(text)); // Menggunakan createTextNode untuk keamanan dasar
            chatMessages.appendChild(messageDiv);

            // Auto-scroll ke bawah
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Scroll ke bawah saat pertama kali load jika ada banyak pesan
        chatMessages.scrollTop = chatMessages.scrollHeight;
    </script>
</body>

</html>