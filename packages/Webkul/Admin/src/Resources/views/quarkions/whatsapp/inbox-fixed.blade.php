<x-admin::layouts>
    <x-slot name="title">
        WhatsApp Inbox - Quarkions IA
    </x-slot>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-whatsapp-inbox></quarkions-whatsapp-inbox>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="quarkions-whatsapp-inbox-template">
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
                                Todas (@{{ stats.total_conversations || 0 }})
                            </button>
                            <button 
                                @click="setFilter('unread')"
                                :class="activeFilter === 'unread' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                                class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                            >
                                Não lidas (@{{ stats.unread_messages || 0 }})
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
                        <div v-else-if="conversations && conversations.length > 0">
                            <div 
                                v-for="conversation in conversations" 
                                :key="conversation.lead_id || conversation.id"
                                @click="selectConversation(conversation)"
                                :class="selectedConversation?.lead_id === conversation.lead_id ? 'bg-blue-50 border-r-2 border-blue-500' : 'hover:bg-gray-50'"
                                class="p-4 border-b border-gray-100 cursor-pointer transition-colors"
                            >
                                <div class="flex items-center space-x-3">
                                    <!-- Avatar -->
                                    <div class="relative">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                            @{{ getInitials(conversation.lead?.nome || conversation.nome || 'U') }}
                                        </div>
                                        <div v-if="conversation.unread_count > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-bold">
                                            @{{ conversation.unread_count > 9 ? '9+' : conversation.unread_count }}
                                        </div>
                                    </div>

                                    <!-- Conteúdo -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium text-gray-900 truncate">
                                                @{{ conversation.lead?.nome || conversation.nome || 'Usuário' }}
                                            </h3>
                                            <span class="text-xs text-gray-500">
                                                @{{ formatTime(conversation.last_message_at || conversation.updated_at) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between mt-1">
                                            <p class="text-sm text-gray-600 truncate">
                                                @{{ conversation.mensagem || conversation.last_message || 'Sem mensagens' }}
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
                                        @{{ getInitials(selectedConversation.lead?.nome || selectedConversation.nome || 'U') }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">@{{ selectedConversation.lead?.nome || selectedConversation.nome || 'Usuário' }}</h3>
                                        <p class="text-sm text-gray-500">@{{ selectedConversation.lead?.telefone || selectedConversation.telefone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensagens -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4">
                            <div v-if="loadingMessages" class="text-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                                <p class="text-sm text-gray-500 mt-2">Carregando mensagens...</p>
                            </div>
                            <div v-else-if="messages && messages.length > 0">
                                <div 
                                    v-for="message in messages" 
                                    :key="message.id"
                                    :class="message.from_me ? 'justify-end' : 'justify-start'"
                                    class="flex mb-4"
                                >
                                    <div 
                                        :class="message.from_me ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900'"
                                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                                    >
                                        <p class="text-sm">@{{ message.mensagem || message.body }}</p>
                                        <p class="text-xs mt-1 opacity-75">@{{ formatTime(message.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <p class="text-gray-500">Nenhuma mensagem encontrada</p>
                            </div>
                        </div>

                        <!-- Input de nova mensagem -->
                        <div class="bg-white border-t border-gray-200 p-4">
                            <form @submit.prevent="sendMessage" class="flex space-x-3">
                                <input 
                                    v-model="newMessage"
                                    type="text" 
                                    placeholder="Digite sua mensagem..." 
                                    class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :disabled="sendingMessage"
                                >
                                <button 
                                    type="submit" 
                                    :disabled="!newMessage.trim() || sendingMessage"
                                    class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="sendingMessage">Enviando...</span>
                                    <span v-else>Enviar</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Estado inicial -->
                    <div v-else class="flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">Selecione uma conversa</h3>
                            <p class="mt-2 text-sm text-gray-500">Escolha uma conversa da lista para começar a chat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            // Aguardar que o DOM e o Vue estejam carregados
            document.addEventListener('DOMContentLoaded', function() {
                // Tentar registrar o componente com retry
                function registerComponent() {
                    if (typeof app !== 'undefined' && app.component) {
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
                                    
                                    // Filtros e busca
                                    activeFilter: 'all',
                                    searchQuery: '',
                                    
                                    // Estatísticas
                                    stats: {
                                        total_conversations: 0,
                                        unread_messages: 0
                                    },
                                    
                                    // Nova mensagem
                                    newMessage: '',
                                    sendingMessage: false
                                };
                            },
                            
                            mounted() {
                                this.loadConversations();
                                this.checkConnectionStatus();
                                
                                // Polling para atualizações
                                setInterval(() => {
                                    this.loadConversations();
                                }, 30000); // A cada 30 segundos
                            },
                            
                            methods: {
                                async loadConversations() {
                                    try {
                                        this.loadingConversations = true;
                                        const response = await this.$http.get("{{ route('admin.quarkions.whatsapp.conversations') }}");
                                        
                                        if (response.data && response.data.conversations) {
                                            this.conversations = response.data.conversations;
                                            this.stats = response.data.stats || {};
                                            console.log('Conversas carregadas:', this.conversations.length);
                                        } else {
                                            console.warn('Formato de resposta inesperado:', response.data);
                                            this.conversations = [];
                                        }
                                    } catch (error) {
                                        console.error('Erro ao carregar conversas:', error);
                                        this.conversations = [];
                                        this.$emitter.emit('add-flash', { 
                                            type: 'error', 
                                            message: 'Erro ao carregar conversas' 
                                        });
                                    } finally {
                                        this.loadingConversations = false;
                                    }
                                },
                                
                                async checkConnectionStatus() {
                                    try {
                                        const response = await this.$http.get("{{ route('admin.quarkions.whatsapp.status') }}");
                                        this.status = response.data.status || 'disconnected';
                                    } catch (error) {
                                        console.error('Erro ao verificar status:', error);
                                        this.status = 'disconnected';
                                    }
                                },
                                
                                async selectConversation(conversation) {
                                    this.selectedConversation = conversation;
                                    await this.loadMessages(conversation.lead_id || conversation.id);
                                },
                                
                                async loadMessages(conversationId) {
                                    try {
                                        this.loadingMessages = true;
                                        const response = await this.$http.get(`{{ route('admin.quarkions.whatsapp.messages', ['id' => '__ID__']) }}`.replace('__ID__', conversationId));
                                        
                                        if (response.data && Array.isArray(response.data.messages)) {
                                            this.messages = response.data.messages;
                                        } else {
                                            console.warn('Formato de resposta de mensagens inesperado:', response.data);
                                            this.messages = [];
                                        }
                                    } catch (error) {
                                        console.error('Erro ao carregar mensagens:', error);
                                        this.messages = [];
                                        this.$emitter.emit('add-flash', { 
                                            type: 'error', 
                                            message: 'Erro ao carregar mensagens' 
                                        });
                                    } finally {
                                        this.loadingMessages = false;
                                    }
                                },
                                
                                async sendMessage() {
                                    if (!this.newMessage.trim() || !this.selectedConversation) return;
                                    
                                    try {
                                        this.sendingMessage = true;
                                        const response = await this.$http.post("{{ route('admin.quarkions.whatsapp.send-message') }}", {
                                            phone: this.selectedConversation.lead?.telefone || this.selectedConversation.telefone,
                                            message: this.newMessage.trim()
                                        });
                                        
                                        // Adicionar mensagem à lista
                                        if (response.data.success) {
                                            this.messages.push({
                                                id: Date.now(),
                                                mensagem: this.newMessage,
                                                from_me: true,
                                                created_at: new Date().toISOString()
                                            });
                                            this.newMessage = '';
                                            
                                            this.$emitter.emit('add-flash', { 
                                                type: 'success', 
                                                message: 'Mensagem enviada!' 
                                            });
                                        }
                                    } catch (error) {
                                        console.error('Erro ao enviar mensagem:', error);
                                        this.$emitter.emit('add-flash', { 
                                            type: 'error', 
                                            message: 'Erro ao enviar mensagem' 
                                        });
                                    } finally {
                                        this.sendingMessage = false;
                                    }
                                },
                                
                                setFilter(filter) {
                                    this.activeFilter = filter;
                                    this.loadConversations();
                                },
                                
                                searchConversations() {
                                    // Implementar busca local ou via API
                                    console.log('Buscando por:', this.searchQuery);
                                },
                                
                                getInitials(name) {
                                    if (!name) return 'U';
                                    return name.split(' ')
                                        .map(word => word.charAt(0))
                                        .join('')
                                        .toUpperCase()
                                        .substring(0, 2);
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
                    } else {
                        // Retry após 100ms se o app ainda não estiver disponível
                        setTimeout(registerComponent, 100);
                    }
                }
                
                registerComponent();
            });
        </script>
    @endPushOnce
</x-admin::layouts>
