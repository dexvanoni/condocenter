<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MarketplaceAdminController extends Controller
{
    private const CATEGORIES = [
        'products' => 'Produtos',
        'services' => 'Serviços',
        'jobs' => 'Empregos',
        'real_estate' => 'Imóveis',
        'vehicles' => 'Veículos',
        'other' => 'Outros',
    ];

    private const CONDITIONS = [
        'new' => 'Novo',
        'used' => 'Usado',
        'refurbished' => 'Recondicionado',
        'not_applicable' => 'Não se aplica',
    ];

    private const STATUSES = [
        'active' => 'Ativo',
        'sold' => 'Vendido',
        'inactive' => 'Inativo',
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();

            if (
                !$user->hasAnyRole(['Administrador', 'Síndico'])
                && !$user->can('manage_marketplace')
                && !$user->can('manage_marketplace_items')
            ) {
                abort(403, 'Acesso negado.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = [
            'status' => $request->get('status'),
            'category' => $request->get('category'),
            'condition' => $request->get('condition'),
            'search' => $request->get('search'),
        ];

        $query = MarketplaceItem::with(['seller', 'unit'])
            ->where('condominium_id', $user->condominium_id);

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['category']) {
            $query->where('category', $filters['category']);
        }

        if ($filters['condition']) {
            $query->where('condition', $filters['condition']);
        }

        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.admin.index', [
            'items' => $items,
            'filters' => $filters,
            'categories' => self::CATEGORIES,
            'conditions' => self::CONDITIONS,
            'statuses' => self::STATUSES,
            'allowAggregados' => (bool) optional($user->condominium)->marketplace_allow_agregados,
        ]);
    }

    public function update(Request $request, MarketplaceItem $item)
    {
        $user = $request->user();
        $this->ensureSameCondominium($user, $item);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'condition' => ['required', Rule::in(array_keys(self::CONDITIONS))],
            'whatsapp' => ['required', 'string', 'regex:/^\d{10,11}$/'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        $sanitizedWhatsapp = preg_replace('/\D/', '', $validated['whatsapp']);
        $validated['whatsapp'] = $sanitizedWhatsapp;

        $item->update($validated);

        $user->logActivity(
            'update',
            'marketplace',
            "Atualizou o anúncio {$item->title}",
            ['marketplace_item_id' => $item->id]
        );

        return redirect()
            ->route('marketplace.admin.index', $request->query())
            ->with('success', 'Anúncio atualizado com sucesso.');
    }

    public function destroy(Request $request, MarketplaceItem $item)
    {
        $user = $request->user();
        $this->ensureSameCondominium($user, $item);

        if (is_array($item->images)) {
            foreach ($item->images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $itemTitle = $item->title;
        $itemId = $item->id;
        $item->delete();

        $user->logActivity(
            'delete',
            'marketplace',
            "Removeu o anúncio {$itemTitle}",
            ['marketplace_item_id' => $itemId]
        );

        return redirect()
            ->route('marketplace.admin.index', $request->query())
            ->with('success', 'Anúncio removido com sucesso.');
    }

    public function toggleAggregados(Request $request)
    {
        $user = $request->user();
        $condominium = $user->condominium;

        if (!$condominium) {
            return redirect()
                ->route('marketplace.admin.index')
                ->withErrors('Condomínio não encontrado para o usuário autenticado.');
        }

        $validated = $request->validate([
            'marketplace_allow_agregados' => ['required', 'boolean'],
        ]);

        $condominium->update($validated);

        $user->logActivity(
            'update',
            'marketplace',
            'Atualizou a permissão de anúncios para agregados',
            ['allow_agregados' => $validated['marketplace_allow_agregados']]
        );

        $message = $validated['marketplace_allow_agregados']
            ? 'Agora agregados podem anunciar no marketplace.'
            : 'Apenas moradores podem anunciar no marketplace.';

        return redirect()
            ->route('marketplace.admin.index')
            ->with('success', $message);
    }

    private function ensureSameCondominium($user, MarketplaceItem $item): void
    {
        if ($item->condominium_id !== $user->condominium_id) {
            abort(403, 'Você não tem permissão para gerenciar este anúncio.');
        }
    }
}
