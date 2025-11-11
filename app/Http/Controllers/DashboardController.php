<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\Charge;
use App\Models\Transaction;
use App\Models\Reservation;
use App\Models\Package;
use App\Models\Entry;
use App\Models\User;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $condominium = $user->condominium;
        $activeRole = session('active_role');

        // Admin da plataforma (sem condomínio)
        if ($user->isAdmin() && !$condominium) {
            return $this->adminPlatformDashboard($user);
        }

        // Verificar se usuário tem condomínio
        if (!$condominium) {
            return view('dashboard.no-condominium');
        }

        // Se usuário selecionou um perfil específico, respeitar a seleção
        if ($activeRole && $user->hasRole($activeRole)) {
            $dashboard = $this->dashboardByRole($user, $condominium, $activeRole);
            if ($dashboard !== null) {
                return $dashboard;
            }
        }

        // Dashboard específico por role
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
        $currentMonth = now()->format('Y-m');

        // KPIs Financeiros do Mês Atual
        $totalReceitas = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $totalDespesas = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $saldo = $totalReceitas - $totalDespesas;

        // Mês Anterior (para comparação)
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

        $totalUnidades = $condominium->units()->count();
        $taxaAdimplencia = $totalUnidades > 0 
            ? (($totalUnidades - $inadimplentes) / $totalUnidades) * 100 
            : 100;

        // Próximas Reservas
        $proximasReservas = Reservation::with(['space', 'unit', 'user'])
            ->whereHas('space', function ($q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            })
            ->where('status', 'approved')
            ->where('reservation_date', '>=', now())
            ->orderBy('reservation_date')
            ->limit(5)
            ->get();

        // Reservas Pendentes de Aprovação
        $reservasPendentes = Reservation::whereHas('space', function ($q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            })
            ->where('status', 'pending')
            ->count();

        // Reservas do mês
        $reservasMes = Reservation::whereHas('space', function ($q) use ($condominium) {
                $q->where('condominium_id', $condominium->id);
            })
            ->whereMonth('reservation_date', now()->month)
            ->whereYear('reservation_date', now()->year)
            ->count();

        // Últimas Transações
        $ultimasTransacoes = Transaction::with(['user'])
            ->where('condominium_id', $condominium->id)
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get();

        // Categorias financeiras (ano em curso)
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

        // Encomendas Pendentes
        $encombendasPendentes = Package::byCondominium($condominium->id)
            ->pending()
            ->count();

        // Encomendas de Hoje
        $encombendasHoje = Package::byCondominium($condominium->id)
            ->whereDate('received_at', today())
            ->count();

        // Total de Moradores Ativos
        $moradoresAtivos = User::where('condominium_id', $condominium->id)
            ->where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Morador', 'Síndico']);
            })
            ->count();
        $ocupacaoPercentual = $totalUnidades > 0
            ? ($moradoresAtivos / $totalUnidades) * 100
            : 0;

        // Entradas de Hoje
        $entradasHoje = Entry::where('condominium_id', $condominium->id)
            ->whereDate('entry_time', today())
            ->count();

        // Gráfico de Receitas vs Despesas (últimos 6 meses)
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
                'saldo' => $receitas - $despesas
            ];
        }

        return view('dashboard.sindico', compact(
            'totalReceitas',
            'totalDespesas',
            'saldo',
            'variacaoReceitas',
            'variacaoDespesas',
            'totalAReceber',
            'totalEmAtraso',
            'inadimplentes',
            'taxaAdimplencia',
            'proximasReservas',
            'reservasPendentes',
            'ultimasTransacoes',
            'encombendasPendentes',
            'encombendasHoje',
            'moradoresAtivos',
            'entradasHoje',
            'totalUnidades',
            'graficoFinanceiro',
            'categoriasFinanceiras',
            'graficoAdimplencia',
            'ocupacaoPercentual',
            'reservasMes'
        ));
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
                        $voteQuery->where('voter_id', $user->id);
                    });
                },
            ])
            ->withCount('items')
            ->where('condominium_id', $user->condominium_id)
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
                    'scheduled_at' => $assembly->scheduled_at,
                    'voting_closes_at' => $assembly->voting_closes_at,
                    'pending_items' => $assembly->pending_items_count ?? 0,
                    'total_items' => $assembly->items_count ?? $assembly->items->count(),
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
            'statusCadastro'
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
