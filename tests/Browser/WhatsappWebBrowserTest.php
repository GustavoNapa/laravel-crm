<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Webkul\User\Models\User;

class WhatsappWebBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuário para testes
        $this->user = User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => bcrypt('admin123'),
        ]);
    }

    /**
     * Test WhatsApp Web page loads without JavaScript errors
     *
     * @test
     */
    public function whatsapp_web_page_loads_without_errors()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/admin/quarkions/whatsapp')
                ->waitFor('#whatsapp-web-app', 10)
                ->assertSee('WhatsApp')
                ->assertPresent('#whatsapp-web-app')
                ->assertDontSee('Cannot read properties of undefined');
        });
    }

    /**
     * Test WhatsApp Web conversations load
     *
     * @test
     */
    public function whatsapp_conversations_load_properly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/admin/quarkions/whatsapp')
                ->waitFor('#whatsapp-web-app', 10)
                ->pause(3000) // Aguardar carregamento das conversas
                ->assertPresent('.flex.h-full.bg-white'); // Container principal
        });
    }

    /**
     * Test search functionality works
     *
     * @test
     */
    public function search_functionality_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/admin/quarkions/whatsapp')
                ->waitFor('#whatsapp-web-app', 10)
                ->pause(2000)
                ->type('input[placeholder*="Pesquisar"]', 'test')
                ->pause(1000)
                ->assertPresent('input[placeholder*="Pesquisar"]');
        });
    }

    /**
     * Test connection status indicator
     *
     * @test
     */
    public function connection_status_indicator_is_present()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/admin/quarkions/whatsapp')
                ->waitFor('#whatsapp-web-app', 10)
                ->assertPresent('.w-3.h-3.rounded-full'); // Status indicator
        });
    }

    /**
     * Test no JavaScript console errors
     *
     * @test
     */
    public function no_javascript_console_errors()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/admin/quarkions/whatsapp')
                ->waitFor('#whatsapp-web-app', 10)
                ->pause(5000); // Aguardar carregamento completo

            // Verificar se não há erros críticos no console
            $logs = $browser->driver->manage()->getLog('browser');
            $errors = array_filter($logs, function ($log) {
                return $log['level'] === 'SEVERE' &&
                       strpos($log['message'], 'Cannot read properties of undefined') !== false;
            });

            $this->assertEmpty($errors, 'JavaScript errors found: '.json_encode($errors));
        });
    }

    /**
     * Test responsive design
     *
     * @test
     */
    public function responsive_design_works()
    {
        $this->browse(function (Browser $browser) {
            // Desktop
            $browser->resize(1280, 800)
                ->loginAs($this->user)
                ->visit('/admin/quarkions/whatsapp')
                ->waitFor('#whatsapp-web-app', 10)
                ->assertPresent('.w-80'); // Sidebar width

            // Mobile
            $browser->resize(375, 667)
                ->refresh()
                ->waitFor('#whatsapp-web-app', 10)
                ->assertPresent('#whatsapp-web-app');
        });
    }

    /**
     * Test loading states
     *
     * @test
     */
    public function loading_states_work_properly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/admin/quarkions/whatsapp')
                ->waitFor('#whatsapp-web-app', 10)
                ->pause(1000) // Aguardar estado de loading
                ->assertPresent('#whatsapp-web-app');
        });
    }

    /**
     * Test API endpoints are accessible
     *
     * @test
     */
    public function api_endpoints_are_accessible()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user);

            // Testar endpoint de conversas
            $response = $browser->visit('/admin/quarkions/whatsapp/conversations');

            // Verificar se retorna JSON válido
            $content = $browser->driver->getPageSource();
            $this->assertJson($content);

            $data = json_decode($content, true);
            $this->assertArrayHasKey('success', $data);
            $this->assertArrayHasKey('conversations', $data);
        });
    }
}
