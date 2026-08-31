<?php

namespace App\Http\Controllers\Finance;

use App\Exports\AccountabilityExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Services\AccountabilityReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class AccountabilityReportController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly AccountabilityReportService $service
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('view_accountability_reports') && ! $user->can('view_financial_reports')) {
            abort(403);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $data = $this->service->generate($this->activeCondominiumId($user), $startDate, $endDate);

        $canViewDetails = \App\Helpers\SidebarHelper::isAdminOrSindico($user);

        return view('finance.accountability.index', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'canExport' => $user->can('export_accountability_reports'),
            'canViewDetails' => $canViewDetails,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('export_accountability_reports')) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $data = $this->service->generate($this->activeCondominiumId($user), $startDate, $endDate);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.accountability.pdf', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'condominium' => $this->activeCondominium($user),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('prestacao_contas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('export_accountability_reports')) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $data = $this->service->generate($this->activeCondominiumId($user), $startDate, $endDate);

        return Excel::download(
            new AccountabilityExport($this->activeCondominium($user), $data, $startDate, $endDate),
            'prestacao_contas_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.xlsx'
        );
    }

    public function print(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('export_accountability_reports')) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolvePeriod($request);
        $data = $this->service->generate($this->activeCondominiumId($user), $startDate, $endDate);

        return view('finance.accountability.print', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'condominium' => $this->activeCondominium($user),
        ]);
    }

    protected function resolvePeriod(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        return [$startDate, $endDate];
    }

    public function downloadReceipts(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('export_accountability_reports')) {
            abort(403);
        }

        [$startDate, $endDate] = $this->resolvePeriod($request);

        $data = $this->service->generate($this->activeCondominiumId($user), $startDate, $endDate);

        // Criar arquivo ZIP temporário
        $zipFileName = 'comprovantes_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Criar diretório temp se não existir
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            abort(500, 'Não foi possível criar o arquivo ZIP');
        }

        $fileCount = 0;

        // Adicionar comprovantes de entradas manuais
        foreach ($data['manual_incomes'] as $income) {
            $this->addFileToZip($zip, $income, 'ENTRADAS', $fileCount);
        }

        // Adicionar comprovantes de saídas
        foreach ($data['manual_expenses'] as $expense) {
            $this->addFileToZip($zip, $expense, 'SAIDAS', $fileCount);
        }

        $zip->close();

        if ($fileCount === 0) {
            // Se não houver arquivos, deletar o ZIP vazio e retornar erro
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            return redirect()->back()->with('error', 'Nenhum comprovante encontrado para o período selecionado.');
        }

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    protected function addFileToZip(ZipArchive $zip, $account, string $folder, int &$fileCount): void
    {
        $disk = Storage::disk('public');
        
        // Verificar document_path
        if (!empty($account->document_path) && $disk->exists($account->document_path)) {
            $filePath = storage_path('app/public/' . $account->document_path);
            if (file_exists($filePath)) {
                $fileName = $this->generateFileName($account, $account->document_path, 'documento');
                $zip->addFile($filePath, $folder . '/' . $fileName);
                $fileCount++;
            }
        }
        
        // Verificar captured_image_path
        if (!empty($account->captured_image_path) && $disk->exists($account->captured_image_path)) {
            $filePath = storage_path('app/public/' . $account->captured_image_path);
            if (file_exists($filePath)) {
                $fileName = $this->generateFileName($account, $account->captured_image_path, 'imagem');
                $zip->addFile($filePath, $folder . '/' . $fileName);
                $fileCount++;
            }
        }
    }

    protected function generateFileName($account, string $originalPath, string $type): string
    {
        $date = $account->transaction_date->format('Y-m-d');
        $description = Str::slug($account->description, '_');
        $description = Str::limit($description, 50, '');
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $id = $account->id;
        
        return sprintf('%s_%s_%s_%d.%s', $date, $description, $type, $id, $extension);
    }
}

