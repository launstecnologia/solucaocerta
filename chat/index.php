<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat WhatsApp</title>
    <style>
        .container { display: flex; width: 800px; margin: 0 auto; border: 1px solid #ddd; }
        .contacts { width: 200px; border-right: 1px solid #ddd; padding: 10px; }
        .contacts .contact { padding: 10px; cursor: pointer; border-bottom: 1px solid #ddd; }
        .chat-container { width: 600px; padding: 10px; }
        .messages { height: 300px; overflow-y: auto; border-bottom: 1px solid #ddd; margin-bottom: 10px; }
        .message { padding: 5px; border-radius: 5px; margin-bottom: 5px; }
        .sent { background-color: #e0ffe0; text-align: right; }
        .received { background-color: #f0f0f0; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="contacts">
            <h3>Contatos</h3>
            <div id="contactList"></div>
            <input type="text" id="newContactInput" placeholder="Número do contato" />
            <button onclick="addContact()">Adicionar Contato</button>
        </div>

        <div class="chat-container">
            <h3 id="currentContact">Selecione um contato</h3>
            <div id="messages" class="messages"></div>
            <textarea id="messageInput" placeholder="Digite uma mensagem..." rows="3"></textarea>
            <button onclick="sendMessage()">Enviar</button>
        </div>
    </div>

    <script>
        let contacts = [];
        let currentContact = null;
        let lastMessageTimestamp = 0;

        function addContact() {
            const contactNumber = document.getElementById("newContactInput").value;
            if (contactNumber && !contacts.includes(contactNumber)) {
                contacts.push(contactNumber);
                displayContacts();
                document.getElementById("newContactInput").value = '';
            }
        }

        function displayContacts() {
            const contactListDiv = document.getElementById("contactList");
            contactListDiv.innerHTML = '';
            contacts.forEach(contact => {
                const contactDiv = document.createElement("div");
                contactDiv.classList.add("contact");
                contactDiv.textContent = contact;
                contactDiv.onclick = () => selectContact(contact);
                contactListDiv.appendChild(contactDiv);
            });
        }

        function selectContact(contact) {
            currentContact = contact;
            document.getElementById("currentContact").textContent = `Conversando com ${contact}`;
            loadMessages();
        }

        function sendMessage() {
    const messageInput = document.getElementById("messageInput");
    const message = messageInput.value.trim(); // Remove espaços extras

    if (message && currentContact) {
        messageInput.value = ''; // Limpa o campo de entrada imediatamente

        fetch('sendMessage.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message, number: currentContact })
        }).then(response => response.json())
          .then(data => {
            if (data.success) {
                appendMessage({ content: message, type: 'sent' }); // Adiciona a mensagem ao chat
                scrollToBottom(); // Faz o scroll automático
            }
        }).catch(error => {
            console.error("Erro ao enviar mensagem:", error);
        });
    }
}


        function appendMessage(msg) {
            const messagesDiv = document.getElementById("messages");
            const msgDiv = document.createElement("div");
            msgDiv.classList.add("message", msg.type === 'sent' ? "sent" : "received");
            msgDiv.textContent = msg.content;
            messagesDiv.appendChild(msgDiv);
        }

        function scrollToBottom() {
            const messagesDiv = document.getElementById("messages");
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function loadMessages() {
            if (!currentContact) return;

            const url = `getMessages.php?number=${currentContact}&after=${lastMessageTimestamp}`;
            fetch(url)
                .then(response => response.json())
                .then(messages => {
                    if (messages.length > 0) {
                        messages.forEach(msg => {
                            appendMessage(msg);
                            lastMessageTimestamp = Math.max(lastMessageTimestamp, msg.timestamp);
                        });
                        scrollToBottom();
                    }
                })
                .catch(error => {
                    console.error("Erro ao carregar mensagens:", error);
                });
        }

        // Adiciona evento para enviar mensagem com Enter e permitir Shift+Enter para nova linha
        document.getElementById("messageInput").addEventListener("keydown", function(event) {
            if (event.key === "Enter" && !event.shiftKey) {
                event.preventDefault(); // Evita a quebra de linha
                sendMessage(); // Envia a mensagem
            }
        });

        // Reduz o intervalo de atualização para 2 segundos para melhorar a velocidade de exibição
        setInterval(loadMessages, 500);
    </script>
</body>
</html>
