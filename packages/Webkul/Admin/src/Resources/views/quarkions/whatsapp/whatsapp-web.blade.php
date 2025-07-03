<x-admin::layouts>
    <x-slot:title>
        WhatsApp Web
    </x-slot:title>

    <div id="whatsapp-web-app" class="h-screen bg-gray-100">
        <!-- Header -->
        <div class="bg-green-600 text-white p-4 flex items-center">
            <h1 class="text-xl font-semibold">WhatsApp Web</h1>
            <div class="ml-auto">
                <span id="connection-status" class="text-sm">Conectando...</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex h-full">
            <!-- Sidebar - Lista de Conversas -->
            <div class="w-1/3 bg-white border-r border-gray-300">
                <!-- Search Bar -->
                <div class="p-4 border-b border-gray-200">
                    <input 
                        type="text" 
                        id="search-conversations"
                        placeholder="Buscar conversas..." 
                        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>

                <!-- Conversations List -->
                <div id="conversations-list" class="overflow-y-auto h-full">
                    <div class="p-4 text-center text-gray-500">
                        Carregando conversas...
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
                <!-- Chat Header -->
                <div id="chat-header" class="bg-gray-50 p-4 border-b border-gray-200 hidden">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
                        <div>
                            <h3 id="chat-contact-name" class="font-semibold"></h3>
                            <p id="chat-contact-status" class="text-sm text-gray-500"></p>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div id="messages-area" class="flex-1 overflow-y-auto p-4 bg-gray-50">
                    <div class="text-center text-gray-500 mt-20">
                        Selecione uma conversa para começar
                    </div>
                </div>

                <!-- Message Input -->
                <div id="message-input-area" class="p-4 bg-white border-t border-gray-200 hidden">
                    <div class="flex items-center">
                        <input 
                            type="text" 
                            id="message-input"
                            placeholder="Digite uma mensagem..." 
                            class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                        <button 
                            id="send-button"
                            class="ml-3 bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            Enviar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando WhatsApp Web...');
            
            // Estado da aplicação
            const state = {
                conversations: [],
                currentConversation: null,
                messages: []
            };

            // Elementos DOM
            const elements = {
                connectionStatus: document.getElementById('connection-status'),
                conversationsList: document.getElementById('conversations-list'),
                searchInput: document.getElementById('search-conversations'),
                chatHeader: document.getElementById('chat-header'),
                messagesArea: document.getElementById('messages-area'),
                messageInputArea: document.getElementById('message-input-area'),
                messageInput: document.getElementById('message-input'),
                sendButton: document.getElementById('send-button'),
                chatContactName: document.getElementById('chat-contact-name'),
                chatContactStatus: document.getElementById('chat-contact-status')
            };

            // Carregar conversas
            async function loadConversations() {
                try {
                    elements.connectionStatus.textContent = 'Carregando...';
                    
                    const response = await fetch('/api/whatsapp/conversations');
                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.conversations)) {
                        state.conversations = data.conversations;
                        renderConversations(state.conversations);
                        elements.connectionStatus.textContent = `Conectado (${state.conversations.length} conversas)`;
                    } else {
                        throw new Error('Formato de resposta inválido');
                    }
                } catch (error) {
                    console.error('Erro ao carregar conversas:', error);
                    elements.connectionStatus.textContent = 'Erro de conexão';
                    elements.conversationsList.innerHTML = `
                        <div class="p-4 text-center text-red-500">
                            Erro ao carregar conversas: ${error.message}
                        </div>
                    `;
                }
            }

            // Renderizar lista de conversas
            function renderConversations(conversations) {
                if (!conversations || conversations.length === 0) {
                    elements.conversationsList.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            Nenhuma conversa encontrada
                        </div>
                    `;
                    return;
                }

                const html = conversations.map(conversation => `
                    <div class="conversation-item p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50" 
                         data-conversation-id="${conversation.id || conversation.remoteJid}">
                        <div class="flex items-center">
                            <div class="relative w-12 h-12 mr-3">
                                ${conversation.profile_photo ? 
                                    `<img src="${conversation.profile_photo}" alt="Profile" class="w-12 h-12 rounded-full object-cover">` :
                                    `<div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-gray-600 font-semibold">
                                            ${(conversation.name || conversation.pushName || conversation.remoteJid || 'N/A').charAt(0).toUpperCase()}
                                        </span>
                                    </div>`
                                }
                                ${conversation.unread_count > 0 ? 
                                    `<div class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                        ${conversation.unread_count > 99 ? '99+' : conversation.unread_count}
                                    </div>` : ''
                                }
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">
                                    ${conversation.name || conversation.pushName || conversation.remoteJid || 'Contato sem nome'}
                                </h4>
                                <p class="text-sm text-gray-500 truncate">
                                    ${conversation.last_message || 'Sem mensagens'}
                                </p>
                            </div>
                            <div class="text-xs text-gray-400">
                                ${conversation.timestamp ? new Date(conversation.timestamp * 1000).toLocaleTimeString() : ''}
                            </div>
                        </div>
                    </div>
                `).join('');

                elements.conversationsList.innerHTML = html;

                // Adicionar event listeners
                document.querySelectorAll('.conversation-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const conversationId = this.dataset.conversationId;
                        selectConversation(conversationId);
                    });
                });
            }

            // Selecionar conversa
            function selectConversation(conversationId) {
                const conversation = state.conversations.find(c => 
                    (c.id || c.remoteJid) === conversationId
                );
                
                if (!conversation) return;

                state.currentConversation = conversation;
                
                // Atualizar header do chat com foto de perfil
                const chatHeaderHtml = `
                    <div class="flex items-center">
                        <div class="w-10 h-10 mr-3">
                            ${conversation.profile_photo ? 
                                `<img src="${conversation.profile_photo}" alt="Profile" class="w-10 h-10 rounded-full object-cover">` :
                                `<div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-gray-600 font-semibold">
                                        ${(conversation.name || conversation.pushName || conversation.remoteJid || 'Contato').charAt(0).toUpperCase()}
                                    </span>
                                </div>`
                            }
                        </div>
                        <div>
                            <h3 id="chat-contact-name" class="font-semibold">${conversation.name || conversation.pushName || conversation.remoteJid || 'Contato'}</h3>
                            <p id="chat-contact-status" class="text-sm text-gray-500">Online</p>
                        </div>
                    </div>
                `;
                
                elements.chatHeader.innerHTML = chatHeaderHtml;
                
                // Mostrar área do chat
                elements.chatHeader.classList.remove('hidden');
                elements.messageInputArea.classList.remove('hidden');
                
                // Carregar mensagens
                loadMessages(conversationId);
                
                // Destacar conversa selecionada
                document.querySelectorAll('.conversation-item').forEach(item => {
                    item.classList.remove('bg-green-100');
                });
                document.querySelector(`[data-conversation-id="${conversationId}"]`).classList.add('bg-green-100');
                
                // Zerar contador de não lidas para esta conversa
                if (conversation.unread_count > 0) {
                    fetch(`/api/whatsapp/conversations/${conversationId}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).then(() => {
                        // Atualizar contador localmente
                        conversation.unread_count = 0;
                        renderConversations(state.conversations);
                    }).catch(error => {
                        console.error('Error marking conversation as read:', error);
                    });
                }
            }

            // Carregar mensagens
            async function loadMessages(conversationId) {
                try {
                    elements.messagesArea.innerHTML = '<div class="text-center text-gray-500">Carregando mensagens...</div>';
                    
                    const response = await fetch(`/api/whatsapp/conversations/${conversationId}/messages`);
                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.messages)) {
                        state.messages = data.messages;
                        renderMessages(state.messages);
                    } else {
                        elements.messagesArea.innerHTML = '<div class="text-center text-gray-500">Nenhuma mensagem encontrada</div>';
                    }
                } catch (error) {
                    console.error('Erro ao carregar mensagens:', error);
                    elements.messagesArea.innerHTML = '<div class="text-center text-red-500">Erro ao carregar mensagens</div>';
                }
            }

            // Renderizar mensagens
            function renderMessages(messages) {
                if (!messages || messages.length === 0) {
                    elements.messagesArea.innerHTML = '<div class="text-center text-gray-500">Nenhuma mensagem</div>';
                    return;
                }

                const html = messages.map(message => {
                    const isFromMe = message.fromMe || message.key?.fromMe;
                    const messageClass = isFromMe ? 'ml-auto bg-green-500 text-white' : 'mr-auto bg-white';
                    
                    return `
                        <div class="mb-4 flex ${isFromMe ? 'justify-end' : 'justify-start'}">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${messageClass}">
                                <p class="text-sm">${message.message?.conversation || message.body || 'Mensagem sem conteúdo'}</p>
                                <p class="text-xs mt-1 opacity-70">
                                    ${message.messageTimestamp ? new Date(message.messageTimestamp * 1000).toLocaleTimeString() : ''}
                                </p>
                            </div>
                        </div>
                    `;
                }).join('');

                elements.messagesArea.innerHTML = html;
                elements.messagesArea.scrollTop = elements.messagesArea.scrollHeight;
            }

            // Enviar mensagem
            async function sendMessage() {
                const messageText = elements.messageInput.value.trim();
                if (!messageText || !state.currentConversation) return;

                try {
                    const response = await fetch('/api/whatsapp/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            to: state.currentConversation.id || state.currentConversation.remoteJid,
                            message: messageText
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        elements.messageInput.value = '';
                        // Recarregar mensagens
                        loadMessages(state.currentConversation.id || state.currentConversation.remoteJid);
                        // Recarregar conversas para atualizar última mensagem
                        loadConversations();
                    } else {
                        alert('Erro ao enviar mensagem: ' + (data.message || 'Erro desconhecido'));
                    }
                } catch (error) {
                    console.error('Erro ao enviar mensagem:', error);
                    alert('Erro ao enviar mensagem');
                }
            }

            // Event listeners
            elements.sendButton.addEventListener('click', sendMessage);
            elements.messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });

            elements.searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const filteredConversations = state.conversations.filter(conversation => {
                    const name = (conversation.name || conversation.pushName || conversation.remoteJid || '').toLowerCase();
                    return name.includes(searchTerm);
                });
                renderConversations(filteredConversations);
            });

            // Configurar Echo para broadcast em tempo real
            function setupEcho() {
                if (typeof Echo !== 'undefined') {
                    // Escutar mensagens globais do WhatsApp
                    Echo.channel('whatsapp-messages')
                        .listen('MessageCreated', (e) => {
                            console.log('Nova mensagem recebida:', e);
                            
                            // Atualizar lista de conversas
                            loadConversations();
                            
                            // Se a conversa atual é a que recebeu a mensagem, recarregar mensagens
                            if (state.currentConversation && 
                                state.currentConversation.id === e.message.lead_id) {
                                loadMessages(state.currentConversation.id);
                            }
                        });

                    // Escutar mensagens específicas da conversa atual
                    function listenToConversation(conversationId) {
                        if (state.conversationChannel) {
                            Echo.leave(state.conversationChannel);
                        }
                        
                        state.conversationChannel = `conversation.${conversationId}`;
                        Echo.private(state.conversationChannel)
                            .listen('MessageCreated', (e) => {
                                console.log('Mensagem na conversa atual:', e);
                                
                                // Adicionar mensagem à lista atual
                                if (state.messages) {
                                    const newMessage = {
                                        id: e.message.id,
                                        body: e.message.mensagem,
                                        fromMe: e.message.tipo === 'enviada',
                                        messageTimestamp: new Date(e.message.criado_em).getTime() / 1000,
                                        message: {
                                            conversation: e.message.mensagem
                                        },
                                        key: {
                                            fromMe: e.message.tipo === 'enviada'
                                        }
                                    };
                                    
                                    state.messages.push(newMessage);
                                    renderMessages(state.messages);
                                }
                                
                                // Zerar contador de não lidas se a janela está ativa
                                if (document.hasFocus() && state.currentConversation) {
                                    const conversation = state.conversations.find(c => c.id === conversationId);
                                    if (conversation) {
                                        conversation.unread_count = 0;
                                        renderConversations(state.conversations);
                                    }
                                }
                            });
                    }
                    
                    // Adicionar listener para quando uma conversa é selecionada
                    const originalSelectConversation = selectConversation;
                    selectConversation = function(conversationId) {
                        originalSelectConversation(conversationId);
                        listenToConversation(conversationId);
                    };
                } else {
                    console.warn('Echo não está disponível. Broadcast em tempo real não funcionará.');
                }
            }

            // Inicializar
            loadConversations();
            setupEcho();
            
            // Atualizar conversas a cada 30 segundos (fallback se Echo não estiver funcionando)
            setInterval(loadConversations, 30000);
        });
    </script>
    @endPushOnce
</x-admin::layouts>

