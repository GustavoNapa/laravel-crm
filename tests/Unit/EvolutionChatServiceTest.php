<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EvolutionChatService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class EvolutionChatServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar valores de teste
        Config::set('whatsapp.evolution_base_url', 'https://test-api.com');
        Config::set('whatsapp.evolution_token', 'test-token');
        Config::set('whatsapp.instance_name', 'test-instance');
        
        $this->service = new EvolutionChatService();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(EvolutionChatService::class, $this->service);
    }

    /** @test */
    public function it_can_find_chats_successfully()
    {
        // Mock da resposta da API
        $mockResponse = [
            'success' => true,
            'data' => [
                [
                    'id' => 'chat1',
                    'name' => 'Test Chat',
                    'lastMessage' => 'Hello',
                ]
            ]
        ];

        Http::fake([
            'https://test-api.com/chat/findChats/test-instance' => Http::response($mockResponse, 200)
        ]);

        $result = $this->service->findChats();

        $this->assertEquals($mockResponse, $result);
    }

    /** @test */
    public function it_handles_api_errors_gracefully()
    {
        Http::fake([
            'https://test-api.com/chat/findChats/test-instance' => Http::response([], 500)
        ]);

        $result = $this->service->findChats();

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_handles_network_exceptions()
    {
        Http::fake([
            'https://test-api.com/chat/findChats/test-instance' => function () {
                throw new \Exception('Network error');
            }
        ]);

        $result = $this->service->findChats();

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_formats_conversation_data_correctly()
    {
        $mockChats = [
            [
                'id' => 'chat1',
                'name' => 'Test User',
                'lastMessage' => [
                    'message' => 'Hello World',
                    'timestamp' => '2025-07-03T14:30:00Z'
                ],
                'unreadCount' => 5,
                'isGroup' => false
            ]
        ];

        $result = $this->service->formatConversationData($mockChats);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        
        $conversation = $result[0];
        $this->assertEquals('chat1', $conversation['id']);
        $this->assertEquals('Test User', $conversation['name']);
        $this->assertEquals('Hello World', $conversation['lastMessage']);
        $this->assertEquals(5, $conversation['unreadCount']);
        $this->assertFalse($conversation['isGroup']);
    }

    /** @test */
    public function it_handles_empty_chat_data()
    {
        $result = $this->service->formatConversationData([]);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function it_handles_malformed_chat_data()
    {
        $malformedData = [
            ['id' => 'chat1'], // Dados incompletos
            null, // Dados nulos
            'invalid', // Tipo inválido
        ];

        $result = $this->service->formatConversationData($malformedData);
        
        $this->assertIsArray($result);
        // Deve filtrar dados inválidos e retornar apenas dados válidos
    }

    /** @test */
    public function it_uses_correct_api_headers()
    {
        Http::fake();

        $this->service->findChats();

        Http::assertSent(function ($request) {
            return $request->hasHeader('apikey', 'test-token') &&
                   $request->hasHeader('Content-Type', 'application/json');
        });
    }

    /** @test */
    public function it_calls_correct_api_endpoint()
    {
        Http::fake();

        $this->service->findChats();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://test-api.com/chat/findChats/test-instance';
        });
    }

    /** @test */
    public function it_can_get_conversation_history()
    {
        $mockMessages = [
            [
                'id' => 'msg1',
                'message' => 'Hello',
                'timestamp' => '2025-07-03T14:30:00Z',
                'fromMe' => false
            ]
        ];

        Http::fake([
            'https://test-api.com/chat/findMessages/test-instance' => Http::response([
                'success' => true,
                'data' => $mockMessages
            ], 200)
        ]);

        $result = $this->service->getConversationHistory('chat1');

        $this->assertEquals($mockMessages, $result);
    }

    /** @test */
    public function it_can_send_message()
    {
        Http::fake([
            'https://test-api.com/message/sendText/test-instance' => Http::response([
                'success' => true,
                'messageId' => 'msg123'
            ], 200)
        ]);

        $result = $this->service->sendMessage('chat1', 'Hello World');

        $this->assertTrue($result['success']);
        $this->assertEquals('msg123', $result['messageId']);
    }

    /** @test */
    public function it_validates_required_configuration()
    {
        Config::set('whatsapp.evolution_base_url', '');
        
        $this->expectException(\Exception::class);
        new EvolutionChatService();
    }
}

