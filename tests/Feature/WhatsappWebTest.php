<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webkul\User\Models\User;

class WhatsappWebTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar role primeiro
        $role = \Webkul\User\Models\Role::create([
            'name' => 'Administrator',
            'description' => 'Administrator role',
            'permission_type' => 'all',
        ]);
        
        // Criar usuário para testes
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'status' => 1,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function it_can_access_whatsapp_web_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quarkions/whatsapp');

        $response->assertStatus(200);
        $response->assertSee('WhatsApp Web');
        $response->assertSee('whatsapp-web-app');
    }

    /** @test */
    public function it_can_fetch_conversations_api()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quarkions/whatsapp/conversations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'conversations' => [
                '*' => [
                    'id',
                    'name',
                    'avatar',
                    'lastMessage',
                    'lastMessageTime',
                    'unreadCount',
                    'isGroup',
                    'remoteJid'
                ]
            ]
        ]);
    }

    /** @test */
    public function it_returns_empty_conversations_when_evolution_api_fails()
    {
        // Simular falha da Evolution API configurando URL inválida
        config(['whatsapp.evolution_base_url' => 'http://invalid-url']);

        $response = $this->actingAs($this->user)
            ->get('/admin/quarkions/whatsapp/conversations');

        $response->assertStatus(500);
        $response->assertJsonStructure([
            'success',
            'message',
            'conversations'
        ]);
        
        $this->assertFalse($response->json('success'));
        $this->assertEquals([], $response->json('conversations'));
    }

    /** @test */
    public function it_can_search_conversations()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quarkions/whatsapp/conversations?search=test');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'conversations',
            'total'
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_whatsapp_routes()
    {
        $response = $this->get('/admin/quarkions/whatsapp');
        $response->assertRedirect('/admin/login');

        $response = $this->get('/admin/quarkions/whatsapp/conversations');
        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function whatsapp_web_page_contains_required_vue_elements()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/quarkions/whatsapp');

        $content = $response->getContent();
        
        // Verificar se contém elementos Vue necessários
        $this->assertStringContainsString('id="whatsapp-web-app"', $content);
        $this->assertStringContainsString('v-for="conversation in (whatsapp ? whatsapp.conversations : [])"', $content);
        $this->assertStringContainsString('v-if="loading"', $content);
        $this->assertStringContainsString('@click="selectConversation(conversation)"', $content);
        
        // Verificar se contém scripts necessários
        $this->assertStringContainsString('initWhatsappInbox', $content);
        $this->assertStringContainsString('loadConversations', $content);
    }

    /** @test */
    public function it_handles_conversation_selection_api()
    {
        // Primeiro, obter uma conversa válida
        $conversationsResponse = $this->actingAs($this->user)
            ->get('/admin/quarkions/whatsapp/conversations');

        if ($conversationsResponse->json('success') && !empty($conversationsResponse->json('conversations'))) {
            $firstConversation = $conversationsResponse->json('conversations')[0];
            $conversationId = $firstConversation['id'];

            $response = $this->actingAs($this->user)
                ->get("/admin/quarkions/whatsapp/conversations/{$conversationId}");

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'messages'
            ]);
        } else {
            $this->markTestSkipped('Nenhuma conversa disponível para teste');
        }
    }

    /** @test */
    public function it_validates_evolution_api_configuration()
    {
        // Verificar se as configurações necessárias estão presentes
        $this->assertNotEmpty(config('whatsapp.evolution_base_url'));
        $this->assertNotEmpty(config('whatsapp.evolution_token'));
        $this->assertNotEmpty(config('whatsapp.instance_name'));
    }

    /** @test */
    public function whatsapp_service_can_be_instantiated()
    {
        $service = new \App\Services\EvolutionChatService();
        $this->assertInstanceOf(\App\Services\EvolutionChatService::class, $service);
    }
}

