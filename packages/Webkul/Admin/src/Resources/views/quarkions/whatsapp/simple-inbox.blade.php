<x-admin::layouts>
    <x-slot name="title">
        WhatsApp Inbox - Teste
    </x-slot>

    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">WhatsApp Inbox</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <p>Esta é uma versão simplificada do WhatsApp Inbox para testar se o básico funciona.</p>
            
            <div class="mt-4">
                <button onclick="testApi()" class="bg-blue-500 text-white px-4 py-2 rounded">
                    Testar API
                </button>
            </div>
            
            <div id="results" class="mt-4"></div>
        </div>
    </div>

    <script>
        async function testApi() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Testando API...</p>';
            
            try {
                const response = await fetch('/admin/quarkions/whatsapp/status', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                resultsDiv.innerHTML = '<h3>Status da API:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultsDiv.innerHTML = '<p style="color: red;">Erro: ' + error.message + '</p>';
            }
        }
    </script>
</x-admin::layouts>
