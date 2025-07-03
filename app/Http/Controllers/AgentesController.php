<?php

namespace App\Http\Controllers;

use App\Models\Agentes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentesController extends Controller
{
    public function index()
    {
        $agentes = Agentes::paginate(15);

        if (request()->wantsJson()) {
            return response()->json($agentes);
        }

        return view('agentes.index', compact('agentes'));
    }

    public function create()
    {
        return response()->json(['types' => ['isis', 'bruna', 'especialista']]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'       => 'required|string|max:255',
            'tipo'       => 'required|in:isis,bruna,especialista',
            'voz_padrao' => 'nullable|string',
            'ativo'      => 'boolean',
        ]);

        Agentes::create([
            'id'         => Str::uuid(),
            'nome'       => $request->nome,
            'tipo'       => $request->tipo,
            'voz_padrao' => $request->voz_padrao,
            'ativo'      => $request->has('ativo'),
            'cliente_id' => 'default_client',
        ]);

        return response()->json(['message' => 'Agente criado com sucesso!'], 201);
    }

    public function show(Agentes $agente)
    {
        return response()->json($agente);
    }

    public function edit(Agentes $agente)
    {
        return response()->json(['agente' => $agente, 'types' => ['isis', 'bruna', 'especialista']]);
    }

    public function update(Request $request, Agentes $agente)
    {
        $request->validate([
            'nome'       => 'required|string|max:255',
            'tipo'       => 'required|in:isis,bruna,especialista',
            'voz_padrao' => 'nullable|string',
            'ativo'      => 'boolean',
        ]);

        $agente->update([
            'nome'       => $request->nome,
            'tipo'       => $request->tipo,
            'voz_padrao' => $request->voz_padrao,
            'ativo'      => $request->has('ativo'),
        ]);

        return response()->json(['message' => 'Agente atualizado com sucesso!']);
    }

    public function destroy(Agentes $agente)
    {
        $agente->delete();

        return response()->json(['message' => 'Agente excluído com sucesso!']);
    }

    public function dashboard()
    {
        $agentesAtivos = Agentes::where('ativo', true)->count();
        $agentesInativos = Agentes::where('ativo', false)->count();

        $agentesRecentes = Agentes::orderBy('id', 'desc')->take(5)->get();

        return response()->json([
            'agentesAtivos'   => $agentesAtivos,
            'agentesInativos' => $agentesInativos,
            'agentesRecentes' => $agentesRecentes,
        ]);
    }
}
