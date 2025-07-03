<x-admin::layouts>
    <x-slot:title>
        WhatsApp Web
    </x-slot:title>

    <div class="h-screen bg-gray-100 overflow-hidden">
        <!-- WhatsApp Web Container -->
        <div class="flex h-full bg-white" id="whatsapp-web-app">
            <!-- Sidebar -->
            <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
                <!-- Header -->
                <div class="bg-gray-50 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900">WhatsApp</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path d="M10 4a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path d="M10 20a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div class="p-3 bg-white border-b border-gray-200">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm" 
                            placeholder="Pesquisar ou começar uma nova conversa"
                        >
                    </div>
                </div>

                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Loading State -->
                    <div v-if="loading" class="p-4">
                        <div v-for="i in 5" :key="i" class="flex items-center space-x-3 p-3 animate-pulse">
                            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                            <div class="flex-1">
                                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversations -->
                    <div v-else>
                        <div 
                            v-for="conversation in filteredConversations" 
                            :key="conversation.id"
                            @click="selectConversation(conversation)"
                            :class="[
                                'flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100',
                                selectedConversation?.id === conversation.id ? 'bg-gray-100' : ''
                            ]"
                        >
                            <!-- Avatar -->
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-medium text-lg"
                                     :style="{ backgroundColor: getAvatarColor(conversation.name) }">
                                    @{{ getInitials(conversation.name) }}
                                </div>
                                <div v-if="conversation.unread > 0" 
                                     class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    @{{ conversation.unread }}
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="ml-3 flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        @{{ conversation.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        @{{ formatTime(conversation.updatedAt) }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-500 truncate">
                                        @{{ conversation.lastMessage }}
                                    </p>
                                    <div v-if="conversation.unread > 0" class="ml-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            @{{ conversation.unread }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="filteredConversations.length === 0 && !loading" class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.471L3 21l2.471-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma conversa encontrada</h3>
                            <p class="mt-1 text-sm text-gray-500">Comece uma nova conversa ou aguarde mensagens.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
                <!-- No conversation selected -->
                <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center bg-gray-50">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.471L3 21l2.471-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">WhatsApp Web</h3>
                        <p class="mt-2 text-sm text-gray-500">Selecione uma conversa para começar a conversar</p>
                    </div>
                </div>

                <!-- Chat Header -->
                <div v-if="selectedConversation" class="bg-gray-50 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-medium"
                                 :style="{ backgroundColor: getAvatarColor(selectedConversation.name) }">
                                @{{ getInitials(selectedConversation.name) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">@{{ selectedConversation.name }}</h3>
                                <p class="text-xs text-gray-500">
                                    <span v-if="connectionStatus === 'connected'" class="text-green-600">online</span>
                                    <span v-else-if="connectionStatus === 'connecting'" class="text-yellow-600">conectando...</span>
                                    <span v-else class="text-red-600">offline</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path d="M10 4a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path d="M10 20a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div v-if="selectedConversation" class="flex-1 overflow-y-auto p-4 bg-gray-50" 
                     style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"100\" height=\"100\" viewBox=\"0 0 100 100\"><defs><pattern id=\"grain\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"><circle cx=\"50\" cy=\"50\" r=\"1\" fill=\"%23f0f0f0\" opacity=\"0.3\"/></pattern></defs><rect width=\"100\" height=\"100\" fill=\"url(%23grain)\"/></svg>');"
                     ref="messagesContainer">
                    
                    <!-- Loading Messages -->
                    <div v-if="loadingMessages" class="space-y-4">
                        <div v-for="i in 3" :key="i" class="flex animate-pulse">
                            <div class="w-64 h-12 bg-gray-200 rounded-lg"></div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div v-else class="space-y-2">
                        <div v-for="message in messages" :key="message.id" 
                             :class="[
                                 'flex',
                                 message.tipo === 'enviada' ? 'justify-end' : 'justify-start'
                             ]">
                            <div :class="[
                                'max-w-xs lg:max-w-md px-4 py-2 rounded-lg shadow-sm',
                                message.tipo === 'enviada' 
                                    ? 'bg-green-500 text-white' 
                                    : 'bg-white text-gray-900'
                            ]">
                                <p class="text-sm">@{{ message.mensagem }}</p>
                                <p :class="[
                                    'text-xs mt-1',
                                    message.tipo === 'enviada' ? 'text-green-100' : 'text-gray-500'
                                ]">
                                    @{{ formatMessageTime(message.created_at) }}
                                    <span v-if="message.tipo === 'enviada'" class="ml-1">
                                        <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty Messages -->
                    <div v-if="messages.length === 0 && !loadingMessages" class="text-center py-8">
                        <p class="text-gray-500">Nenhuma mensagem ainda. Comece a conversa!</p>
                    </div>
                </div>

                <!-- Message Input -->
                <div v-if="selectedConversation" class="bg-gray-50 p-4 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        
                        <div class="flex-1 relative">
                            <input 
                                v-model="newMessage"
                                @keypress.enter="sendMessage"
                                type="text" 
                                class="block w-full px-4 py-2 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm" 
                                placeholder="Digite uma mensagem"
                                :disabled="sending"
                            >
                        </div>

                        <button 
                            @click="sendMessage"
                            :disabled="!newMessage.trim() || sending"
                            :class="[
                                'p-2 rounded-full transition-colors',
                                newMessage.trim() && !sending 
                                    ? 'bg-green-500 text-white hover:bg-green-600' 
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            ]">
                            <svg v-if="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    conversations: [],
                    selectedConversation: null,
                    messages: [],
                    searchQuery: '',
                    newMessage: '',
                    loading: true,
                    loadingMessages: false,
                    sending: false,
                    connectionStatus: 'connecting', // connecting, connected, disconnected
                    pollInterval: null,
                    statusInterval: null
                }
            },
            computed: {
                filteredConversations() {
                    if (!Array.isArray(this.conversations)) return [];
                    if (!this.searchQuery) return this.conversations;
                    
                    return this.conversations.filter(conv => 
                        (conv.name && conv.name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                        (conv.lastMessage && conv.lastMessage.toLowerCase().includes(this.searchQuery.toLowerCase()))
                    );
                }
            },
            mounted() {
                this.loadConversations();
                this.checkConnectionStatus();
                this.startPolling();
            },
            beforeUnmount() {
                this.stopPolling();
            },
            methods: {
                getCsrfToken() {
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) return metaTag.getAttribute('content');
                    
                    const hiddenInput = document.querySelector('input[name="_token"]');
                    if (hiddenInput) return hiddenInput.value;
                    
                    if (window.Laravel && window.Laravel.csrfToken) {
                        return window.Laravel.csrfToken;
                    }
                    
                    console.warn('CSRF token não encontrado');
                    return '';
                },

                async loadConversations() {
                    try {
                        this.loading = true;
                        const response = await fetch('/admin/quarkions/whatsapp/conversations', {
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.conversations = data.conversations || [];
                        } else {
                            console.error('Erro ao carregar conversas:', response.statusText);
                        }
                    } catch (error) {
                        console.error('Erro ao carregar conversas:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async selectConversation(conversation) {
                    this.selectedConversation = conversation;
                    await this.loadMessages(conversation.id);
                    
                    // Mark as read
                    if (conversation.unread > 0) {
                        await this.markAsRead(conversation.id);
                        conversation.unread = 0;
                    }
                },

                async loadMessages(conversationId) {
                    try {
                        this.loadingMessages = true;
                        const response = await fetch(`/admin/quarkions/whatsapp/conversations/${conversationId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.messages = data.messages || [];
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        } else {
                            console.error('Erro ao carregar mensagens:', response.statusText);
                        }
                    } catch (error) {
                        console.error('Erro ao carregar mensagens:', error);
                    } finally {
                        this.loadingMessages = false;
                    }
                },

                async sendMessage() {
                    if (!this.newMessage.trim() || this.sending || !this.selectedConversation) return;

                    try {
                        this.sending = true;
                        const response = await fetch('/admin/quarkions/whatsapp/send-message', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                conversation_id: this.selectedConversation.id,
                                message: this.newMessage
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            
                            // Add message to local state
                            this.messages.push({
                                id: Date.now(),
                                mensagem: this.newMessage,
                                tipo: 'enviada',
                                created_at: new Date().toISOString()
                            });

                            // Update conversation last message
                            this.selectedConversation.lastMessage = this.newMessage;
                            this.selectedConversation.updatedAt = new Date().toISOString();

                            this.newMessage = '';
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        } else {
                            console.error('Erro ao enviar mensagem:', response.statusText);
                            alert('Erro ao enviar mensagem. Tente novamente.');
                        }
                    } catch (error) {
                        console.error('Erro ao enviar mensagem:', error);
                        alert('Erro ao enviar mensagem. Tente novamente.');
                    } finally {
                        this.sending = false;
                    }
                },

                async markAsRead(conversationId) {
                    try {
                        await fetch(`/admin/quarkions/whatsapp/conversations/${conversationId}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            }
                        });
                    } catch (error) {
                        console.error('Erro ao marcar como lida:', error);
                    }
                },

                async checkConnectionStatus() {
                    try {
                        const response = await fetch('/admin/quarkions/whatsapp/status', {
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.connectionStatus = data.status === 'open' ? 'connected' : 'disconnected';
                        } else {
                            this.connectionStatus = 'disconnected';
                        }
                    } catch (error) {
                        console.error('Erro ao verificar status:', error);
                        this.connectionStatus = 'disconnected';
                    }
                },

                startPolling() {
                    // Poll conversations every 10 seconds
                    this.pollInterval = setInterval(() => {
                        this.loadConversations();
                    }, 10000);

                    // Check connection status every 30 seconds
                    this.statusInterval = setInterval(() => {
                        this.checkConnectionStatus();
                    }, 30000);
                },

                stopPolling() {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                    if (this.statusInterval) {
                        clearInterval(this.statusInterval);
                        this.statusInterval = null;
                    }
                },

                scrollToBottom() {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                },

                getInitials(name) {
                    return name.split(' ')
                        .map(word => word.charAt(0))
                        .join('')
                        .substring(0, 2)
                        .toUpperCase();
                },

                getAvatarColor(name) {
                    const colors = [
                        '#e91e63', '#9c27b0', '#673ab7', '#3f51b5',
                        '#2196f3', '#03a9f4', '#00bcd4', '#009688',
                        '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b',
                        '#ffc107', '#ff9800', '#ff5722', '#795548'
                    ];
                    
                    let hash = 0;
                    for (let i = 0; i < name.length; i++) {
                        hash = name.charCodeAt(i) + ((hash << 5) - hash);
                    }
                    
                    return colors[Math.abs(hash) % colors.length];
                },

                formatTime(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const diffTime = Math.abs(now - date);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                    if (diffDays === 1) {
                        return 'Hoje';
                    } else if (diffDays === 2) {
                        return 'Ontem';
                    } else if (diffDays <= 7) {
                        return date.toLocaleDateString('pt-BR', { weekday: 'short' });
                    } else {
                        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
                    }
                },

                formatMessageTime(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleTimeString('pt-BR', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                }
            }
        }).mount('#whatsapp-web-app');
    </script>
</x-admin::layouts>

