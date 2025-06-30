<x-admin::layouts>
    <x-slot:title>
        Dashboard Agentes - Quarkions
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-2">
            <div class="flex cursor-pointer items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Dashboard Agentes IA
                </h1>
            </div>

            <p class="text-base text-gray-600 dark:text-gray-300">
                Visão geral dos agentes de inteligência artificial
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.quarkions.agentes.index') }}"
                class="transparent-button"
            >
                Ver Todos os Agentes
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-agentes-dashboard></quarkions-agentes-dashboard>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="quarkions-agentes-dashboard-template">
            <div class="dashboard-container">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
                    <div class="box-shadow rounded bg-white p-6 dark:bg-gray-900">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                <i class="text-2xl text-green-600 dark:text-green-300">✓</i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">@{{ stats.ativos }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Agentes Ativos</p>
                            </div>
                        </div>
                    </div>

                    <div class="box-shadow rounded bg-white p-6 dark:bg-gray-900">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                                <i class="text-2xl text-red-600 dark:text-red-300">✗</i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">@{{ stats.inativos }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Agentes Inativos</p>
                            </div>
                        </div>
                    </div>

                    <div class="box-shadow rounded bg-white p-6 dark:bg-gray-900">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                <i class="text-2xl text-blue-600 dark:text-blue-300">👥</i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">@{{ stats.total }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Total de Agentes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Agents -->
                <div class="box-shadow rounded bg-white dark:bg-gray-900">
                    <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Agentes Recentes
                        </h3>
                    </div>
                    
                    <div class="p-4">
                        <div v-if="loading" class="text-center py-8">
                            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
                            <p class="mt-4 text-gray-600 dark:text-gray-300">Carregando dados...</p>
                        </div>
                        
                        <div v-else-if="agentesRecentes.length === 0" class="text-center py-8">
                            <p class="text-gray-600 dark:text-gray-300">Nenhum agente encontrado</p>
                        </div>
                        
                        <div v-else class="space-y-4">
                            <div 
                                v-for="agente in agentesRecentes" 
                                :key="agente.id"
                                class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                            >
                                <div class="flex items-center">
                                    <div 
                                        class="flex h-10 w-10 items-center justify-center rounded-full"
                                        :class="tipoClass(agente.tipo)"
                                    >
                                        <i class="text-lg text-white">🤖</i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                            @{{ agente.nome }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            Tipo: @{{ agente.tipo }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <span 
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="statusClass(agente.ativo)"
                                    >
                                        @{{ agente.ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('quarkions-agentes-dashboard', {
                template: '#quarkions-agentes-dashboard-template',
                
                data() {
                    return {
                        stats: {
                            ativos: 0,
                            inativos: 0,
                            total: 0
                        },
                        agentesRecentes: [],
                        loading: false
                    };
                },

                mounted() {
                    this.loadDashboard();
                },

                methods: {
                    loadDashboard() {
                        this.loading = true;
                        
                        this.$http.get("{{ route('admin.quarkions.agentes.dashboard') }}")
                            .then(response => {
                                this.stats.ativos = response.data.agentesAtivos || 0;
                                this.stats.inativos = response.data.agentesInativos || 0;
                                this.stats.total = this.stats.ativos + this.stats.inativos;
                                this.agentesRecentes = response.data.agentesRecentes || [];
                            })
                            .catch(error => {
                                console.error('Erro ao carregar dashboard:', error);
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    },

                    tipoClass(tipo) {
                        const classes = {
                            'isis': 'bg-blue-500',
                            'bruna': 'bg-green-500',
                            'especialista': 'bg-orange-500'
                        };
                        return classes[tipo] || 'bg-gray-500';
                    },

                    statusClass(ativo) {
                        return ativo 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
