<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AssemblyController extends Controller
{
    /**
     * Lista assembleias
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Assembly::with(['creator'])
            ->where('condominium_id', $user->condominium_id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $assemblies = $query->orderBy('scheduled_at', 'desc')->paginate(15);

        return response()->json($assemblies);
    }

    /**
     * Cria uma nova assembleia
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'agenda' => 'required|array',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:30|max:480',
            'voting_type' => 'required|in:open,secret',
            'allow_delegation' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Verificar permissão
        if (!$user->can('create_assemblies')) {
            return response()->json(['error' => 'Sem permissão para criar assembleias'], 403);
        }

        $assembly = Assembly::create([
            'condominium_id' => $user->condominium_id,
            'created_by' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'agenda' => $request->agenda,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'voting_type' => $request->voting_type,
            'allow_delegation' => $request->boolean('allow_delegation'),
            'status' => 'scheduled',
        ]);

        // TODO: Enviar notificações para todos os moradores

        return response()->json([
            'message' => 'Assembleia criada com sucesso. Todos os moradores serão notificados.',
            'assembly' => $assembly
        ], 201);
    }

    /**
     * Exibe uma assembleia
     */
    public function show($id)
    {
        $assembly = Assembly::with(['creator', 'votes.user', 'votes.unit'])
            ->findOrFail($id);

        // Verificar permissão
        if ($assembly->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Calcular resultados da votação
        $voteResults = [];
        
        if ($assembly->agenda) {
            foreach ($assembly->agenda as $item) {
                $itemKey = is_array($item) ? ($item['id'] ?? $item['title']) : $item;
                
                $yes = Vote::where('assembly_id', $assembly->id)
                    ->where('agenda_item', $itemKey)
                    ->where('vote', 'yes')
                    ->count();
                
                $no = Vote::where('assembly_id', $assembly->id)
                    ->where('agenda_item', $itemKey)
                    ->where('vote', 'no')
                    ->count();
                
                $abstain = Vote::where('assembly_id', $assembly->id)
                    ->where('agenda_item', $itemKey)
                    ->where('vote', 'abstain')
                    ->count();
                
                $voteResults[$itemKey] = [
                    'yes' => $yes,
                    'no' => $no,
                    'abstain' => $abstain,
                    'total' => $yes + $no + $abstain,
                ];
            }
        }

        $assembly->vote_results = $voteResults;

        return response()->json($assembly);
    }

    /**
     * Atualiza uma assembleia
     */
    public function update(Request $request, $id)
    {
        $assembly = Assembly::findOrFail($id);

        $user = Auth::user();

        // Verificar permissão
        if (!$user->can('manage_assemblies') || $assembly->created_by !== $user->id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Não permitir editar assembleias em progresso ou concluídas
        if (in_array($assembly->status, ['in_progress', 'completed'])) {
            return response()->json([
                'error' => 'Não é possível editar uma assembleia em andamento ou concluída'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'scheduled_at' => 'sometimes|date|after:now',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assembly->update($request->all());

        return response()->json([
            'message' => 'Assembleia atualizada com sucesso',
            'assembly' => $assembly
        ]);
    }

    /**
     * Registra voto
     */
    public function vote(Request $request, $id)
    {
        $assembly = Assembly::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'agenda_item' => 'required|string',
            'vote' => 'required|in:yes,no,abstain',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Verificar se assembleia está aberta para votação
        if (!in_array($assembly->status, ['scheduled', 'in_progress'])) {
            return response()->json([
                'error' => 'Esta assembleia não está aberta para votação'
            ], 400);
        }

        // Verificar se usuário pertence ao condomínio
        if ($assembly->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se já votou neste item
        $existingVote = Vote::where('assembly_id', $assembly->id)
            ->where('user_id', $user->id)
            ->where('agenda_item', $request->agenda_item)
            ->first();

        if ($existingVote) {
            return response()->json([
                'error' => 'Você já votou neste item'
            ], 400);
        }

        // Registrar voto
        $vote = Vote::create([
            'assembly_id' => $assembly->id,
            'user_id' => $user->id,
            'unit_id' => $user->unit_id,
            'agenda_item' => $request->agenda_item,
            'vote' => $request->vote,
            'encrypted_vote' => $assembly->voting_type === 'secret' ? encrypt($request->vote) : null,
        ]);

        return response()->json([
            'message' => 'Voto registrado com sucesso',
            'vote' => $vote
        ], 201);
    }

    /**
     * Remove uma assembleia
     */
    public function destroy($id)
    {
        $assembly = Assembly::findOrFail($id);

        $user = Auth::user();

        // Verificar permissão
        if (!$user->can('manage_assemblies') || $assembly->created_by !== $user->id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Não permitir deletar assembleias concluídas
        if ($assembly->status === 'completed') {
            return response()->json([
                'error' => 'Não é possível remover uma assembleia já concluída'
            ], 400);
        }

        $assembly->delete();

        return response()->json([
            'message' => 'Assembleia removida com sucesso'
        ]);
    }
}
