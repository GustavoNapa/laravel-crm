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
                        loading: false
                    };
                },

                mounted() {
                    this.loadQrCode();
                    this.checkStatus();
                    
                    // Atualizar status a cada 5 segundos
                    this.statusInterval = setInterval(() => {
                        this.checkStatus();
                    }, 5000);
                },

                beforeUnmount() {
                    if (this.statusInterval) {
                        clearInterval(this.statusInterval);
                    }
                },

                methods: {
                    loadQrCode() {
                        this.loading = true;
                        
                        this.$http.get("{{ route('admin.quarkions.whatsapp.qrcode') }}")
                            .then(response => {
                                this.qrCode = response.data.qrCode;
                                this.status = response.data.status;
                            })
                            .catch(error => {
                                console.error('Erro ao carregar QR Code:', error);
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    },

                    checkStatus() {
                        this.$http.get("{{ route('admin.quarkions.whatsapp.status') }}")
                            .then(response => {
                                this.status = response.data;
                                
                                // Se conectado, parar de verificar
                                if (response.data.state === 'open' && this.statusInterval) {
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
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
