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
        
        if (request()->wantsJson()) {
            return response()->json($agendamentos);
        }
        
        return view('agenda.index', compact('agendamentos'));
    }

    public function create()
    {
        $leads = LeadQuarkions::all();
        return response()->json(['leads' => $leads]);
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

        return response()->json(['message' => 'Agendamento criado com sucesso!'], 201);
    }

    public function show(Agenda $agenda)
    {
        $agenda->load('lead');
        return response()->json($agenda);
    }

    public function edit(Agenda $agenda)
    {
        $leads = LeadQuarkions::all();
        return response()->json(['agenda' => $agenda, 'leads' => $leads]);
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

        return response()->json(['message' => 'Agendamento atualizado com sucesso!']);
    }

    public function destroy(Agenda $agenda)
    {
        $agenda->delete();
        return response()->json(['message' => 'Agendamento excluído com sucesso!']);
    }
}
