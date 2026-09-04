<?php

namespace App\Http\Controllers;

use App\Exports\OccurrenceBookExport;
use App\Http\Requests\AcknowledgeOccurrenceBookEntryRequest;
use App\Http\Requests\ExportOccurrenceBookRequest;
use App\Http\Requests\SaveOccurrenceBookCommentRequest;
use App\Http\Requests\StoreOccurrenceBookEntryRequest;
use App\Http\Requests\UpdateOccurrenceBookSettingsRequest;
use App\Models\OccurrenceBookEntry;
use App\Services\OccurrenceBookService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OccurrenceBookController extends Controller
{
    public function __construct(
        private readonly OccurrenceBookService $occurrenceBookService,
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $this->assertResidentAccess($user);
        $this->authorize('create', OccurrenceBookEntry::class);

        return view('occurrence-book.index', [
            'entries' => $this->occurrenceBookService->paginateForResident($user),
            'publicBookEnabled' => $this->occurrenceBookService->isPublicBookEnabled($user->tenantCondominiumId()),
        ]);
    }

    public function publicIndex(Request $request): View
    {
        $user = Auth::user();
        $this->assertPublicBookAccess($user);

        $filters = $request->only(['type', 'start_date', 'end_date', 'search']);

        return view('occurrence-book.public.index', [
            'entries' => $this->occurrenceBookService->paginatePublicBook($user, $filters),
            'filters' => $filters,
            'types' => OccurrenceBookEntry::TYPES,
        ]);
    }

    public function publicShow(OccurrenceBookEntry $entry): View
    {
        $user = Auth::user();
        $this->assertPublicBookAccess($user);
        $this->authorize('viewPublic', $entry);

        return view('occurrence-book.public.show', [
            'entry' => $entry,
        ]);
    }

    public function create(): View
    {
        $this->assertResidentAccess(Auth::user());
        $this->authorize('create', OccurrenceBookEntry::class);

        return view('occurrence-book.create', [
            'types' => OccurrenceBookEntry::TYPES,
        ]);
    }

    public function store(StoreOccurrenceBookEntryRequest $request): RedirectResponse
    {
        $entry = $this->occurrenceBookService->create(
            Auth::user(),
            $request->validated(),
            $request->file('photo')
        );

        return redirect()
            ->route('occurrence-book.show', $entry)
            ->with('success', 'Registro enviado ao síndico com sucesso. Você será notificado quando houver ciência.');
    }

    public function show(OccurrenceBookEntry $entry): View
    {
        $this->authorize('view', $entry);

        $entry->load(['author', 'unit', 'acknowledgedBy', 'syndicCommentedBy']);

        return view('occurrence-book.show', [
            'entry' => $entry,
            'isSyndicView' => Auth::user()->can('manage_occurrence_book'),
        ]);
    }

    public function manageIndex(Request $request): View
    {
        $this->assertSyndicAccess(Auth::user());
        $this->authorize('viewAny', OccurrenceBookEntry::class);

        $filters = $request->only(['type', 'start_date', 'end_date', 'status', 'search']);

        return view('occurrence-book.manage.index', [
            'entries' => $this->occurrenceBookService->paginateForSyndic(Auth::user(), $filters),
            'stats' => $this->occurrenceBookService->statsForSyndic(Auth::user()),
            'filters' => $filters,
            'types' => OccurrenceBookEntry::TYPES,
            'publicBookEnabled' => $this->occurrenceBookService->isPublicBookEnabled(Auth::user()->tenantCondominiumId()),
        ]);
    }

    public function updateSettings(UpdateOccurrenceBookSettingsRequest $request): RedirectResponse
    {
        $this->assertSyndicAccess(Auth::user());

        $enabled = $request->boolean('occurrence_book_public_enabled');
        $this->occurrenceBookService->updatePublicSetting(Auth::user(), $enabled);

        return redirect()
            ->route('occurrence-book.manage.index')
            ->with('success', $enabled
                ? 'Livro de Ocorrências exposto aos moradores e agregados (sem identificação do autor).'
                : 'Livro de Ocorrências oculto para moradores e agregados.');
    }

    public function manageShow(OccurrenceBookEntry $entry): View
    {
        $this->authorize('view', $entry);

        $entry->load(['author', 'unit', 'acknowledgedBy', 'syndicCommentedBy']);

        return view('occurrence-book.manage.show', [
            'entry' => $entry,
        ]);
    }

    public function saveComment(SaveOccurrenceBookCommentRequest $request, OccurrenceBookEntry $entry): RedirectResponse
    {
        $this->assertSyndicAccess(Auth::user());

        $this->occurrenceBookService->saveSyndicComment(
            $entry,
            Auth::user(),
            $request->validated('syndic_comment'),
            $request->boolean('show_syndic_comment_publicly'),
        );

        return redirect()
            ->route('occurrence-book.manage.show', $entry)
            ->with('success', 'Comentário salvo com sucesso.');
    }

    public function acknowledge(AcknowledgeOccurrenceBookEntryRequest $request, OccurrenceBookEntry $entry): RedirectResponse
    {
        $this->assertSyndicAccess(Auth::user());
        $this->occurrenceBookService->acknowledge(
            $entry,
            Auth::user(),
            $request->validated('acknowledgment_note')
        );

        return redirect()
            ->route('occurrence-book.manage.show', $entry)
            ->with('success', 'Ciência registrada. O morador foi notificado.');
    }

    public function exportExcel(ExportOccurrenceBookRequest $request): BinaryFileResponse
    {
        $this->assertSyndicAccess(Auth::user());
        $entries = $this->occurrenceBookService->exportCollection(Auth::user(), $request->filters());
        $filename = 'livro-ocorrencias-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new OccurrenceBookExport($entries), $filename);
    }

    public function exportPdf(ExportOccurrenceBookRequest $request)
    {
        $this->assertSyndicAccess(Auth::user());
        $user = Auth::user();
        $entries = $this->occurrenceBookService->exportCollection($user, $request->filters());
        $condominium = $user->condominium;

        $pdf = Pdf::loadView('occurrence-book.export.pdf', [
            'entries' => $entries,
            'condominium' => $condominium,
            'filters' => $request->filters(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('livro-ocorrencias-'.now()->format('Y-m-d-His').'.pdf');
    }

    private function assertResidentAccess($user): void
    {
        if ($user->isAdmin() && !$user->isSindico()) {
            abort(403, 'Livro de Ocorrências indisponível para administradores da plataforma.');
        }
    }

    private function assertSyndicAccess($user): void
    {
        if (!$user->isSindico()) {
            abort(403, 'Somente o perfil Síndico pode acessar o Livro de Ocorrências.');
        }
    }

    private function assertPublicBookAccess($user): void
    {
        if ($user->isAdmin() && !$user->isSindico()) {
            abort(403, 'Livro de Ocorrências indisponível para administradores da plataforma.');
        }

        $this->authorize('viewPublicBook', OccurrenceBookEntry::class);
    }
}
