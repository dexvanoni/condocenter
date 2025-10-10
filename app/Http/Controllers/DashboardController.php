<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Transaction;
use App\Models\Reservation;
use App\Models\Package;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $condominium = $user->condominium;

        // Admin da plataforma (sem condomínio)
        if ($user->isAdmin() && !$condominium) {
            return $this->adminPlatformDashboard($user);
        }

        // Verificar se usuário tem condomínio
        if (!$condominium) {
            return view('dashboard.no-condominium');
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

    protected function sindicoDashboard(User $user, $condominium)
    {
        $currentMonth = now()->format('Y-m');

        // KPIs Financeiros
        $totalReceitas = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $totalDespesas = Transaction::where('condominium_id', $condominium->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $saldo = $totalReceitas - $totalDespesas;

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

        // Últimas Transações
        $ultimasTransacoes = Transaction::with(['user'])
            ->where('condominium_id', $condominium->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Encomendas Pendentes
        $encombendasPendentes = Package::where('condominium_id', $condominium->id)
            ->where('status', 'pending')
            ->count();

        return view('dashboard.sindico', compact(
            'totalReceitas',
            'totalDespesas',
            'saldo',
            'totalAReceber',
            'totalEmAtraso',
            'inadimplentes',
            'proximasReservas',
            'ultimasTransacoes',
            'encombendasPendentes'
        ));
    }

    protected function moradorDashboard(User $user, $condominium)
    {
        // Extrato da Unidade
        $chargesPendentes = Charge::where('unit_id', $user->unit_id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get();

        $chargesPagas = Charge::where('unit_id', $user->unit_id)
            ->where('status', 'paid')
            ->orderBy('due_date', 'desc')
            ->limit(6)
            ->get();

        // Próximas Reservas do Morador
        $minhasReservas = Reservation::with('space')
            ->where('user_id', $user->id)
            ->where('reservation_date', '>=', now())
            ->orderBy('reservation_date')
            ->get();

        // Encomendas Pendentes
        $encomendas = Package::where('unit_id', $user->unit_id)
            ->where('status', 'pending')
            ->orderBy('received_at', 'desc')
            ->get();

        // Notificações não lidas
        $notificacoes = $user->notifications()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.morador', compact(
            'chargesPendentes',
            'chargesPagas',
            'minhasReservas',
            'encomendas',
            'notificacoes'
        ));
    }

    protected function porteiroDashboard(User $user, $condominium)
    {
        // Entradas de hoje
        $entradasHoje = Entry::where('condominium_id', $condominium->id)
            ->whereDate('entry_time', today())
            ->orderBy('entry_time', 'desc')
            ->limit(20)
            ->get();

        // Encomendas para registrar hoje
        $encombendasHoje = Package::where('condominium_id', $condominium->id)
            ->whereDate('received_at', today())
            ->get();

        // Visitantes esperados (pré-autorizados)
        $visitantesEsperados = []; // Implementar lógica de pré-autorização

        return view('dashboard.porteiro', compact(
            'entradasHoje',
            'encombendasHoje',
            'visitantesEsperados'
        ));
    }

    protected function conselhoFiscalDashboard(User $user, $condominium)
    {
        // Visão financeira para fiscalização
        $transacoesMes = Transaction::where('condominium_id', $condominium->id)
            ->whereMonth('transaction_date', now()->month)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalReceitas = $transacoesMes->where('type', 'income')->sum('amount');
        $totalDespesas = $transacoesMes->where('type', 'expense')->sum('amount');

        // Transações sem comprovante
        $semComprovante = Transaction::where('condominium_id', $condominium->id)
            ->whereDoesntHave('receipts')
            ->where('type', 'expense')
            ->count();

        return view('dashboard.conselho', compact(
            'transacoesMes',
            'totalReceitas',
            'totalDespesas',
            'semComprovante'
        ));
    }

    protected function agregadoDashboard(User $user, $condominium)
    {
        // Dashboard limitado para agregados
        $moradorResponsavel = $user->moradorVinculado;
        
        // Encomendas da unidade (via morador responsável)
        $encomendas = [];
        if ($moradorResponsavel && $moradorResponsavel->unit_id) {
            $encomendas = Package::where('unit_id', $moradorResponsavel->unit_id)
                ->where('status', 'pending')
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
        $totalCondominios = \App\Models\Condominium::count();
        $totalUsuarios = \App\Models\User::count();
        $condominiosAtivos = \App\Models\Condominium::where('is_active', true)->count();
        
        $condominios = \App\Models\Condominium::withCount('users', 'units')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.admin', compact(
            'totalCondominios',
            'totalUsuarios',
            'condominiosAtivos',
            'condominios'
        ));
    }
}
