<?php

namespace App\Http\Controllers;

use App\Helpers\SidebarHelper;
use App\Models\MarketplaceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('acao') === 'novo' && SidebarHelper::canCreateMarketplace(Auth::user())) {
            return redirect()->route('marketplace.create');
        }

        return view('marketplace.index');
    }

    public function create()
    {
        $user = Auth::user();

        if (!SidebarHelper::canCreateMarketplace($user)) {
            abort(403, 'Você não tem permissão para criar anúncios.');
        }

        return view('marketplace.create', $this->formViewData($user));
    }

    public function edit(MarketplaceItem $item)
    {
        $user = Auth::user();

        if ($item->condominium_id !== $user->tenantCondominiumId()) {
            abort(403);
        }

        if ($item->seller_id !== $user->id && !$user->isSindico() && !$user->isAdmin()) {
            abort(403, 'Você não tem permissão para editar este anúncio.');
        }

        return view('marketplace.edit', array_merge($this->formViewData($user), [
            'item' => $item,
        ]));
    }

    protected function formViewData($user): array
    {
        $user->load('unit');

        return [
            'currentUser' => $user,
            'prefilledUnit' => $user->unit,
            'prefilledWhatsapp' => preg_replace('/\D/', '', (string) ($user->phone ?? '')),
            'categories' => [
                'products' => 'Produtos',
                'services' => 'Serviços',
                'jobs' => 'Empregos',
                'real_estate' => 'Imóveis',
                'vehicles' => 'Veículos',
                'other' => 'Outros',
            ],
            'conditions' => [
                'new' => 'Novo',
                'used' => 'Usado',
                'refurbished' => 'Recondicionado',
                'not_applicable' => 'Não se aplica',
            ],
        ];
    }
}
