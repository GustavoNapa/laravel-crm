<?php

namespace Webkul\Admin\Http\Controllers;

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\AgentesController;
use Illuminate\Http\Request;

class QuarkionsController extends Controller
{
    protected $agendaController;
    protected $whatsappController;
    protected $agentesController;

    public function __construct(
        AgendaController $agendaController,
        WhatsAppController $whatsappController,
        AgentesController $agentesController
    ) {
        $this->agendaController = $agendaController;
        $this->whatsappController = $whatsappController;
        $this->agentesController = $agentesController;
    }

    /**
     * Agenda methods
     */
    public function agendaIndex()
    {
        return view('admin::quarkions.agenda.index');
    }

    public function agendaCreate()
    {
        return view('admin::quarkions.agenda.create');
    }

    public function agendaStore(Request $request)
    {
        return $this->agendaController->store($request);
    }

    public function agendaShow($id)
    {
        return view('admin::quarkions.agenda.show', ['id' => $id]);
    }

    public function agendaEdit($id)
    {
        return view('admin::quarkions.agenda.edit', ['id' => $id]);
    }

    public function agendaUpdate(Request $request, $id)
    {
        return $this->agendaController->update($request, $id);
    }

    public function agendaDestroy($id)
    {
        return $this->agendaController->destroy($id);
    }

    /**
     * WhatsApp methods
     */
    public function whatsappIndex()
    {
        return view('admin::quarkions.whatsapp.index');
    }

    public function whatsappChat($leadId)
    {
        return view('admin::quarkions.whatsapp.chat', ['leadId' => $leadId]);
    }

    public function whatsappSendMessage(Request $request)
    {
        return $this->whatsappController->sendMessage($request);
    }

    public function whatsappQrCode()
    {
        return view('admin::quarkions.whatsapp.qrcode');
    }

    public function whatsappWebhook(Request $request)
    {
        return $this->whatsappController->webhook($request);
    }

    public function whatsappCreateInstance(Request $request)
    {
        return $this->whatsappController->createInstance($request);
    }

    public function whatsappSetWebhook(Request $request)
    {
        return $this->whatsappController->setWebhook($request);
    }

    public function whatsappGetStatus()
    {
        return $this->whatsappController->getStatus();
    }

    /**
     * Agentes IA methods
     */
    public function agentesIndex()
    {
        return view('admin::quarkions.agentes.index');
    }

    public function agentesCreate()
    {
        return view('admin::quarkions.agentes.create');
    }

    public function agentesStore(Request $request)
    {
        return $this->agentesController->store($request);
    }

    public function agentesDashboard()
    {
        return view('admin::quarkions.agentes.dashboard');
    }

    public function agentesShow($id)
    {
        return view('admin::quarkions.agentes.show', ['id' => $id]);
    }

    public function agentesEdit($id)
    {
        return view('admin::quarkions.agentes.edit', ['id' => $id]);
    }

    public function agentesUpdate(Request $request, $id)
    {
        return $this->agentesController->update($request, $id);
    }

    public function agentesDestroy($id)
    {
        return $this->agentesController->destroy($id);
    }
}
