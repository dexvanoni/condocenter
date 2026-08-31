<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\StoreAccountabilityReportUploadRequest;
use App\Helpers\SidebarHelper;
use App\Models\AccountabilityReportUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountabilityReportUploadController extends Controller
{
    use ResolvesActiveCondominium;

    public function index(Request $request)
    {
        $user = $request->user();
        $condominiumId = $this->activeCondominiumId($user);

        if (!SidebarHelper::isFinancialSimplified($user)) {
            abort(403, 'Disponível apenas no ambiente financeiro simplificado.');
        }

        if (!$this->canViewUploads($user)) {
            abort(403, 'Acesso negado.');
        }

        $month = $request->integer('month') ?: null;
        $year = $request->integer('year') ?: null;

        $uploads = AccountabilityReportUpload::with(['uploader', 'reviewer'])
            ->where('condominium_id', $condominiumId)
            ->when($month, fn ($query) => $query->where('month', $month))
            ->when($year, fn ($query) => $query->where('year', $year))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(12)
            ->withQueryString();

        return view('finance.accountability-uploads.index', [
            'uploads' => $uploads,
            'month' => $month,
            'year' => $year,
            'monthNames' => AccountabilityReportUpload::MONTH_NAMES,
            'canManage' => SidebarHelper::isAdminOrSindico($user),
            'canApproveCouncil' => $this->canApproveCouncil($user),
            'years' => range(now()->year, now()->year - 10),
        ]);
    }

    public function store(StoreAccountabilityReportUploadRequest $request)
    {
        $user = $request->user();
        $condominiumId = $this->activeCondominiumId($user);

        if (!SidebarHelper::isFinancialSimplified($user)) {
            abort(403, 'Disponível apenas no ambiente financeiro simplificado.');
        }

        $existing = AccountabilityReportUpload::where('condominium_id', $condominiumId)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existing) {
            $existing->deleteStoredFile();
            $existing->delete();
        }

        $file = $request->file('file');
        $path = $file->store(
            "accountability/{$condominiumId}/{$request->year}/{$request->month}",
            'public'
        );

        AccountabilityReportUpload::create([
            'condominium_id' => $condominiumId,
            'uploaded_by' => $user->id,
            'month' => $request->month,
            'year' => $request->year,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'notes' => $request->notes,
            'council_status' => AccountabilityReportUpload::COUNCIL_STATUS_PENDING,
        ]);

        return redirect()
            ->route('accountability-uploads.index', [
                'month' => $request->month,
                'year' => $request->year,
            ])
            ->with('success', 'Prestação de contas enviada. Aguardando aprovação do Conselho Fiscal.');
    }

    public function approve(Request $request, AccountabilityReportUpload $upload)
    {
        $user = $request->user();

        if (!$this->canApproveCouncil($user)) {
            abort(403, 'Acesso negado.');
        }

        $this->authorizeUploadAccess($request, $upload);

        if (!$upload->isCouncilPending()) {
            return redirect()
                ->route('accountability-uploads.index')
                ->with('info', 'Este documento já foi analisado pelo Conselho Fiscal.');
        }

        $upload->update([
            'council_status' => AccountabilityReportUpload::COUNCIL_STATUS_APPROVED,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('accountability-uploads.index', [
                'month' => $upload->month,
                'year' => $upload->year,
            ])
            ->with('success', 'Prestação de contas aprovada pelo Conselho Fiscal.');
    }

    public function download(Request $request, AccountabilityReportUpload $upload)
    {
        $this->authorizeUploadAccess($request, $upload);

        $user = $request->user();

        if ($this->requiresCouncilApprovalForDownload($user) && !$upload->isCouncilApproved()) {
            abort(403, 'Este documento ainda aguarda aprovação do Conselho Fiscal.');
        }

        if (!Storage::disk('public')->exists($upload->file_path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('public')->download(
            $upload->file_path,
            $upload->original_filename
        );
    }

    public function destroy(Request $request, AccountabilityReportUpload $upload)
    {
        $user = $request->user();

        if (!SidebarHelper::isAdminOrSindico($user)) {
            abort(403, 'Acesso negado.');
        }

        $this->authorizeUploadAccess($request, $upload);

        $upload->deleteStoredFile();
        $upload->delete();

        return redirect()
            ->route('accountability-uploads.index')
            ->with('success', 'Arquivo removido com sucesso.');
    }

    private function canViewUploads($user): bool
    {
        if (SidebarHelper::isAdminOrSindico($user)) {
            return true;
        }

        return $user->can('view_accountability_reports')
            || $user->can('view_own_financial')
            || $user->isMorador()
            || $user->isConselhoFiscal();
    }

    private function canApproveCouncil($user): bool
    {
        if (!SidebarHelper::isFinancialSimplified($user)) {
            return false;
        }

        return $user->isConselhoFiscal() || $user->isAdmin();
    }

    private function requiresCouncilApprovalForDownload($user): bool
    {
        if (SidebarHelper::isAdminOrSindico($user) || $user->isConselhoFiscal()) {
            return false;
        }

        return true;
    }

    private function authorizeUploadAccess(Request $request, AccountabilityReportUpload $upload): void
    {
        $user = $request->user();

        $this->ensureResourceBelongsToActiveCondominium($user, (int) $upload->condominium_id);

        if (!$this->canViewUploads($user)) {
            abort(403, 'Acesso negado.');
        }
    }
}
