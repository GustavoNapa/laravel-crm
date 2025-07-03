<x-admin::layouts>
    <x-slot:title>
        WhatsApp Web - Paginado
    </x-slot:title>

    <div id="whatsapp-web-app" class="h-screen bg-gray-100">
        <!-- Header -->
        <div class="bg-green-600 text-white p-4 flex items-center">
            <h1 class="text-xl font-semibold">WhatsApp Web - Paginado</h1>
            <div class="ml-auto flex items-center space-x-4">
                <div class="text-sm">
                    <span id="conversations-count" class="font-medium">0</span> conversas
                </div>
                <div class="text-sm">
                    <span id="messages-count" class="font-medium">0</span> mensagens
                </div>
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
                <div id="conversations-container" class="overflow-y-auto h-full">
                    <div id="conversations-list">
                        <div class="p-4 text-center text-gray-500">
                            Carregando conversas...
                        </div>
                    </div>
                    <!-- Loading indicator for pagination -->
                    <div id="loading-more" class="p-4 text-center text-gray-500 hidden">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500 mx-auto"></div>
                        <p class="mt-2 text-sm">Carregando mais conversas...</p>
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
                    <!-- Loading indicator for message pagination -->
                    <div id="loading-more-messages" class="p-4 text-center text-gray-500 hidden">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500 mx-auto"></div>
                        <p class="mt-2 text-sm">Carregando mensagens anteriores...</p>
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
            console.log('Inicializando WhatsApp Web com paginação...');
            
            // Estado da aplicação
            const state = {
                conversations: [],
                currentConversation: null,
                messages: [],
                conversationsPagination: {
                    current_page: 1,
                    has_more: true,
                    next_cursor: null,
                    loading: false
                },
                messagesPagination: {
                    current_page: 1,
                    has_more: true,
                    next_cursor: null,
                    loading: false
                },
                scrollPosition: null
            };

            // Elementos DOM
            const elements = {
                connectionStatus: document.getElementById('connection-status'),
                conversationsCount: document.getElementById('conversations-count'),
                messagesCount: document.getElementById('messages-count'),
                conversationsList: document.getElementById('conversations-list'),
                conversationsContainer: document.getElementById('conversations-container'),
                loadingMore: document.getElementById('loading-more'),
                loadingMoreMessages: document.getElementById('loading-more-messages'),
                searchInput: document.getElementById('search-conversations'),
                chatHeader: document.getElementById('chat-header'),
                messagesArea: document.getElementById('messages-area'),
                messageInputArea: document.getElementById('message-input-area'),
                messageInput: document.getElementById('message-input'),
                sendButton: document.getElementById('send-button'),
                chatContactName: document.getElementById('chat-contact-name'),
                chatContactStatus: document.getElementById('chat-contact-status')
            };

            // Carregar conversas com paginação otimizada
            async function loadConversations(page = 1, append = false) {
                if (state.conversationsPagination.loading) return;
                
                try {
                    state.conversationsPagination.loading = true;
                    
                    if (page === 1) {
                        elements.connectionStatus.textContent = 'Carregando...';
                    } else {
                        elements.loadingMore.classList.remove('hidden');
                    }
                    
                    const searchQuery = elements.searchInput.value.trim();
                    const params = new URLSearchParams({
                        page: page,
                        per_page: 15
                    });
                    
                    if (searchQuery) {
                        params.append('search', searchQuery);
                    }

                    // Usar cursor se disponível para melhor performance
                    if (append && state.conversationsPagination.next_cursor) {
                        params.append('cursor', state.conversationsPagination.next_cursor);
                    }
                    
                    const response = await fetch(`/admin/quarkions/whatsapp/conversations?${params}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.conversations)) {
                        if (append) {
                            // Evitar duplicatas ao fazer append
                            const existingIds = new Set(state.conversations.map(c => c.id || c.remoteJid));
                            const newConversations = data.conversations.filter(c => 
                                !existingIds.has(c.id || c.remoteJid)
                            );
                            state.conversations = [...state.conversations, ...newConversations];
                        } else {
                            state.conversations = data.conversations;
                        }
                        
                        state.conversationsPagination = {
                            current_page: data.pagination.current_page,
                            has_more: data.pagination.has_more,
                            next_cursor: data.pagination.next_cursor,
                            loading: false
                        };
                        
                        renderConversations(state.conversations, append);
                        
                        // Atualizar contadores
                        elements.conversationsCount.textContent = state.conversations.length;
                        
                        if (page === 1) {
                            elements.connectionStatus.textContent = `Conectado (${data.pagination.total} total)`;
                        }
                    } else {
                        throw new Error('Formato de resposta inválido');
                    }
                } catch (error) {
                    console.error('Erro ao carregar conversas:', error);
                    if (page === 1) {
                        elements.connectionStatus.textContent = 'Erro de conexão';
                        elements.conversationsList.innerHTML = `
                            <div class="p-4 text-center text-red-500">
                                Erro ao carregar conversas: ${error.message}
                            </div>
                        `;
                    }
                } finally {
                    state.conversationsPagination.loading = false;
                    elements.loadingMore.classList.add('hidden');
                }
            }

            // Renderizar lista de conversas
            function renderConversations(conversations, append = false) {
                if (!conversations || conversations.length === 0) {
                    if (!append) {
                        elements.conversationsList.innerHTML = `
                            <div class="p-4 text-center text-gray-500">
                                Nenhuma conversa encontrada
                            </div>
                        `;
                    }
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

                if (append) {
                    elements.conversationsList.innerHTML += html;
                } else {
                    elements.conversationsList.innerHTML = html;
                }

                // Adicionar event listeners para novas conversas
                document.querySelectorAll('.conversation-item').forEach(item => {
                    if (!item.hasAttribute('data-listener-added')) {
                        item.setAttribute('data-listener-added', 'true');
                        item.addEventListener('click', function() {
                            const conversationId = this.dataset.conversationId;
                            selectConversation(conversationId);
                        });
                    }
                });
            }

            // Carregar mensagens com paginação otimizada
            async function loadMessages(conversationId, page = 1, append = false, loadOlder = false) {
                if (state.messagesPagination.loading) return;
                
                try {
                    state.messagesPagination.loading = true;
                    
                    const params = new URLSearchParams({
                        page: page,
                        per_page: 20,
                        load_older: loadOlder
                    });

                    // Usar cursor se disponível para melhor performance
                    if (append && state.messagesPagination.next_cursor) {
                        params.append('cursor', state.messagesPagination.next_cursor);
                    }
                    
                    if (page === 1 && !append) {
                        elements.messagesArea.innerHTML = '<div class="text-center text-gray-500">Carregando mensagens...</div>';
                    } else if (loadOlder) {
                        elements.loadingMoreMessages.classList.remove('hidden');
                        // Guardar posição do scroll antes de carregar mensagens antigas
                        const scrollHeight = elements.messagesArea.scrollHeight;
                        const scrollTop = elements.messagesArea.scrollTop;
                        state.scrollPosition = { scrollHeight, scrollTop };
                    }
                    
                    const response = await fetch(`/admin/quarkions/whatsapp/conversations/${conversationId}?${params}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.messages)) {
                        if (append || loadOlder) {
                            // Evitar duplicatas
                            const existingIds = new Set(state.messages.map(m => m.id));
                            const newMessages = data.messages.filter(m => !existingIds.has(m.id));
                            
                            if (loadOlder) {
                                // Inserir mensagens antigas no início
                                state.messages = [...newMessages.reverse(), ...state.messages];
                            } else {
                                // Adicionar mensagens novas no final
                                state.messages = [...state.messages, ...newMessages];
                            }
                        } else {
                            state.messages = data.messages.reverse(); // Reverter para ordem cronológica
                        }
                        
                        state.messagesPagination = {
                            current_page: data.pagination.current_page,
                            has_more: data.pagination.has_more,
                            next_cursor: data.pagination.next_cursor,
                            loading: false
                        };
                        
                        renderMessages(state.messages, loadOlder);
                        
                        // Atualizar contador de mensagens
                        elements.messagesCount.textContent = state.messages.length;
                        
                        // Restaurar posição do scroll se carregou mensagens antigas
                        if (loadOlder && state.scrollPosition) {
                            const newScrollHeight = elements.messagesArea.scrollHeight;
                            const addedHeight = newScrollHeight - state.scrollPosition.scrollHeight;
                            elements.messagesArea.scrollTop = state.scrollPosition.scrollTop + addedHeight;
                        } else if (!append && !loadOlder) {
                            // Scroll para o final para mensagens novas
                            setTimeout(() => scrollToBottom(), 100);
                        }
                    } else {
                        elements.messagesArea.innerHTML = '<div class="text-center text-gray-500">Nenhuma mensagem encontrada</div>';
                    }
                } catch (error) {
                    console.error('Erro ao carregar mensagens:', error);
                    if (page === 1) {
                        elements.messagesArea.innerHTML = '<div class="text-center text-red-500">Erro ao carregar mensagens</div>';
                    }
                } finally {
                    state.messagesPagination.loading = false;
                    elements.loadingMoreMessages.classList.add('hidden');
                }
            }

            // Função de debounce para otimizar eventos de scroll
            function debounce(func, delay) {
                let timeoutId;
                return function (...args) {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => func.apply(this, args), delay);
                };
            }

            // Scroll infinito para conversas
            const handleConversationsScroll = debounce(() => {
                const container = elements.conversationsContainer;
                const threshold = 100; // pixels antes do final
                
                if (container.scrollTop + container.clientHeight >= container.scrollHeight - threshold) {
                    if (state.conversationsPagination.has_more && !state.conversationsPagination.loading) {
                        const nextPage = state.conversationsPagination.current_page + 1;
                        loadConversations(nextPage, true);
                    }
                }
            }, 200);

            // Scroll infinito para mensagens (carregar mensagens antigas ao chegar no topo)
            const handleMessagesScroll = debounce(() => {
                const container = elements.messagesArea;
                const threshold = 100;
                
                // Carregar mensagens antigas quando chegar perto do topo
                if (container.scrollTop <= threshold) {
                    if (state.messagesPagination.has_more && !state.messagesPagination.loading) {
                        const nextPage = state.messagesPagination.current_page + 1;
                        loadMessages(state.currentConversation.id || state.currentConversation.remoteJid, nextPage, true, true);
                    }
                }
            }, 200);

            // Função para scroll suave para o final
            function scrollToBottom(smooth = true) {
                if (smooth) {
                    elements.messagesArea.scrollTo({
                        top: elements.messagesArea.scrollHeight,
                        behavior: 'smooth'
                    });
                } else {
                    elements.messagesArea.scrollTop = elements.messagesArea.scrollHeight;
                }
            }

            // Renderizar mensagens
            function renderMessages(messages, preserveScroll = false) {
                if (!messages || messages.length === 0) {
                    elements.messagesArea.innerHTML = '<div class="text-center text-gray-500">Nenhuma mensagem</div>';
                    return;
                }

                const html = messages.map(message => `
                    <div class="mb-4 ${message.fromMe || message.key?.fromMe ? 'text-right' : 'text-left'}">
                        <div class="inline-block max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                            message.fromMe || message.key?.fromMe 
                                ? 'bg-green-500 text-white' 
                                : 'bg-white border border-gray-300 text-gray-900'
                        }">
                            <p class="text-sm">${message.body || message.message?.conversation || 'Mensagem sem conteúdo'}</p>
                            <p class="text-xs mt-1 ${message.fromMe || message.key?.fromMe ? 'text-green-100' : 'text-gray-500'}">
                                ${message.messageTimestamp ? new Date(message.messageTimestamp * 1000).toLocaleTimeString() : ''}
                            </p>
                        </div>
                    </div>
                `).join('');

                elements.messagesArea.innerHTML = html;
                
                if (!preserveScroll) {
                    setTimeout(() => scrollToBottom(), 100);
                }
            }

            // Debounce para busca de conversas
            const handleSearchInput = debounce(() => {
                // Reset pagination para nova busca
                state.conversationsPagination = {
                    current_page: 1,
                    has_more: true,
                    next_cursor: null,
                    loading: false
                };
                loadConversations(1, false);
            }, 500);
            }

            // Selecionar conversa
            function selectConversation(conversationId) {
                const conversation = state.conversations.find(c => 
                    (c.id || c.remoteJid) === conversationId
                );
                
                if (!conversation) return;

                state.currentConversation = conversation;
                
                // Reset message pagination
                state.messagesPagination = {
                    current_page: 1,
                    has_more: true,
                    loading: false
                };
                
                // Limpar mensagens anteriores
                state.messages = [];
                
                // Atualizar header do chat
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
                elements.chatHeader.classList.remove('hidden');
                elements.messageInputArea.classList.remove('hidden');
                
                // Carregar mensagens da conversa
                loadMessages(conversationId, 1, false);
                
                // Destacar conversa selecionada
                document.querySelectorAll('.conversation-item').forEach(item => {
                    item.classList.remove('bg-green-100');
                });
                const selectedItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
                if (selectedItem) {
                    selectedItem.classList.add('bg-green-100');
                }
            }

            // Enviar mensagem otimizada
            async function sendMessage() {
                const messageText = elements.messageInput.value.trim();
                if (!messageText || !state.currentConversation) return;

                try {
                    const response = await fetch('/admin/quarkions/whatsapp/send-message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            remoteJid: state.currentConversation.id || state.currentConversation.remoteJid,
                            message: messageText
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        elements.messageInput.value = '';
                        
                        // Adicionar mensagem localmente para feedback imediato
                        const newMessage = {
                            id: Date.now(),
                            body: messageText,
                            fromMe: true,
                            messageTimestamp: Date.now() / 1000
                        };
                        state.messages.push(newMessage);
                        renderMessages(state.messages);
                        
                        // Recarregar conversas para atualizar última mensagem (sem reset da lista)
                        setTimeout(() => loadConversations(1, false), 1000);
                    } else {
                        alert('Erro ao enviar mensagem: ' + (data.message || 'Erro desconhecido'));
                    }
                } catch (error) {
                    console.error('Erro ao enviar mensagem:', error);
                    alert('Erro ao enviar mensagem');
                }
            }

            // Event listeners
            elements.conversationsContainer.addEventListener('scroll', handleConversationsScroll);
            elements.messagesArea.addEventListener('scroll', handleMessagesScroll);
            elements.searchInput.addEventListener('input', handleSearchInput);

            elements.sendButton.addEventListener('click', sendMessage);
            elements.messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Inicializar aplicação
            loadConversations();
            
            // Atualizar conversas automaticamente a cada 30 segundos
            setInterval(() => {
                if (state.conversationsPagination.current_page === 1) {
                    loadConversations(1, false);
                }
            }, 30000);
        });
    </script>
    @endPushOnce
</x-admin::layouts>
