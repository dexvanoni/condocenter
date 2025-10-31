<?php

namespace App\Http\Controllers;

use App\Models\InternalRegulation;
use App\Models\InternalRegulationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InternalRegulationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the current internal regulation.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $regulation = InternalRegulation::with(['updatedBy', 'history.updatedBy'])
            ->byCondominium($user->condominium_id)
            ->active()
            ->first();

        // Se não existir regimento, verifica se o usuário é admin para criar
        if (!$regulation && ($user->hasRole('Administrador') || $user->hasRole('Síndico'))) {
            return redirect()->route('internal-regulations.create');
        }

        return view('internal-regulations.index', compact('regulation'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar se já existe regimento ativo
        $existingRegulation = InternalRegulation::byCondominium($user->condominium_id)
            ->active()
            ->first();

        if ($existingRegulation) {
            return redirect()->route('internal-regulations.index')
                ->with('warning', 'Já existe um regimento interno ativo para este condomínio.');
        }

        // Apenas administrador ou síndico pode criar
        if (!$user->hasRole('Administrador') && !$user->hasRole('Síndico')) {
            abort(403, 'Apenas administradores e síndicos podem criar o regimento interno.');
        }

        return view('internal-regulations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas administrador ou síndico pode criar
        if (!$user->hasRole('Administrador') && !$user->hasRole('Síndico')) {
            abort(403, 'Apenas administradores e síndicos podem criar o regimento interno.');
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'assembly_date' => 'nullable|date',
            'assembly_details' => 'nullable|string|max:255',
        ]);

        $validated['condominium_id'] = $user->condominium_id;
        $validated['updated_by'] = $user->id;
        $validated['is_active'] = true;
        $validated['version'] = 1;

        $regulation = InternalRegulation::create($validated);

        // Log da atividade
        $user->logActivity(
            'create',
            'internal_regulations',
            'Criou o regimento interno',
            ['regulation_id' => $regulation->id]
        );

        return redirect()->route('internal-regulations.index')
            ->with('success', 'Regimento interno criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas administrador ou síndico pode editar
        if (!$user->hasRole('Administrador') && !$user->hasRole('Síndico')) {
            abort(403, 'Apenas administradores e síndicos podem editar o regimento interno.');
        }

        $regulation = InternalRegulation::byCondominium($user->condominium_id)
            ->active()
            ->firstOrFail();

        return view('internal-regulations.edit', compact('regulation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas administrador ou síndico pode atualizar
        if (!$user->hasRole('Administrador') && !$user->hasRole('Síndico')) {
            abort(403, 'Apenas administradores e síndicos podem atualizar o regimento interno.');
        }

        $regulation = InternalRegulation::byCondominium($user->condominium_id)
            ->active()
            ->firstOrFail();

        $validated = $request->validate([
            'content' => 'required|string',
            'assembly_date' => 'nullable|date',
            'assembly_details' => 'nullable|string|max:255',
            'changes_summary' => 'nullable|string',
        ]);

        $regulation->content = $validated['content'];
        $regulation->assembly_date = $validated['assembly_date'] ?? $regulation->assembly_date;
        $regulation->assembly_details = $validated['assembly_details'] ?? $regulation->assembly_details;
        $regulation->updated_by = $user->id;
        $regulation->save();

        // Atualizar o histórico com o resumo das mudanças se fornecido
        if (!empty($validated['changes_summary'])) {
            $latestHistory = InternalRegulationHistory::where('internal_regulation_id', $regulation->id)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($latestHistory) {
                $latestHistory->changes_summary = $validated['changes_summary'];
                $latestHistory->save();
            }
        }

        // Log da atividade
        $user->logActivity(
            'update',
            'internal_regulations',
            'Atualizou o regimento interno (Versão ' . $regulation->version . ')',
            ['regulation_id' => $regulation->id, 'version' => $regulation->version]
        );

        return redirect()->route('internal-regulations.index')
            ->with('success', 'Regimento interno atualizado com sucesso! Nova versão: ' . $regulation->version);
    }

    /**
     * Display history of changes.
     */
    public function history()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $regulation = InternalRegulation::byCondominium($user->condominium_id)
            ->active()
            ->first();

        if (!$regulation) {
            return redirect()->route('internal-regulations.index')
                ->with('warning', 'Nenhum regimento interno encontrado.');
        }

        $history = InternalRegulationHistory::where('internal_regulation_id', $regulation->id)
            ->with('updatedBy')
            ->orderBy('version', 'desc')
            ->get();

        return view('internal-regulations.history', compact('regulation', 'history'));
    }

    /**
     * Show a specific historical version.
     */
    public function showHistory($historyId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $history = InternalRegulationHistory::with(['updatedBy', 'internalRegulation'])
            ->where('condominium_id', $user->condominium_id)
            ->findOrFail($historyId);

        return view('internal-regulations.show-history', compact('history'));
    }

    /**
     * Export current regulation to PDF.
     */
    public function exportPdf()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $regulation = InternalRegulation::with(['updatedBy', 'condominium'])
            ->byCondominium($user->condominium_id)
            ->active()
            ->firstOrFail();

        $pdf = Pdf::loadView('internal-regulations.pdf', compact('regulation'));

        return $pdf->download('regimento-interno-v' . $regulation->version . '.pdf');
    }

    /**
     * Print current regulation.
     */
    public function print()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $regulation = InternalRegulation::with(['updatedBy', 'condominium'])
            ->byCondominium($user->condominium_id)
            ->active()
            ->firstOrFail();

        return view('internal-regulations.print', compact('regulation'));
    }
}
