<x-admin::layouts>
    <x-slot name="title">
        WhatsApp Inbox
    </x-slot>

    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar de Conversas -->
        <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header da Sidebar -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Conversas</h2>
                    <div class="flex items-center space-x-2">
                        <span v-if="status === 'open'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                            Conectado
                        </span>
                        <span v-else-if="status === 'connecting'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <div class="w-2 h-2 bg-yellow-400 rounded-full mr-1 animate-pulse"></div>
                            Conectando...
                        </span>
                        <span v-else class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <div class="w-2 h-2 bg-red-400 rounded-full mr-1"></div>
                            Desconectado
                        </span>
                    </div>
                </div>
                
                <!-- Busca -->
                <div class="relative">
                    <input 
                        v-model="searchQuery"
                        @input="searchConversations"
                        type="text" 
                        placeholder="Buscar conversas..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <!-- Filtros -->
                <div class="flex space-x-2 mt-3">
                    <button 
                        @click="setFilter('all')"
                        :class="activeFilter === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                    >
                        Todas ({{ stats.total_conversations || 0 }})
                    </button>
                    <button 
                        @click="setFilter('unread')"
                        :class="activeFilter === 'unread' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                        class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                    >
                        Não lidas ({{ stats.unread_messages || 0 }})
                    </button>
                </div>
            </div>

            <!-- Lista de Conversas -->
            <div class="flex-1 overflow-y-auto">
                <!-- Loading skeleton -->
                <div v-if="loadingConversations" class="p-4">
                    <div v-for="i in 5" :key="i" class="mb-4 animate-pulse">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                            <div class="flex-1">
                                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de conversas -->
                <div v-else-if="conversations.length > 0">
                    <div 
                        v-for="conversation in conversations" 
                        :key="conversation.lead_id"
                        @click="selectConversation(conversation)"
                        :class="selectedConversation?.lead_id === conversation.lead_id ? 'bg-blue-50 border-r-2 border-blue-500' : 'hover:bg-gray-50'"
                        class="p-4 border-b border-gray-100 cursor-pointer transition-colors"
                    >
                        <div class="flex items-center space-x-3">
                            <!-- Avatar -->
                            <div class="relative">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ getInitials(conversation.lead?.nome || 'U') }}
                                </div>
                                <div v-if="conversation.unread_count > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-bold">
                                    {{ conversation.unread_count > 9 ? '9+' : conversation.unread_count }}
                                </div>
                            </div>

                            <!-- Conteúdo -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-900 truncate">
                                        {{ conversation.lead?.nome || 'Usuário' }}
                                    </h3>
                                    <span class="text-xs text-gray-500">
                                        {{ formatTime(conversation.last_message_at) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-sm text-gray-600 truncate">
                                        {{ conversation.mensagem || 'Sem mensagens' }}
                                    </p>
                                    <div class="flex items-center space-x-1">
                                        <span v-if="conversation.lead?.status === 'ativo'" class="w-2 h-2 bg-green-400 rounded-full"></span>
                                        <span v-else-if="conversation.lead?.status === 'pendente'" class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                        <span v-else class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vazio -->
                <div v-else class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma conversa encontrada</h3>
                    <p class="mt-1 text-sm text-gray-500">Aguarde novas mensagens chegarem.</p>
                </div>
            </div>
        </div>

        <!-- Área Principal do Chat -->
        <div class="flex-1 flex flex-col">
            <!-- Conversa selecionada -->
            <div v-if="selectedConversation" class="flex-1 flex flex-col">
                <!-- Header do Chat -->
                <div class="bg-white border-b border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ getInitials(selectedConversation.lead?.nome || 'U') }}
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ selectedConversation.lead?.nome || 'Usuário' }}</h3>
                                <p class="text-sm text-gray-500">{{ selectedConversation.lead?.telefone }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select 
                                v-model="selectedConversation.lead.status"
                                @change="updateConversationStatus"
                                class="text-sm border border-gray-300 rounded-md px-3 py-1"
                            >
                                <option value="ativo">Ativo</option>
                                <option value="pendente">Pendente</option>
                                <option value="resolvido">Resolvido</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Área de Mensagens -->
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                    <!-- Loading mensagens -->
                    <div v-if="loadingMessages" class="space-y-4">
                        <div v-for="i in 3" :key="i" class="animate-pulse">
                            <div class="flex" :class="i % 2 === 0 ? 'justify-end' : 'justify-start'">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg" :class="i % 2 === 0 ? 'bg-gray-200' : 'bg-gray-200'">
                                    <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagens -->
                    <div v-else-if="messages.length > 0">
                        <div v-for="message in messages" :key="message.id" class="flex" :class="message.tipo === 'enviada' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg" :class="message.tipo === 'enviada' ? 'bg-blue-500 text-white' : 'bg-white border border-gray-200'">
                                <p class="text-sm">{{ message.mensagem }}</p>
                                <p class="text-xs mt-1 opacity-70">{{ formatTime(message.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Estado vazio -->
                    <div v-else class="text-center py-8">
                        <p class="text-gray-500">Nenhuma mensagem ainda. Inicie a conversa!</p>
                    </div>
                </div>

                <!-- Input de Mensagem -->
                <div class="bg-white border-t border-gray-200 p-4">
                    <div class="flex space-x-3">
                        <div class="flex-1">
                            <textarea 
                                v-model="newMessage"
                                @keydown.enter.prevent="sendMessage"
                                placeholder="Digite sua mensagem..."
                                rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            ></textarea>
                        </div>
                        <button 
                            @click="sendMessage"
                            :disabled="!newMessage.trim() || sendingMessage"
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg v-if="sendingMessage" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estado inicial -->
            <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Selecione uma conversa</h3>
                    <p class="mt-2 text-sm text-gray-500">Escolha uma conversa da lista para começar a responder.</p>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            app.component('quarkions-whatsapp-inbox', {
                template: '#quarkions-whatsapp-inbox-template',
                
                data() {
                    return {
                        // Estado da conexão
                        status: 'connecting',
                        
                        // Conversas
                        conversations: [],
                        selectedConversation: null,
                        loadingConversations: true,
                        
                        // Mensagens
                        messages: [],
                        loadingMessages: false,
                        
                        // Busca e filtros
                        searchQuery: '',
                        activeFilter: 'all',
                        
                        // Envio de mensagem
                        newMessage: '',
                        sendingMessage: false,
                        
                        // Estatísticas
                        stats: {
                            total_conversations: 0,
                            unread_messages: 0
                        },
                        
                        // Intervalos
                        statusInterval: null,
                        conversationsInterval: null
                    };
                },

                mounted() {
                    this.loadConversations();
                    this.checkStatus();
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
                            this.loadingConversations = true;
                            
                            const params = new URLSearchParams({
                                search: this.searchQuery,
                                unread_only: this.activeFilter === 'unread' ? '1' : '0'
                            });

                            const response = await fetch(`{{ route('admin.quarkions.whatsapp.conversations') }}?${params}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                }
                            });

                            const data = await response.json();
                            
                            if (data.success) {
                                this.conversations = data.data.data || [];
                                this.stats = data.stats || {};
                            } else {
                                console.error('Erro ao carregar conversas:', data.message);
                            }
                        } catch (error) {
                            console.error('Erro ao carregar conversas:', error);
                        } finally {
                            this.loadingConversations = false;
                        }
                    },

                    async selectConversation(conversation) {
                        try {
                            this.selectedConversation = conversation;
                            this.loadingMessages = true;
                            this.messages = [];

                            const response = await fetch(`{{ route('admin.quarkions.whatsapp.conversation.history', ':id') }}`.replace(':id', conversation.lead_id), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                }
                            });

                            const data = await response.json();
                            
                            if (data.success) {
                                this.messages = data.messages.data || [];
                                this.selectedConversation = data.conversation;
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
                        if (!this.newMessage.trim() || !this.selectedConversation || this.sendingMessage) {
                            return;
                        }

                        try {
                            this.sendingMessage = true;

                            const response = await fetch(`{{ route('admin.quarkions.whatsapp.send-message') }}`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                },
                                body: JSON.stringify({
                                    lead_id: this.selectedConversation.lead_id,
                                    message: this.newMessage
                                })
                            });

                            const data = await response.json();
                            
                            if (data.success) {
                                this.messages.push(data.message);
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
                            this.sendingMessage = false;
                        }
                    },

                    async checkStatus() {
                        try {
                            const response = await fetch(`{{ route('admin.quarkions.whatsapp.status') }}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                }
                            });

                            const data = await response.json();
                            this.status = data.state || 'close';
                        } catch (error) {
                            console.error('Erro ao verificar status:', error);
                            this.status = 'error';
                        }
                    },

                    async updateConversationStatus() {
                        if (!this.selectedConversation) return;

                        try {
                            const response = await fetch(`{{ route('admin.quarkions.whatsapp.update-status', ':id') }}`.replace(':id', this.selectedConversation.lead_id), {
                                method: 'PATCH',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                },
                                body: JSON.stringify({
                                    status: this.selectedConversation.lead.status
                                })
                            });

                            const data = await response.json();
                            
                            if (!data.success) {
                                console.error('Erro ao atualizar status:', data.message);
                            }
                        } catch (error) {
                            console.error('Erro ao atualizar status:', error);
                        }
                    },

                    searchConversations() {
                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            this.loadConversations();
                        }, 500);
                    },

                    setFilter(filter) {
                        this.activeFilter = filter;
                        this.loadConversations();
                    },

                    startPolling() {
                        // Verificar status a cada 30 segundos
                        this.statusInterval = setInterval(() => {
                            this.checkStatus();
                        }, 30000);

                        // Atualizar conversas a cada 10 segundos
                        this.conversationsInterval = setInterval(() => {
                            this.loadConversations();
                        }, 10000);
                    },

                    stopPolling() {
                        if (this.statusInterval) {
                            clearInterval(this.statusInterval);
                        }
                        if (this.conversationsInterval) {
                            clearInterval(this.conversationsInterval);
                        }
                    },

                    scrollToBottom() {
                        if (this.$refs.messagesContainer) {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        }
                    },

                    getInitials(name) {
                        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    },

                    formatTime(timestamp) {
                        if (!timestamp) return '';
                        const date = new Date(timestamp);
                        const now = new Date();
                        const diff = now - date;
                        
                        if (diff < 60000) { // menos de 1 minuto
                            return 'agora';
                        } else if (diff < 3600000) { // menos de 1 hora
                            return Math.floor(diff / 60000) + 'm';
                        } else if (diff < 86400000) { // menos de 1 dia
                            return Math.floor(diff / 3600000) + 'h';
                        } else {
                            return date.toLocaleDateString('pt-BR');
                        }
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>

