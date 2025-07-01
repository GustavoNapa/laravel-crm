<x-admin::layouts>
    <x-slot:title>
        QR Code WhatsApp - Quarkions
    </x-slot>

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex flex-col gap-2">
            <div class="flex cursor-pointer items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    QR Code WhatsApp
                </h1>
            </div>

            <p class="text-base text-gray-600 dark:text-gray-300">
                Escaneie o QR Code para conectar ao WhatsApp
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.quarkions.whatsapp.index') }}"
                class="transparent-button"
            >
                @lang('admin::app.layouts.back')
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <quarkions-whatsapp-qrcode></quarkions-whatsapp-qrcode>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="quarkions-whatsapp-qrcode-template">
            <div class="qrcode-container">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <div class="text-center">
                        <div v-if="loading" class="py-8">
                            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
                            <p class="mt-4 text-gray-600 dark:text-gray-300">Carregando QR Code...</p>
                        </div>
                        
                        <div v-else-if="qrCode" class="py-4">
                            <img :src="qrCode" alt="QR Code WhatsApp" class="mx-auto max-w-sm rounded border">
                            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                                Escaneie este QR Code com o WhatsApp para conectar
                            </p>
                            <button @click="refreshQrCode" class="mt-4 primary-button">
                                Atualizar QR Code
                            </button>
                        </div>
                        
                        <div v-else-if="status && status.state === 'open'" class="py-8">
                            <div class="text-green-500">
                                <i class="text-6xl">✓</i>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white">
                                WhatsApp Conectado!
                            </h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                Sua instância do WhatsApp está ativa e funcionando.
                            </p>
                        </div>
                        
                        <div v-else-if="timeout" class="py-8">
                            <div class="text-yellow-500">
                                <i class="text-6xl">⏱</i>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white">
                                Timeout
                            </h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                {{ timeoutMessage }}
                            </p>
                            <button @click="tryAgain" class="mt-4 primary-button">
                                Tentar Novamente
                            </button>
                        </div>
                        
                        <div v-else class="py-8">
                            <div class="text-red-500">
                                <i class="text-6xl">✗</i>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white">
                                Erro ao carregar QR Code
                            </h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                Não foi possível gerar o QR Code. Tente novamente.
                            </p>
                            <button @click="loadQrCode" class="mt-4 primary-button">
                                Tentar Novamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('quarkions-whatsapp-qrcode', {
                template: '#quarkions-whatsapp-qrcode-template',
                
                data() {
                    return {
                        qrCode: null,
                        status: null,
                        loading: false,
                        timeout: false,
                        timeoutMessage: '',
                        maxAttempts: 18, // 3 minutos (18 * 10 segundos)
                        currentAttempt: 0,
                        attemptInterval: null
                    };
                },

                mounted() {
                    this.loadQrCode();
                },

                beforeUnmount() {
                    if (this.statusInterval) {
                        clearInterval(this.statusInterval);
                    }
                    if (this.attemptInterval) {
                        clearInterval(this.attemptInterval);
                    }
                },

                methods: {
                    loadQrCode() {
                        this.loading = true;
                        this.timeout = false;
                        this.currentAttempt = 0;
                        
                        // Limpar intervalos anteriores
                        if (this.statusInterval) {
                            clearInterval(this.statusInterval);
                        }
                        if (this.attemptInterval) {
                            clearInterval(this.attemptInterval);
                        }
                        
                        this.attemptToGetQrCode();
                    },

                    attemptToGetQrCode() {
                        if (this.currentAttempt >= this.maxAttempts) {
                            this.handleTimeout();
                            return;
                        }

                        fetch("{{ route('admin.quarkions.whatsapp.qrcode') }}", {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (data.connected) {
                                    // Já está conectado
                                    this.status = { state: 'open' };
                                    this.loading = false;
                                } else if (data.qrcode) {
                                    // QR Code obtido com sucesso
                                    this.qrCode = data.qrcode;
                                    this.loading = false;
                                    this.startStatusCheck();
                                } else {
                                    // Tentar novamente em 10 segundos
                                    this.scheduleNextAttempt();
                                }
                            } else if (data.timeout) {
                                this.handleTimeout();
                            } else {
                                this.scheduleNextAttempt();
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao carregar QR Code:', error);
                            this.scheduleNextAttempt();
                        });
                    },

                    scheduleNextAttempt() {
                        this.currentAttempt++;
                        
                        if (this.currentAttempt >= this.maxAttempts) {
                            this.handleTimeout();
                            return;
                        }

                        this.attemptInterval = setTimeout(() => {
                            this.attemptToGetQrCode();
                        }, 10000); // 10 segundos
                    },

                    handleTimeout() {
                        this.loading = false;
                        this.timeout = true;
                        this.timeoutMessage = 'Timeout ao gerar QR Code após 3 minutos. Deseja tentar novamente?';
                        
                        // Limpar intervalos
                        if (this.statusInterval) {
                            clearInterval(this.statusInterval);
                        }
                        if (this.attemptInterval) {
                            clearInterval(this.attemptInterval);
                        }
                    },

                    startStatusCheck() {
                        // Verificar status a cada 5 segundos
                        this.statusInterval = setInterval(() => {
                            this.checkStatus();
                        }, 5000);
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
                            this.status = data;
                            
                            // Se conectado, parar de verificar
                            if (data.state === 'open' && this.statusInterval) {
                                clearInterval(this.statusInterval);
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao verificar status:', error);
                        });
                    },

                    refreshQrCode() {
                        this.qrCode = null;
                        this.loadQrCode();
                    },

                    tryAgain() {
                        this.timeout = false;
                        this.timeoutMessage = '';
                        this.loadQrCode();
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
