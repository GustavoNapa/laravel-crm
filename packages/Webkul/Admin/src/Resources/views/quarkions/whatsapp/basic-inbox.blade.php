<x-admin::layouts>
    <x-slot name="title">
        WhatsApp - Quarkions
    </x-slot>

    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">WhatsApp Inbox</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <quarkions-whatsapp-basic-test></quarkions-whatsapp-basic-test>
        </div>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="quarkions-whatsapp-basic-test-template"
        >
            <div>
                <p>Carregando WhatsApp Inbox...</p>
                <div class="mt-4">
                    <button @click="testConnection" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Testar Conexão
                    </button>
                </div>
                <div v-if="status" class="mt-4">
                    <h3>Status:</h3>
                    <pre>@{{ JSON.stringify(status, null, 2) }}</pre>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('quarkions-whatsapp-basic-test', {
                template: '#quarkions-whatsapp-basic-test-template',
                
                data() {
                    return {
                        status: null
                    };
                },
                
                methods: {
                    async testConnection() {
                        try {
                            const response = await fetch('/admin/quarkions/whatsapp/status', {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            });
                            
                            this.status = await response.json();
                        } catch (error) {
                            this.status = {
                                error: error.message
                            };
                        }
                    }
                },
                
                mounted() {
                    this.testConnection();
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
