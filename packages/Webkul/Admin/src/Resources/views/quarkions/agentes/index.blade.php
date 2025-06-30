<x-admin::layouts>
    <x-slot:title>
        Agentes IA - Quarkions
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-2">
            <div class="flex cursor-pointer items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Agentes IA
                </h1>
            </div>

            <p class="text-base text-gray-600 dark:text-gray-300">
                Gerencie seus agentes de inteligência artificial
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.quarkions.agentes.create') }}"
                class="primary-button"
            >
                @lang('admin::app.layouts.add')
                Agente
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-agentes-index></quarkions-agentes-index>
        </div>
    </div>

@push('scripts')
    <script type="text/x-template" id="quarkions-agentes-index-template">
        <div class="agentes-container">
            <div class="stats-cards mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="text-success">@{{ stats.ativos }}</h3>
                                <p>Agentes Ativos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="text-danger">@{{ stats.inativos }}</h3>
                                <p>Agentes Inativos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="text-primary">@{{ stats.total }}</h3>
                                <p>Total de Agentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="agentes-list">
                <div class="card">
                    <div class="card-header">
                        <h3>Lista de Agentes</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="loading" class="text-center">
                            <i class="icon-spinner animate-spin"></i>
                            Carregando agentes...
                        </div>
                        
                        <div v-else-if="agentes.length === 0" class="text-center text-muted">
                            Nenhum agente encontrado
                        </div>
                        
                        <div v-else class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Voz Padrão</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="agente in agentes" :key="agente.id">
                                        <td>@{{ agente.nome }}</td>
                                        <td>
                                            <span class="badge" :class="tipoClass(agente.tipo)">
                                                @{{ agente.tipo }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" :class="statusClass(agente.ativo)">
                                                @{{ agente.ativo ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td>@{{ agente.voz_padrao || '-' }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a 
                                                    :href="editUrl(agente.id)" 
                                                    class="btn btn-sm btn-primary"
                                                >
                                                    <i class="icon-edit"></i>
                                                </a>
                                                <button 
                                                    @click="deleteAgente(agente.id)"
                                                    class="btn btn-sm btn-danger"
                                                >
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('quarkions-agentes-index', {
            template: '#quarkions-agentes-index-template',
            
            data() {
                return {
                    agentes: [],
                    stats: {
                        ativos: 0,
                        inativos: 0,
                        total: 0
                    },
                    loading: false
                };
            },

            mounted() {
                this.loadAgentes();
            },

            methods: {
                loadAgentes() {
                    this.loading = true;
                    
                    this.$http.get("{{ route('admin.quarkions.agentes.index') }}")
                        .then(response => {
                            this.agentes = response.data.data || [];
                            this.calculateStats();
                        })
                        .catch(error => {
                            console.error('Erro ao carregar agentes:', error);
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                calculateStats() {
                    this.stats.total = this.agentes.length;
                    this.stats.ativos = this.agentes.filter(a => a.ativo).length;
                    this.stats.inativos = this.stats.total - this.stats.ativos;
                },

                tipoClass(tipo) {
                    const classes = {
                        'isis': 'badge-primary',
                        'bruna': 'badge-success',
                        'especialista': 'badge-warning'
                    };
                    return classes[tipo] || 'badge-secondary';
                },

                statusClass(ativo) {
                    return ativo ? 'badge-success' : 'badge-danger';
                },

                editUrl(id) {
                    return "{{ route('admin.quarkions.agentes.edit', ':id') }}".replace(':id', id);
                },

                deleteAgente(id) {
                    if (confirm('Tem certeza que deseja excluir este agente?')) {
                        this.$http.delete(`{{ route('admin.quarkions.agentes.destroy', ':id') }}`.replace(':id', id))
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: 'Agente excluído com sucesso!' });
                                this.loadAgentes();
                            })
                            .catch(error => {
                                console.error('Erro ao excluir agente:', error);
                                this.$emitter.emit('add-flash', { type: 'error', message: 'Erro ao excluir agente!' });
                            });
                    }
                }
            }
        });
    </script>

    <style>
        .stats-card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        
        .badge-primary { background-color: #007bff; color: white; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
    </style>
@endpush

</x-admin::layouts>
