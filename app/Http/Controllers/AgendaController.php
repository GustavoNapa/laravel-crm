<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\LeadQuarkions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgendaController extends Controller
{
    public function index()
    {
        $agendamentos = Agenda::with('lead')->orderBy('data', 'desc')->paginate(15);
        return view('agenda.index', compact('agendamentos'));
    }

    public function create()
    {
        $leads = LeadQuarkions::all();
        return view('agenda.create', compact('leads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads_quarkions,id',
            'data' => 'required|date',
            'horario' => 'required|string',
            'status' => 'required|string',
            'observacoes' => 'nullable|string'
        ]);

        Agenda::create([
            'id' => Str::uuid(),
            'cliente_id' => 'default_client', // Implementar autenticação depois
            'lead_id' => $request->lead_id,
            'data' => $request->data,
            'horario' => $request->horario,
            'status' => $request->status,
            'observacoes' => $request->observacoes
        ]);

        return redirect()->route('agenda.index')->with('success', 'Agendamento criado com sucesso!');
    }

    public function show(Agenda $agenda)
    {
        $agenda->load('lead');
        return view('agenda.show', compact('agenda'));
    }

    public function edit(Agenda $agenda)
    {
        $leads = LeadQuarkions::all();
        return view('agenda.edit', compact('agenda', 'leads'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads_quarkions,id',
            'data' => 'required|date',
            'horario' => 'required|string',
            'status' => 'required|string',
            'observacoes' => 'nullable|string'
        ]);

        $agenda->update($request->all());

        return redirect()->route('agenda.index')->with('success', 'Agendamento atualizado com sucesso!');
    }

    public function destroy(Agenda $agenda)
    {
        $agenda->delete();
        return redirect()->route('agenda.index')->with('success', 'Agendamento excluído com sucesso!');
    }
}
