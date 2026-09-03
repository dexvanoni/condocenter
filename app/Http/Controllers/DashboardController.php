<?php

namespace App\Http\Controllers;

use App\Models\AccessMovement;
use App\Models\Assembly;
use App\Models\Charge;
use App\Models\BankAccount;
use App\Models\BankAccountReconciliation;
use App\Models\CondominiumAccount;
use App\Models\Transaction;
use App\Models\Reservation;
use App\Models\Package;
use App\Models\Entry;
use App\Models\User;
use App\Models\Condominium;
use App\Services\ActiveCondominiumService;
use App\Services\SyndicConversationStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $activeCondominiumService = app(ActiveCondominiumService::class);
        $condominium = $activeCondominiumService->getActiveCondominium($user) ?? $user->condominium;
        $activeRole = session('active_role');

        // Admin da plataforma (sem condomínio selecionado na sessão)
        if ($user->isAdmin() && !$activeCondominiumService->hasActiveCondominium($user)) {
            return $this->adminPlatformDashboard($user);
        }

        // Verificar se usuário tem condomínio
        if (!$condominium) {
            return view('dashboard.no-condominium');
        }

        // Se usuário selecionou um perfil específico, respeitar a seleção
        if ($activeRole && $user->hasAssignedRole($activeRole)) {
            $dashboard = $this->dashboardByRole($user, $condominium, $activeRole);
            if ($dashboard !== null) {
                return $dashboard;
            }
        }

        // Dashboard específico pelo perfil ativo (granularidade real)
        if ($user->shouldUseActiveRoleOnly()) {
            return $this->dashboardByRole($user, $condominium, $user->getActiveRoleName())
                ?? $this->defaultDashboard($user, $condominium);
        }

        // Dashboard específico por role (usuário com perfil único)
        if ($user->isSindico() || $user->isAdmin()) {
            return $this->sindicoDashboard($user, $condominium);
        } elseif ($user->isMorador()) {
            return $this->moradorDashboard($user, $condominium);
        } elseif ($user->isAgregado()) {
            return $this->agregadoDashboard($user, $condominium);
        } elseif ($user->isPorteiro()) {
            return $this->porteiroDashboard($user, $condominium);
        } elseif ($user->isConselhoFiscal()) {
            return $this->conselhoFiscalDashboard($user, $condominium);
        }

        // Fallback para perfis não tratados
        return $this->defaultDashboard($user, $condominium);
    }

    protected function dashboardByRole(User $user, $condominium, string $roleName)
    {
        switch ($roleName) {
            case 'Administrador':
            case 'Síndico':
                return $this->sindicoDashboard($user, $condominium);
            case 'Morador':
                return $this->moradorDashboard($user, $condominium);
            case 'Agregado':
                return $this->agregadoDashboard($user, $condominium);
            case 'Porteiro':
                return $this->porteiroDashboard($user, $condominium);
            case 'Conselho Fiscal':
                return $this->conselhoFiscalDashboard($user, $condominium);
            default:
                return null;
        }
    }

    protected function sindicoDashboard(User $user, $condominium)
    {
        $isFinancialFull = $condominium->isFinancialFull();

        $totalUnidades = $condominium->units()->count();

        $proximasReservas = Reservation::with(['space', 'unit', 'user'])
            ->whereHas('space', function ($q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            })
            ->where('status', 'approved')
            ->where('reservation_date', '>=', now())
            ->orderBy('reservation_date')
            ->limit(5)
            ->get();

        $reservasPendentes = Reservation::whereHas('space', function ($q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            })
            ->where('status', 'pending')
            ->count();

        $reservasMes = Reservation::whereHas('space', function ($q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            })
            ->whereMonth('reservation_date', now()->month)
            ->whereYear('reservation_date', now()->year)
            ->count();

        $encombendasPendentes = Package::byCondominium($condominium->id)
            ->pending()
            ->count();

        $encombendasHoje = Package::byCondominium($condominium->id)
            ->whereDate('received_at', today())
            ->count();

        $moradoresAtivos = User::where('condominium_id', $condominium->id)
            ->where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Morador', 'Síndico']);
            })
            ->count();

        $ocupacaoPercentual = $totalUnidades > 0
            ? ($moradoresAtivos / $totalUnidades) * 100
            : 0;

        $entradasHoje = Entry::where('condominium_id', $condominium->id)
            ->whereDate('entry_time', today())
            ->count();

        $accessMovementsHoje = AccessMovement::query()
            ->where('condominium_id', $condominium->id)
            ->whereDate('occurred_at', today())
            ->count();

        $pendingUsersCount = User::query()
            ->where('condominium_id', $condominium->id)
            ->where('registration_status', 'pending')
            ->count();

        $pendingUsers = User::query()
            ->where('condominium_id', $condominium->id)
            ->where('registration_status', 'pending')
            ->with('unit')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $syndicConversationStats = app(SyndicConversationStatsService::class)
            ->forCondominium($condominium->id);

        $syndicPendingConversations = collect($syndicConversationStats['conversations'])
            ->filter(fn (array $conversation) => (bool) ($conversation['pending_response'] ?? false))
            ->take(6)
            ->values();

        $financialMetrics = $this->buildSindicoFinancialMetrics($condominium, $isFinancialFull);

        return view('dashboard.sindico', array_merge(
            compact(
                'condominium',
                'isFinancialFull',
                'totalUnidades',
                'proximasReservas',
                'reservasPendentes',
                'reservasMes',
                'encombendasPendentes',
                'encombendasHoje',
                'moradoresAtivos',
                'ocupacaoPercentual',
                'entradasHoje',
                'accessMovementsHoje',
                'pendingUsersCount',
                'pendingUsers',
                'syndicConversationStats',
                'syndicPendingConversations',
            ),
            $financialMetrics
        ));
    }

    protected function buildSindicoFinancialMetrics(Condominium $condominium, bool $isFinancialFull): array
    {
        $empty = [
            'totalReceitas' => 0,
            'totalDespesas' => 0,
            'saldo' => 0,
            'variacaoReceitas' => 0,
            'variacaoDespesas' => 0,
            'totalAReceber' => 0,
            'totalEmAtraso' => 0,
            'inadimplentes' => 0,
            'taxaAdimplencia' => 100,
            'ultimasTransacoes' => collect(),
            'categoriasFinanceiras' => collect(),
            'graficoAdimplencia' => ['adimplentes' => 0, 'inadimplentes' => 0],
            'graficoFinanceiro' => [],
            'saldoConsolidado' => 0,
            'entradasNaoConciliadas' => 0,
            'saidasNaoConciliadas' => 0,
            'ultimaConsolidacao' => null,
            'inadimplentesDetalhe' => collect(),
        ];

        if (!$isFinancialFull) {
            return $empty;
        }

        $totalUnidades = $condominium->units()->count();

        // KPIs Financeiros do Mês Atual
        $totalReceitas = Transaction::withTrashed()
            ->where('condominium_id', $condominium->id)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $totalDespesas = Transaction::withTrashed()
            ->where('condominium_id', $condominium->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $saldo = $totalReceitas - $totalDespesas;

        // Mês Anterior (para comparação)
        $receitasMesAnterior = Transaction::withTrashed()
            ->where('condominium_id', $condominium->id)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $despesasMesAnterior = Transaction::withTrashed()
            ->where('condominium_id', $condominium->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        // Percentuais de variação
        $variacaoReceitas = $receitasMesAnterior > 0 
            ? (($totalReceitas - $receitasMesAnterior) / $receitasMesAnterior) * 100 
            : 0;
        
        $variacaoDespesas = $despesasMesAnterior > 0 
            ? (($totalDespesas - $despesasMesAnterior) / $despesasMesAnterior) * 100 
            : 0;

        // Cobranças
        $totalAReceber = Charge::where('condominium_id', $condominium->id)
            ->where('status', 'pending')
            ->sum('amount');

        $totalEmAtraso = Charge::where('condominium_id', $condominium->id)
            ->where('status', 'overdue')
            ->sum('amount');

        $inadimplentes = Charge::where('condominium_id', $condominium->id)
            ->where('status', 'overdue')
            ->distinct('unit_id')
            ->count('unit_id');

        $taxaAdimplencia = $totalUnidades > 0
            ? (($totalUnidades - $inadimplentes) / $totalUnidades) * 100
            : 100;

        $ultimasTransacoes = Transaction::with(['user'])
            ->where('condominium_id', $condominium->id)
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        $categoriasFinanceiras = Transaction::where('condominium_id', $condominium->id)
            ->whereYear('transaction_date', now()->year)
            ->selectRaw("
                COALESCE(category, 'Não Informada') as category,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_receitas,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_despesas,
                SUM(amount) as total_movimentado
            ")
            ->groupBy('category')
            ->orderByDesc('total_movimentado')
            ->limit(6)
            ->get();

        $graficoAdimplencia = [
            'adimplentes' => max($totalUnidades - $inadimplentes, 0),
            'inadimplentes' => $inadimplentes,
        ];

        $inadimplentesDetalhe = Charge::query()
            ->where('condominium_id', $condominium->id)
            ->where('status', 'overdue')
            ->with('unit')
            ->orderBy('due_date')
            ->get()
            ->groupBy('unit_id')
            ->map(function ($charges) {
                $first = $charges->first();

                return [
                    'unit' => $first?->unit,
                    'total' => $charges->sum('amount'),
                    'count' => $charges->count(),
                    'oldest_due' => $charges->min('due_date'),
                ];
            })
            ->sortByDesc('total')
            ->take(8)
            ->values();

        $graficoFinanceiro = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $receitas = Transaction::where('condominium_id', $condominium->id)
                ->where('type', 'income')
                ->whereMonth('transaction_date', $mes->month)
                ->whereYear('transaction_date', $mes->year)
                ->sum('amount');

            $despesas = Transaction::where('condominium_id', $condominium->id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $mes->month)
                ->whereYear('transaction_date', $mes->year)
                ->sum('amount');

            $graficoFinanceiro[] = [
                'mes' => $mes->format('M/Y'),
                'receitas' => $receitas,
                'despesas' => $despesas,
                'saldo' => $receitas - $despesas,
            ];
        }

        $saldoConsolidado = BankAccount::where('condominium_id', $condominium->id)
            ->sum('current_balance');

        $ultimaConsolidacao = BankAccountReconciliation::where('condominium_id', $condominium->id)
            ->latest('created_at')
            ->first();

        $periodStart = $ultimaConsolidacao && $ultimaConsolidacao->end_date
            ? $ultimaConsolidacao->end_date->copy()->addDay()
            : now()->subMonths(12)->startOfDay();

        $entradasNaoConciliadas = Transaction::withTrashed()
            ->where('condominium_id', $condominium->id)
            ->where('status', 'paid')
            ->whereNull('reconciliation_id')
            ->where('type', 'income')
            ->where('transaction_date', '>=', $periodStart)
            ->sum('amount')
            + CondominiumAccount::where('condominium_id', $condominium->id)
                ->whereNull('reconciliation_id')
                ->where('type', 'income')
                ->where('transaction_date', '>=', $periodStart)
                ->sum('amount');

        $saidasNaoConciliadas = Transaction::withTrashed()
            ->where('condominium_id', $condominium->id)
            ->where('status', 'paid')
            ->whereNull('reconciliation_id')
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $periodStart)
            ->sum('amount')
            + CondominiumAccount::where('condominium_id', $condominium->id)
                ->whereNull('reconciliation_id')
                ->where('type', 'expense')
                ->where('transaction_date', '>=', $periodStart)
                ->sum('amount');

        if ($ultimaConsolidacao) {
            $ultimaConsolidacao->loadMissing('bankAccount');
        }

        return [
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $saldo,
            'variacaoReceitas' => $variacaoReceitas,
            'variacaoDespesas' => $variacaoDespesas,
            'totalAReceber' => $totalAReceber,
            'totalEmAtraso' => $totalEmAtraso,
            'inadimplentes' => $inadimplentes,
            'taxaAdimplencia' => $taxaAdimplencia,
            'ultimasTransacoes' => $ultimasTransacoes,
            'categoriasFinanceiras' => $categoriasFinanceiras,
            'graficoAdimplencia' => $graficoAdimplencia,
            'graficoFinanceiro' => $graficoFinanceiro,
            'saldoConsolidado' => $saldoConsolidado,
            'entradasNaoConciliadas' => $entradasNaoConciliadas,
            'saidasNaoConciliadas' => $saidasNaoConciliadas,
            'ultimaConsolidacao' => $ultimaConsolidacao,
            'inadimplentesDetalhe' => $inadimplentesDetalhe,
        ];
    }

    protected function moradorDashboard(User $user, $condominium)
    {
        // Cobranças Pendentes
        $chargesPendentes = Charge::where('unit_id', $user->unit_id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get();

        // Cobranças Em Atraso
        $chargesAtrasadas = Charge::where('unit_id', $user->unit_id)
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->get();

        // Total de Débitos
        $totalDebitos = $chargesPendentes->sum('amount') + $chargesAtrasadas->sum('amount');

        // Cobranças Pagas (últimas 6)
        $chargesPagas = Charge::where('unit_id', $user->unit_id)
            ->where('status', 'paid')
            ->orderBy('due_date', 'desc')
            ->limit(6)
            ->get();

        // Total Pago no Ano
        $totalPagoAno = Charge::where('unit_id', $user->unit_id)
            ->where('status', 'paid')
            ->whereYear('due_date', now()->year)
            ->sum('amount');

        // Próximas Reservas do Morador
        $minhasReservas = Reservation::with('space')
            ->where('user_id', $user->id)
            ->where('reservation_date', '>=', now())
            ->orderBy('reservation_date')
            ->limit(5)
            ->get();

        // Total de Reservas Ativas
        $totalReservasAtivas = Reservation::where('user_id', $user->id)
            ->where('reservation_date', '>=', now())
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        // Encomendas Pendentes
        $encomendas = Package::forUnit($user->unit_id)
            ->pending()
            ->orderBy('received_at', 'desc')
            ->get();

        // Encomendas Recebidas Este Mês
        $encombendasMes = Package::where('unit_id', $user->unit_id)
            ->whereMonth('received_at', now()->month)
            ->count();

        // Assembleias aguardando voto do usuário
        $assembliesPendentes = Assembly::with(['items', 'allowedRoles'])
            ->withCount([
                'items as pending_items_count' => function ($query) use ($user) {
                    $query->whereDoesntHave('votes', function ($voteQuery) use ($user) {
                        $voteQuery->where(function ($q) use ($user) {
                            $q->where('voter_id', $user->id);
                            if ($user->unit_id) {
                                $q->orWhere('unit_id', $user->unit_id);
                            }
                        });
                    });
                },
            ])
            ->withCount('items')
            ->where('condominium_id', $user->tenantCondominiumId())
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->get()
            ->filter(function (Assembly $assembly) use ($user) {
                return $assembly->isVotingOpen()
                    && $assembly->canUserVote($user)
                    && ($assembly->pending_items_count ?? 0) > 0;
            })
            ->map(function (Assembly $assembly) {
                $assembly->append('display_status');
                return [
                    'id' => $assembly->id,
                    'title' => $assembly->title,
                    'status' => $assembly->display_status ?? $assembly->status,
                    'urgency' => $assembly->urgency,
                    'voting_opens_at' => $assembly->voting_opens_at ?? $assembly->scheduled_at,
                    'voting_closes_at' => $assembly->voting_closes_at,
                    'pending_items' => $assembly->pending_items_count ?? 0,
                    'total_items' => $assembly->items_count ?? $assembly->items->count(),
                    'voted_items' => max(0, ($assembly->items_count ?? $assembly->items->count()) - ($assembly->pending_items_count ?? 0)),
                ];
            })
            ->values();

        // Notificações não lidas
        $notificacoes = $user->notifications()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Total de Notificações Não Lidas
        $totalNotificacoes = $user->notifications()
            ->where('is_read', false)
            ->count();

        // Status do Perfil
        $possuiDividas = $user->possui_dividas;
        $statusCadastro = $user->is_active ? 'Ativo' : 'Inativo';

        // Entradas financeiras recentes - apenas da própria unidade para moradores
        // Moradores não podem ver detalhes de outras unidades (privacidade)
        // Verifica se é morador (verificação robusta: é morador E não é admin/síndico)
        $isMorador = $user->isMorador() && !$user->isAdmin() && !$user->isSindico();
        
        $recentFinancialEntries = CondominiumAccount::with('creator')
            ->byCondominium($condominium->id)
            ->where('type', 'income')
            ->where('source_type', 'charge')
            ->whereBetween('transaction_date', [now()->subDays(30)->startOfDay(), now()->endOfDay()])
            ->orderByDesc('transaction_date')
            ->limit(10)
            ->get();

        // Buscar as cobranças relacionadas para obter informações da unidade
        $chargeIds = $recentFinancialEntries->pluck('source_id')->filter()->unique();
        $chargesById = Charge::with('unit')
            ->whereIn('id', $chargeIds)
            ->get()
            ->keyBy('id');

        // Filtrar e mapear entradas: moradores veem apenas da própria unidade
        $filteredFinancialEntries = $recentFinancialEntries->map(function ($entry) use ($chargesById, $isMorador, $user) {
            $charge = $chargesById->get($entry->source_id);
            
            // Para moradores, mostrar apenas entradas da própria unidade
            if ($isMorador && $charge && $charge->unit_id !== $user->unit_id) {
                return null; // Filtrar fora
            }

            return [
                'id' => $entry->id,
                'transaction_date' => $entry->transaction_date,
                'title' => $charge?->title ?? $entry->description,
                'amount' => $entry->amount,
                // Para moradores, nunca mostrar unidade (null)
                // Para admin/síndico, mostrar unidade
                'unit' => $isMorador ? null : ($charge?->unit?->full_identifier ?? null),
                'is_own_unit' => $charge && $charge->unit_id === $user->unit_id,
            ];
        })->filter(); // Remove entradas null (de outras unidades para moradores)

        // Para moradores, calcular total agregado de outras unidades (sem detalhes)
        $otherUnitsSummary = null;
        if ($isMorador) {
            $otherUnitsEntries = $recentFinancialEntries->filter(function ($entry) use ($chargesById, $user) {
                $charge = $chargesById->get($entry->source_id);
                return $charge && $charge->unit_id !== $user->unit_id;
            });
            
            if ($otherUnitsEntries->isNotEmpty()) {
                $otherUnitsSummary = [
                    'count' => $otherUnitsEntries->count(),
                    'total' => $otherUnitsEntries->sum('amount'),
                ];
            }
        }

        $onlinePaymentsEnabled = $condominium?->acceptsOnlinePayments() ?? false;

        return view('dashboard.morador', compact(
            'chargesPendentes',
            'chargesAtrasadas',
            'totalDebitos',
            'chargesPagas',
            'totalPagoAno',
            'minhasReservas',
            'totalReservasAtivas',
            'encomendas',
            'encombendasMes',
            'assembliesPendentes',
            'notificacoes',
            'totalNotificacoes',
            'possuiDividas',
            'statusCadastro',
            'filteredFinancialEntries',
            'isMorador',
            'otherUnitsSummary',
            'onlinePaymentsEnabled',
        ));
    }

    protected function porteiroDashboard(User $user, $condominium)
    {
        // Entradas de hoje
        $entradasHoje = Entry::with(['unit', 'registeredBy'])
            ->where('condominium_id', $condominium->id)
            ->whereDate('entry_time', today())
            ->orderBy('entry_time', 'desc')
            ->limit(30)
            ->get();

        // Total de Entradas Hoje
        $totalEntradasHoje = Entry::where('condominium_id', $condominium->id)
            ->whereDate('entry_time', today())
            ->count();

        // Entradas Ainda Dentro (sem saída registrada)
        $entradasAbertas = Entry::where('condominium_id', $condominium->id)
            ->whereNull('exit_time')
            ->whereDate('entry_time', today())
            ->count();

        // Encomendas registradas hoje
        $encomendasHoje = Package::with('unit')
            ->byCondominium($condominium->id)
            ->whereDate('received_at', today())
            ->orderByDesc('received_at')
            ->limit(10)
            ->get();

        // Total de Encomendas Hoje
        $totalEncomendasHoje = $encomendasHoje->count();

        // Encomendas Pendentes de Retirada (Total)
        $encomendasPendentesTotal = Package::byCondominium($condominium->id)
            ->pending()
            ->count();

        $encomendasPendentes = Package::with(['unit', 'unit.users'])
            ->byCondominium($condominium->id)
            ->pending()
            ->orderBy('received_at')
            ->limit(6)
            ->get();

        // Estatísticas por Tipo de Entrada Hoje
        $entriesByType = Entry::where('condominium_id', $condominium->id)
            ->whereDate('entry_time', today())
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $visitantes = $entriesByType['visitor'] ?? 0;
        $prestadores = $entriesByType['service_provider'] ?? 0;
        $entregas = $entriesByType['delivery'] ?? 0;
        $moradores = $entriesByType['resident'] ?? 0;

        // Visitantes esperados (pré-autorizados) - placeholder para implementação futura
        $visitantesEsperados = [];

        // Última Atividade
        $ultimaAtividade = Entry::where('condominium_id', $condominium->id)
            ->orderBy('entry_time', 'desc')
            ->first();

        return view('dashboard.porteiro', compact(
            'entradasHoje',
            'totalEntradasHoje',
            'entradasAbertas',
            'encomendasHoje',
            'totalEncomendasHoje',
            'encomendasPendentes',
            'encomendasPendentesTotal',
            'visitantes',
            'prestadores',
            'entregas',
            'moradores',
            'visitantesEsperados',
            'ultimaAtividade'
        ));
    }

    protected function conselhoFiscalDashboard(User $user, $condominium)
    {
        // Transações do Mês Atual
        $transacoesMes = Transaction::with(['user', 'receipts'])
            ->where('condominium_id', $condominium->id)
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalReceitas = $transacoesMes->where('type', 'income')->sum('amount');
        $totalDespesas = $transacoesMes->where('type', 'expense')->sum('amount');
        $saldoMes = $totalReceitas - $totalDespesas;

        // Comparação com Mês Anterior
        $receitasMesAnterior = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $despesasMesAnterior = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $variacaoReceitas = $receitasMesAnterior > 0 
            ? (($totalReceitas - $receitasMesAnterior) / $receitasMesAnterior) * 100 
            : 0;

        $variacaoDespesas = $despesasMesAnterior > 0 
            ? (($totalDespesas - $despesasMesAnterior) / $despesasMesAnterior) * 100 
            : 0;

        // Transações sem comprovante (ALERTA)
        $semComprovante = Transaction::where('condominium_id', $condominium->id)
            ->whereDoesntHave('receipts')
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->count();

        $totalSemComprovanteValor = Transaction::where('condominium_id', $condominium->id)
            ->whereDoesntHave('receipts')
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        // Total de Transações no Mês
        $totalTransacoes = $transacoesMes->count();

        // Despesas por Categoria (Top 5)
        $despesasPorCategoria = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Saldo Acumulado no Ano
        $receitasAno = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'income')
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $despesasAno = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $saldoAno = $receitasAno - $despesasAno;

        // Cobranças em Atraso (Indicador de Inadimplência)
        $valorEmAtraso = Charge::where('condominium_id', $condominium->id)
            ->where('status', 'overdue')
            ->sum('amount');

        $inadimplentes = Charge::where('condominium_id', $condominium->id)
            ->where('status', 'overdue')
            ->distinct('unit_id')
            ->count('unit_id');

        return view('dashboard.conselho', compact(
            'transacoesMes',
            'totalReceitas',
            'totalDespesas',
            'saldoMes',
            'variacaoReceitas',
            'variacaoDespesas',
            'semComprovante',
            'totalSemComprovanteValor',
            'totalTransacoes',
            'despesasPorCategoria',
            'receitasAno',
            'despesasAno',
            'saldoAno',
            'valorEmAtraso',
            'inadimplentes'
        ));
    }

    protected function agregadoDashboard(User $user, $condominium)
    {
        // Dashboard limitado para agregados
        $moradorResponsavel = $user->moradorVinculado;
        
        // Encomendas da unidade (via morador responsável)
        $encomendas = [];
        if ($moradorResponsavel && $moradorResponsavel->unit_id) {
            $encomendas = Package::forUnit($moradorResponsavel->unit_id)
                ->pending()
                ->orderBy('received_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Notificações limitadas
        $notificacoes = $user->notifications()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('dashboard.agregado', compact(
            'moradorResponsavel',
            'encomendas',
            'notificacoes'
        ));
    }

    protected function defaultDashboard(User $user, $condominium)
    {
        // Dashboard genérico para perfis não tratados
        $notificacoes = $user->notifications()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.default', compact(
            'notificacoes'
        ));
    }

    protected function adminPlatformDashboard(User $user)
    {
        // Dashboard do administrador da plataforma
        $totalCondominios = Condominium::count();
        $totalUsuarios = User::count();
        $condominiosAtivos = Condominium::where('is_active', true)->count();
        $condominiosInativos = $totalCondominios - $condominiosAtivos;
        
        // Usuários por Perfil
        $usuariosPorPerfil = User::with('roles')
            ->get()
            ->flatMap(function ($user) {
                return $user->roles->pluck('name');
            })
            ->countBy()
            ->toArray();

        // Usuários Ativos
        $usuariosAtivos = User::where('is_active', true)->count();
        $usuariosInativos = $totalUsuarios - $usuariosAtivos;
        $usuariosAtivosPercentual = $totalUsuarios > 0
            ? ($usuariosAtivos / $totalUsuarios) * 100
            : 0;
        $condominiosAtivosPercentual = $totalCondominios > 0
            ? ($condominiosAtivos / $totalCondominios) * 100
            : 0;

        // Condomínios Recentes (últimos 10)
        $condominios = Condominium::withCount('users', 'units')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Total de Transações na Plataforma (este mês)
        $totalTransacoesMes = Transaction::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();

        $volumeFinanceiroMes = Transaction::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        // Total de Reservas na Plataforma (este mês)
        $totalReservasMes = Reservation::whereMonth('reservation_date', now()->month)
            ->whereYear('reservation_date', now()->year)
            ->count();

        // Crescimento Mensal
        $usuariosMesAnterior = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $usuariosMesAtual = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $crescimentoUsuarios = $usuariosMesAnterior > 0 
            ? (($usuariosMesAtual - $usuariosMesAnterior) / $usuariosMesAnterior) * 100 
            : 0;

        // Condomínios com Mais Usuários (Top 5)
        $topCondominios = Condominium::withCount('users')
            ->orderByDesc('users_count')
            ->limit(5)
            ->get();

        // Histórico de crescimento (6 meses)
        $historicoPlataforma = collect(range(5, 0))->map(function ($i) {
            $mes = now()->subMonths($i);

            return [
                'mes' => $mes->format('M/Y'),
                'usuarios' => User::whereMonth('created_at', $mes->month)
                    ->whereYear('created_at', $mes->year)
                    ->count(),
                'condominios' => Condominium::whereMonth('created_at', $mes->month)
                    ->whereYear('created_at', $mes->year)
                    ->count(),
            ];
        });

        // Indicadores operacionais
        $valorCobrancasPendentes = Charge::whereIn('status', ['pending', 'overdue'])->sum('amount');
        $valorCobrancasAtraso = Charge::where('status', 'overdue')->sum('amount');
        $totalCobrancasPendentes = Charge::whereIn('status', ['pending', 'overdue'])->count();
        $reservasPendentes = Reservation::where('status', 'pending')->count();

        $resumoOperacional = [
            'cobrancasPendentes' => $totalCobrancasPendentes,
            'valorCobrancasPendentes' => $valorCobrancasPendentes,
            'valorCobrancasAtraso' => $valorCobrancasAtraso,
            'reservasPendentes' => $reservasPendentes,
        ];

        return view('dashboard.admin', compact(
            'totalCondominios',
            'totalUsuarios',
            'condominiosAtivos',
            'condominiosInativos',
            'usuariosPorPerfil',
            'usuariosAtivos',
            'usuariosInativos',
            'condominios',
            'totalTransacoesMes',
            'volumeFinanceiroMes',
            'totalReservasMes',
            'crescimentoUsuarios',
            'topCondominios',
            'historicoPlataforma',
            'usuariosAtivosPercentual',
            'condominiosAtivosPercentual',
            'resumoOperacional'
        ));
    }
}
