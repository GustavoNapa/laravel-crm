<x-admin::layouts>
    <x-slot:title>
        WhatsApp Web
    </x-slot:title>

    <!-- WhatsApp Web Interface -->
    <div id="whatsapp-web-app" class="h-screen bg-gray-100 overflow-hidden">
        <div class="flex h-full bg-white">
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
                            <div :class="['w-3 h-3 rounded-full', connectionStatus === 'open' ? 'bg-green-500' : connectionStatus === 'connecting' ? 'bg-yellow-500' : 'bg-red-500']"></div>
                            <span class="text-xs text-gray-500">@{{ connectionStatus }}</span>
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
                                <div v-if="conversation.unreadCount > 0" 
                                     class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    @{{ conversation.unreadCount }}
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="ml-3 flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        @{{ conversation.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        @{{ formatTime(conversation.lastMessageTime) }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-500 truncate">
                                        @{{ conversation.lastMessage }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="filteredConversations.length === 0" class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma conversa</h3>
                            <p class="mt-1 text-sm text-gray-500">Comece uma nova conversa para ver aqui.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
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
                                <p class="text-xs text-gray-500">@{{ selectedConversation.isGroup ? 'Grupo' : 'Contato' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
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
                <div v-if="selectedConversation" class="flex-1 overflow-y-auto p-4 bg-gray-50" ref="messagesContainer">
                    <!-- Loading Messages -->
                    <div v-if="loadingMessages" class="flex justify-center p-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
                    </div>

                    <!-- Messages -->
                    <div v-else class="space-y-4">
                        <div 
                            v-for="message in messages" 
                            :key="message.id"
                            :class="[
                                'flex',
                                message.fromMe ? 'justify-end' : 'justify-start'
                            ]"
                        >
                            <div 
                                :class="[
                                    'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                                    message.fromMe 
                                        ? 'bg-green-500 text-white' 
                                        : 'bg-white text-gray-900 border border-gray-200'
                                ]"
                            >
                                <p class="text-sm">@{{ message.message }}</p>
                                <p :class="[
                                    'text-xs mt-1',
                                    message.fromMe ? 'text-green-100' : 'text-gray-500'
                                ]">
                                    @{{ formatTime(message.timestamp) }}
                                </p>
                            </div>
                        </div>

                        <!-- Empty Messages -->
                        <div v-if="messages.length === 0" class="text-center py-8">
                            <p class="text-gray-500">Nenhuma mensagem ainda</p>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div v-if="selectedConversation" class="bg-white p-4 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        <button class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <div class="flex-1">
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
                                'p-2 rounded-full',
                                newMessage.trim() && !sending 
                                    ? 'bg-green-500 text-white hover:bg-green-600' 
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                            ]"
                        >
                            <svg v-if="sending" class="w-5 h-5 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Empty Chat State -->
                <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">WhatsApp Web</h3>
                        <p class="mt-2 text-sm text-gray-500">Selecione uma conversa para começar a conversar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@pushOnce('scripts')
    <script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        function initWhatsappInbox() {
            // Check if Vue is available
            if (typeof Vue === 'undefined') {
                console.error('Vue is not loaded');
                return;
            }
            
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
                        connectionStatus: 'connecting'
                    };
                },
                computed: {
                    filteredConversations() {
                        if (!this.conversations || !Array.isArray(this.conversations)) {
                            return [];
                        }
                        
                        if (!this.searchQuery) {
                            return this.conversations;
                        }
                        
                        const query = this.searchQuery.toLowerCase();
                        return this.conversations.filter(conv => 
                            (conv.name && conv.name.toLowerCase().includes(query)) ||
                            (conv.lastMessage && conv.lastMessage.toLowerCase().includes(query))
                        );
                    }
                },
                methods: {
                    async loadConversations() {
                        try {
                            this.loading = true;
                            const response = await fetch('/admin/quarkions/whatsapp/conversations');
                            const data = await response.json();
                            
                            if (data.success) {
                                this.conversations = data.conversations || [];
                            } else {
                                console.error('Erro ao carregar conversas:', data.message);
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
                    },
                    
                    async loadMessages(conversationId) {
                        try {
                            this.loadingMessages = true;
                            const response = await fetch(`/admin/quarkions/whatsapp/conversations/${conversationId}`);
                            const data = await response.json();
                            
                            if (data.success) {
                                this.messages = data.messages || [];
                                this.$nextTick(() => {
                                    this.scrollToBottom();
                                });
                            } else {
                                console.error('Erro ao carregar mensagens:', data.message);
                            }
                        } catch (error) {
                            console.error('Erro ao carregar mensagens:', error);
                        } finally {
                            this.loadingMessages = false;
                        }
                    },
                    
                    async sendMessage() {
                        if (!this.newMessage.trim() || this.sending || !this.selectedConversation) {
                            return;
                        }
                        
                        try {
                            this.sending = true;
                            const response = await fetch('/admin/quarkions/whatsapp/send-message', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    remoteJid: this.selectedConversation.remoteJid,
                                    message: this.newMessage
                                })
                            });
                            
                            const data = await response.json();
                            
                            if (data.success) {
                                // Adicionar mensagem localmente
                                this.messages.push({
                                    id: Date.now(),
                                    message: this.newMessage,
                                    fromMe: true,
                                    timestamp: Date.now()
                                });
                                
                                this.newMessage = '';
                                this.$nextTick(() => {
                                    this.scrollToBottom();
                                });
                            } else {
                                alert('Erro ao enviar mensagem: ' + data.message);
                            }
                        } catch (error) {
                            console.error('Erro ao enviar mensagem:', error);
                            alert('Erro ao enviar mensagem');
                        } finally {
                            this.sending = false;
                        }
                    },
                    
                    async checkConnectionStatus() {
                        try {
                            const response = await fetch('/admin/quarkions/whatsapp/status');
                            const data = await response.json();
                            
                            if (data.success) {
                                this.connectionStatus = data.status;
                            }
                        } catch (error) {
                            this.connectionStatus = 'error';
                        }
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
                    
                    getInitials(name) {
                        return name.split(' ')
                            .map(word => word.charAt(0))
                            .join('')
                            .substring(0, 2)
                            .toUpperCase();
                    },
                    
                    formatTime(timestamp) {
                        if (!timestamp) return '';
                        
                        const date = new Date(timestamp * 1000);
                        const now = new Date();
                        
                        if (date.toDateString() === now.toDateString()) {
                            return date.toLocaleTimeString('pt-BR', { 
                                hour: '2-digit', 
                                minute: '2-digit' 
                            });
                        }
                        
                        return date.toLocaleDateString('pt-BR', { 
                            day: '2-digit', 
                            month: '2-digit' 
                        });
                    },
                    
                    scrollToBottom() {
                        const container = this.$refs.messagesContainer;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    }
                },
                
                async mounted() {
                    await this.loadConversations();
                    await this.checkConnectionStatus();
                    
                    // Polling para atualizar conversas
                    setInterval(() => {
                        this.loadConversations();
                    }, 10000);
                    
                    // Polling para status de conexão
                    setInterval(() => {
                        this.checkConnectionStatus();
                    }, 5000);
                }
            }).mount('#whatsapp-web-app');
        }
        
        // Aguardar Vue e app estarem disponíveis
        function waitForVue() {
            if (typeof Vue !== 'undefined') {
                initWhatsappInbox();
            } else {
                setTimeout(waitForVue, 100);
            }
        }
        
        waitForVue();
    });
    </script>
@endpushOnce
</x-admin::layouts>

