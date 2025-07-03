<x-admin::layouts>
    <x-slot name="title">
        WhatsApp - Status Simples
    </x-slot>

    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">WhatsApp Status</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <p>Testando conexão...</p>
            <div id="results" class="mt-4"></div>
        </div>
    </div>

    <script>
        // Teste sem Vue - apenas JavaScript puro
        async function testWhatsAppStatus() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Carregando...</p>';
            
            try {
                const response = await fetch('/admin/quarkions/whatsapp/status', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                resultsDiv.innerHTML = '<h3>Sucesso!</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultsDiv.innerHTML = '<p style="color: red;">Erro: ' + error.message + '</p>';
            }
        }
        
        // Executar quando a página carregar
        document.addEventListener('DOMContentLoaded', testWhatsAppStatus);
    </script>
</x-admin::layouts>
