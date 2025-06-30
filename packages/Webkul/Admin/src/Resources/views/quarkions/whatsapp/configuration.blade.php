<x-admin::layouts>
    <x-slot:title>
        Configurações WhatsApp - Quarkions
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-2">
            <div class="flex cursor-pointer items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Configurações WhatsApp & Agentes IA
                </h1>
            </div>

            <p class="text-base text-gray-600 dark:text-gray-300">
                Configure as integrações com Evolution API, OpenAI e ElevenLabs
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.configuration.index', 'general/whatsapp') }}"
                class="primary-button"
            >
                Abrir Configurações Completas
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-whatsapp-config></quarkions-whatsapp-config>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="quarkions-whatsapp-config-template">
            <div class="config-container">
                <!-- Configuration Cards -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Evolution API Card -->
                    <div class="box-shadow rounded bg-white dark:bg-gray-900">
                        <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Evolution API
                                </h3>
                                <span 
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="evolutionStatus.class"
                                >
                                    @{{ evolutionStatus.text }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        URL Base da API
                                    </label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @{{ evolutionConfig.baseUrl || 'Não configurado' }}
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Instância
                                    </label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @{{ evolutionConfig.instanceName || 'Não configurado' }}
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status da Conexão
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <div 
                                            class="h-2 w-2 rounded-full"
                                            :class="connectionStatus.color"
                                        ></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            @{{ connectionStatus.text }}
                                        </span>
                                        <button
                                            @click="testConnection"
                                            :disabled="testing"
                                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200"
                                        >
                                            @{{ testing ? 'Testando...' : 'Testar' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Agents Card -->
                    <div class="box-shadow rounded bg-white dark:bg-gray-900">
                        <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Agentes de IA
                            </h3>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        OpenAI
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <div 
                                            class="h-2 w-2 rounded-full"
                                            :class="aiConfig.openai.configured ? 'bg-green-500' : 'bg-red-500'"
                                        ></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            @{{ aiConfig.openai.configured ? 'Configurado' : 'Não configurado' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Modelo: @{{ aiConfig.openai.model || 'Não definido' }}
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        ElevenLabs
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <div 
                                            class="h-2 w-2 rounded-full"
                                            :class="aiConfig.elevenlabs.configured ? 'bg-green-500' : 'bg-red-500'"
                                        ></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            @{{ aiConfig.elevenlabs.configured ? 'Configurado' : 'Não configurado' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Settings Card -->
                    <div class="box-shadow rounded bg-white dark:bg-gray-900">
                        <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Configurações de Mensagens
                            </h3>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Atraso entre Mensagens
                                    </label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @{{ messageConfig.delay }} segundos
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Intervalo de Follow-up
                                    </label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @{{ messageConfig.followupInterval }} horas
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Máximo de Tentativas
                                    </label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @{{ messageConfig.maxAttempts }} tentativas
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Hours Card -->
                    <div class="box-shadow rounded bg-white dark:bg-gray-900">
                        <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Horário Comercial
                            </h3>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Horário de Funcionamento
                                    </label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @{{ businessHours.start }}:00 às @{{ businessHours.end }}:00
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status Atual
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <div 
                                            class="h-2 w-2 rounded-full"
                                            :class="currentHourStatus.color"
                                        ></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            @{{ currentHourStatus.text }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 box-shadow rounded bg-white p-6 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Ações Rápidas
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <a
                            href="{{ route('admin.configuration.index', 'general/whatsapp/evolution_api') }}"
                            class="flex items-center justify-center rounded-lg border border-gray-300 p-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Configurar Evolution API
                        </a>
                        
                        <a
                            href="{{ route('admin.configuration.index', 'general/whatsapp/ai_agents') }}"
                            class="flex items-center justify-center rounded-lg border border-gray-300 p-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Configurar Agentes IA
                        </a>
                        
                        <a
                            href="{{ route('admin.configuration.index', 'general/whatsapp/message_settings') }}"
                            class="flex items-center justify-center rounded-lg border border-gray-300 p-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Configurar Mensagens
                        </a>
                        
                        <a
                            href="{{ route('admin.quarkions.agentes.dashboard') }}"
                            class="flex items-center justify-center rounded-lg border border-gray-300 p-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Dashboard Agentes
                        </a>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('quarkions-whatsapp-config', {
                template: '#quarkions-whatsapp-config-template',
                
                data() {
                    return {
                        evolutionConfig: {
                            baseUrl: '',
                            instanceName: '',
                            enabled: false
                        },
                        aiConfig: {
                            openai: {
                                configured: false,
                                model: ''
                            },
                            elevenlabs: {
                                configured: false
                            }
                        },
                        messageConfig: {
                            delay: 0,
                            followupInterval: 0,
                            maxAttempts: 0
                        },
                        businessHours: {
                            start: 8,
                            end: 20
                        },
                        connectionStatus: {
                            text: 'Não testado',
                            color: 'bg-gray-400'
                        },
                        testing: false
                    };
                },

                computed: {
                    evolutionStatus() {
                        if (!this.evolutionConfig.enabled) {
                            return {
                                text: 'Desabilitado',
                                class: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                            };
                        }
                        
                        if (!this.evolutionConfig.baseUrl || !this.evolutionConfig.instanceName) {
                            return {
                                text: 'Não Configurado',
                                class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                            };
                        }
                        
                        return {
                            text: 'Configurado',
                            class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                        };
                    },

                    currentHourStatus() {
                        const now = new Date();
                        const currentHour = now.getHours();
                        const isBusinessHour = currentHour >= this.businessHours.start && currentHour < this.businessHours.end;
                        
                        return {
                            text: isBusinessHour ? 'Horário Comercial' : 'Fora do Horário',
                            color: isBusinessHour ? 'bg-green-500' : 'bg-red-500'
                        };
                    }
                },

                mounted() {
                    this.loadConfiguration();
                },

                methods: {
                    loadConfiguration() {
                        // Carregar configurações do sistema
                        this.evolutionConfig = {
                            baseUrl: '{{ core()->getConfigData("general.whatsapp.evolution_api.base_url") }}',
                            instanceName: '{{ core()->getConfigData("general.whatsapp.evolution_api.instance_name") }}',
                            enabled: {{ core()->getConfigData("general.whatsapp.evolution_api.enable") ? 'true' : 'false' }}
                        };

                        this.aiConfig = {
                            openai: {
                                configured: '{{ core()->getConfigData("general.whatsapp.ai_agents.openai_api_key") }}' !== '',
                                model: '{{ core()->getConfigData("general.whatsapp.ai_agents.openai_model") }}'
                            },
                            elevenlabs: {
                                configured: '{{ core()->getConfigData("general.whatsapp.ai_agents.elevenlabs_api_key") }}' !== ''
                            }
                        };

                        this.messageConfig = {
                            delay: {{ core()->getConfigData("general.whatsapp.message_settings.message_delay") ?? 15 }},
                            followupInterval: {{ core()->getConfigData("general.whatsapp.message_settings.followup_interval_hours") ?? 24 }},
                            maxAttempts: {{ core()->getConfigData("general.whatsapp.message_settings.max_followup_attempts") ?? 3 }}
                        };

                        this.businessHours = {
                            start: {{ core()->getConfigData("general.whatsapp.business_hours.start_hour") ?? 8 }},
                            end: {{ core()->getConfigData("general.whatsapp.business_hours.end_hour") ?? 20 }}
                        };
                    },

                    testConnection() {
                        if (!this.evolutionConfig.baseUrl) {
                            alert('Configure a URL base da Evolution API primeiro');
                            return;
                        }

                        this.testing = true;
                        this.connectionStatus = {
                            text: 'Testando...',
                            color: 'bg-yellow-400'
                        };

                        this.$http.get("{{ route('admin.quarkions.whatsapp.test-connection') }}")
                            .then(response => {
                                if (response.data.success) {
                                    this.connectionStatus = {
                                        text: 'Conectado (' + (response.data.status || 'online') + ')',
                                        color: 'bg-green-500'
                                    };
                                } else {
                                    this.connectionStatus = {
                                        text: 'Falha na conexão',
                                        color: 'bg-red-500'
                                    };
                                    console.error('Connection test failed:', response.data.error);
                                }
                            })
                            .catch(error => {
                                this.connectionStatus = {
                                    text: 'Erro no teste',
                                    color: 'bg-red-500'
                                };
                                console.error('Connection test error:', error);
                            })
                            .finally(() => {
                                this.testing = false;
                            });
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
