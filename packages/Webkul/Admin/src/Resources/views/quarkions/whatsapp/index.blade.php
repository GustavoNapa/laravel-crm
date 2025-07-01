<x-admin::layouts>
    <x-slot:title>
        WhatsApp IA - Quarkions
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-2">
            <div class="flex cursor-pointer items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    WhatsApp IA
                </h1>
            </div>

            <p class="text-base text-gray-600 dark:text-gray-300">
                Gerencie conversas e configurações do WhatsApp
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.quarkions.whatsapp.qrcode') }}"
                class="primary-button"
            >
                QR Code
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-whatsapp-index></quarkions-whatsapp-index>
        </div>
    </div>

@push('scripts')
    <script type="text/x-template" id="quarkions-whatsapp-index-template">
        <div class="whatsapp-container">
            <div class="status-card">
                <div class="card">
                    <div class="card-header">
                        <h3>Status da Conexão</h3>
                    </div>
                    <div class="card-body">
                        <div class="status-indicator" :class="statusClass">
                            <i :class="statusIcon"></i>
                            <span>@{{ statusText }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="conversations-list mt-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Conversas Recentes</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="loading" class="text-center">
                            <i class="icon-spinner animate-spin"></i>
                            Carregando conversas...
                        </div>
                        
                        <div v-else-if="conversas.length === 0" class="text-center text-muted">
                            Nenhuma conversa encontrada
                        </div>
                        
                        <div v-else class="conversation-item" v-for="conversa in conversas" :key="conversa.id">
                            <div class="conversation-header">
                                <h4>@{{ conversa.lead?.nome || 'Lead não identificado' }}</h4>
                                <span class="timestamp">@{{ formatDate(conversa.criado_em) }}</span>
                            </div>
                            <div class="conversation-preview">
                                @{{ conversa.mensagem }}
                            </div>
                            <div class="conversation-actions">
                                <a 
                                    :href="chatUrl(conversa.lead_id)" 
                                    class="btn btn-sm btn-primary"
                                >
                                    Abrir Chat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('quarkions-whatsapp-index', {
            template: '#quarkions-whatsapp-index-template',
            
            data() {
                return {
                    conversas: [],
                    status: 'disconnected',
                    loading: false
                };
            },

            computed: {
                statusClass() {
                    return {
                        'status-online': this.status === 'open',
                        'status-offline': this.status === 'close',
                        'status-connecting': this.status === 'connecting'
                    };
                },

                statusIcon() {
                    switch (this.status) {
                        case 'open': return 'icon-check-circle';
                        case 'close': return 'icon-x-circle';
                        default: return 'icon-clock';
                    }
                },

                statusText() {
                    switch (this.status) {
                        case 'open': return 'Conectado';
                        case 'close': return 'Desconectado';
                        default: return 'Conectando...';
                    }
                }
            },

            mounted() {
                this.loadConversas();
                this.checkStatus();
            },

            methods: {
                loadConversas() {
                    this.loading = true;
                    
                    fetch("{{ route('admin.quarkions.whatsapp.index') }}", {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.conversas = data.data || [];
                    })
                    .catch(error => {
                        console.error('Erro ao carregar conversas:', error);
                        this.conversas = [];
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                },

                checkStatus() {
                    fetch("{{ route('admin.quarkions.whatsapp.status') }}", {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.status = data.state || 'close';
                    })
                    .catch(error => {
                        console.error('Erro ao verificar status:', error);
                        this.status = 'error';
                    });
                },

                chatUrl(leadId) {
                    return "{{ route('admin.quarkions.whatsapp.chat', ':leadId') }}".replace(':leadId', leadId);
                },

                formatDate(date) {
                    return new Date(date).toLocaleString('pt-BR');
                }
            }
        });
    </script>

    <style>
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .status-online { color: #28a745; }
        .status-offline { color: #dc3545; }
        .status-connecting { color: #ffc107; }
        
        .conversation-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .conversation-preview {
            color: #6c757d;
            margin-bottom: 12px;
        }
        
        .timestamp {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
@endpush

</x-admin::layouts>