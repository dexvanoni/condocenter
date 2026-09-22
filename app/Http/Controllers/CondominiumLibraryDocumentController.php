<?php

namespace App\Http\Controllers;

use App\Models\CondominiumLibraryDocument;
use App\Models\InternalRegulation;
use App\Services\LibraryDocumentRegulationSync;
use App\Services\LibraryDocumentTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CondominiumLibraryDocumentController extends Controller
{
    public function index(Request $request, LibraryDocumentRegulationSync $regulationSync)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $condominiumId = (int) $user->tenantCondominiumId();

        $regulationSync->syncForCondominium($condominiumId);

        $documents = CondominiumLibraryDocument::query()
            ->forCondominium($condominiumId)
            ->active()
            ->ordered()
            ->get();

        $selectedId = $request->integer('doc');
        $selected = $selectedId
            ? $documents->firstWhere('id', $selectedId)
            : $documents->first();

        if ($selected && $selected->isRegulation()) {
            $selected->setRelation(
                'internalRegulation',
                InternalRegulation::with(['history' => fn ($q) => $q->orderByDesc('id')->limit(5)])
                    ->find($selected->internal_regulation_id)
            );
        }

        $canManage = $user->hasRole('Administrador') || $user->hasRole('Síndico');

        return view('library-documents.index', [
            'documents' => $documents,
            'selected' => $selected,
            'canManage' => $canManage,
        ]);
    }

    public function manage(LibraryDocumentRegulationSync $regulationSync)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->ensureCanManage($user);

        $condominiumId = (int) $user->tenantCondominiumId();
        $regulationSync->syncForCondominium($condominiumId);

        $documents = CondominiumLibraryDocument::query()
            ->forCondominium($condominiumId)
            ->ordered()
            ->get();

        return view('library-documents.manage', compact('documents'));
    }

    public function store(Request $request, LibraryDocumentTextExtractor $extractor)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->ensureCanManage($user);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'file' => 'required|file|mimes:pdf,txt|max:20480',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $condominiumId = (int) $user->tenantCondominiumId();
        $file = $validated['file'];
        $storedName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs(
            "condominium_library/{$condominiumId}",
            $storedName,
            'local'
        );

        $searchText = $extractor->fromUploadedFile($file);

        CondominiumLibraryDocument::create([
            'condominium_id' => $condominiumId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'source' => CondominiumLibraryDocument::SOURCE_UPLOAD,
            'file_path' => $path,
            'file_mime' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'search_text' => $searchText,
            'sort_order' => $validated['sort_order'] ?? 100,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('library-documents.manage')
            ->with('success', 'Documento adicionado à biblioteca.');
    }

    public function update(Request $request, int $documentId, LibraryDocumentTextExtractor $extractor)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->ensureCanManage($user);

        $document = $this->findDocumentForUser($documentId, $user);

        if ($document->isRegulation()) {
            abort(403, 'O regimento interno é editado na tela dedicada de regimento.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'file' => 'nullable|file|mimes:pdf,txt|max:20480',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        $document->title = $validated['title'];
        $document->description = $validated['description'] ?? null;
        $document->sort_order = $validated['sort_order'] ?? $document->sort_order;
        $document->is_active = $request->has('is_active');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $document->deleteStoredFile();
            $storedName = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs(
                "condominium_library/{$document->condominium_id}",
                $storedName,
                'local'
            );
            $document->file_path = $path;
            $document->file_mime = $file->getMimeType();
            $document->file_size = $file->getSize();
            $document->search_text = $extractor->fromUploadedFile($file);
        }

        $document->save();

        return redirect()
            ->route('library-documents.manage')
            ->with('success', 'Documento atualizado.');
    }

    public function destroy(int $documentId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->ensureCanManage($user);

        $document = $this->findDocumentForUser($documentId, $user);

        if ($document->isRegulation()) {
            abort(403, 'O regimento interno não pode ser removido da biblioteca.');
        }

        $document->deleteStoredFile();
        $document->delete();

        return redirect()
            ->route('library-documents.manage')
            ->with('success', 'Documento removido.');
    }

    public function file(int $documentId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $document = $this->findDocumentForUser($documentId, $user);

        if (!$document->file_path || !Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        $filename = Str::slug($document->title).'.'.pathinfo($document->file_path, PATHINFO_EXTENSION);

        return response()->file(
            Storage::disk('local')->path($document->file_path),
            [
                'Content-Type' => $document->file_mime ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }

    private function ensureCanManage(\App\Models\User $user): void
    {
        if (!$user->hasRole('Administrador') && !$user->hasRole('Síndico')) {
            abort(403, 'Apenas administradores e síndicos podem gerenciar documentos.');
        }
    }

    private function findDocumentForUser(int $documentId, \App\Models\User $user): CondominiumLibraryDocument
    {
        return CondominiumLibraryDocument::query()
            ->forCondominium((int) $user->tenantCondominiumId())
            ->whereKey($documentId)
            ->firstOrFail();
    }
}
