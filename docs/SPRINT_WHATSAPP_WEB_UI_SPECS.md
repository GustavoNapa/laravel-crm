# QUARKIONS IA – SPRINT "WHATSAPP WEB UI"

## Objetivo
Implementar interface visual no estilo WhatsApp Web para contatos e chats com integração Evolution API.

## Especificações Técnicas

### Back-end – Endpoint unificado para WhatsApp Inbox

**Route GET /quarkions/whatsapp/inbox**
Retorna:
```json
{
  "conversations": [
    {
      "id": "abc123",
      "name": "Alfredo Eckenfels",
      "avatar": "https://…",
      "lastMessage": "Ok, combinado",
      "unread": 4,
      "updatedAt": "2025-06-30T21:26:00Z"
    }
  ],
  "selected": { … }
}
```

**Route POST /quarkions/whatsapp/send**
Body: conversation_id, text|audio|image

**Broadcast WhatsAppMessageReceived**
Canal: wa.{cliente_id}

**Repository WhatsappConversationRepository**
- allByClient() (paginação, ordenado desc updated_at)
- history($conversationId)

### Front-end – Layout "WhatsApp Web"

**Tecnologia:** Vue 3 + Tailwind + Laravel Mix

**Componente <WhatsappInbox>**
```html
<div class="flex h-full">
  <!-- Sidebar -->
  <aside class="w-80 border-r overflow-y-auto">
    <input v-model="search" placeholder="Buscar ou começar um chat" />
    <ConversationItem
      v-for="chat in filtered"
      :key="chat.id"
      :conversation="chat"
      :active="chat.id===activeId"
      @click="select(chat.id)" />
  </aside>

  <!-- Chat -->
  <section class="flex-1 flex flex-col">
    <ChatHeader :conversation="active" />
    <ChatBody :messages="messages" @scroll-bottom="loadMore" />
    <ChatFooter @send="sendMessage" />
  </section>
</div>
```

**WebSocket / Echo**
```js
Echo.private(`wa.${clientId}`)
    .listen('.WhatsAppMessageReceived', ({conversation, message}) => {
       inbox.update(conversation, message)
    })
```

### UX Detalhes
- Skeletons enquanto carrega lista/histórico
- Badge verde para não lidas ({{ chat.unread }})
- Auto-scroll ao final ao receber nova mensagem
- Indicador de status (conectando / conectado / offline) ao lado do avatar

### Integração Evolution API
- GET /conversations → popula sidebar
- GET /messages/{conversationId} → popula ChatBody (infinite scroll)
- POST /messages → envio
- Poll caso WebSocket indisponível (/events?since=) como fallback

### Testes
```bash
php artisan test --filter=WhatsappInboxTest
npm run test:unit
npx cypress run --spec cypress/e2e/wa_ui.cy.js
```

**Critérios:**
- ✓ Sidebar carrega 20+ conversas
- ✓ Selecionar conversa → histórico renderiza
- ✓ Mensagem enviada aparece no painel e no Evolution (mock)

### Commit & Push
```bash
git add .
git commit -m "feat: WhatsApp Inbox UI estilo Web + integração Evolution"
git push
gh pr create --fill --web
```

## Recursos de Referência
- Docs Evolution v2.2.3 (/conversations, /messages, WebSocket)
- Layout/ícones WhatsApp Web para inspiração
- Tailwind componetização (flex, overflow)

## Datasources
- Evolution API (base URL .env)
- Broadcast wa.{cliente_id} (websocket)

## Documentação
- Adicionar "WhatsApp Web UI" em docs/agenda_whatsapp.md
- Capturas de tela novas em /docs/img/wa_ui_*.png
- Atualizar todo.md ao concluir cada passo

