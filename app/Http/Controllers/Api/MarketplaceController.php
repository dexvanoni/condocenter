<?php

namespace App\Http\Controllers\Api;

use App\Helpers\SidebarHelper;
use App\Http\Controllers\Controller;
use App\Models\MarketplaceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MarketplaceController extends Controller
{
    /**
     * Lista itens do marketplace
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = MarketplaceItem::with(['seller', 'unit'])
            ->where('condominium_id', $user->condominium_id);

        // Filtros
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Por padrão, mostrar apenas ativos
            $query->where('status', 'active');
        }

        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(12);

        return response()->json($items);
    }

    /**
     * Cria um novo anúncio
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:products,services,jobs,real_estate,vehicles,other',
            'condition' => 'required|in:new,used,refurbished,not_applicable',
            'whatsapp' => ['required', 'string', 'regex:/^\d{10,11}$/'],
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!SidebarHelper::canCreateMarketplace($user)) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Upload de imagens
        $imagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('marketplace/' . $user->condominium_id, 'public');
                $imagesPaths[] = $path;
            }
        }

        $sanitizedWhatsapp = preg_replace('/\D/', '', $request->whatsapp ?? '');

        $item = MarketplaceItem::create([
            'condominium_id' => $user->condominium_id,
            'seller_id' => $user->id,
            'unit_id' => $user->unit_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'condition' => $request->condition,
            'whatsapp' => $sanitizedWhatsapp,
            'images' => $imagesPaths,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Anúncio criado com sucesso!',
            'item' => $item->load('seller')
        ], 201);
    }

    /**
     * Exibe um item
     */
    public function show($id)
    {
        $item = MarketplaceItem::with(['seller.unit', 'unit'])
            ->findOrFail($id);

        // Verificar se pertence ao condomínio
        if ($item->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Incrementar visualizações
        $item->incrementViews();

        return response()->json($item);
    }

    /**
     * Atualiza um anúncio
     */
    public function update(Request $request, $id)
    {
        $item = MarketplaceItem::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas o vendedor ou síndico pode editar
        if ($item->seller_id !== $user->id && !$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'whatsapp' => ['sometimes', 'string', 'regex:/^\d{10,11}$/'],
            'status' => 'sometimes|in:active,sold,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sanitizedWhatsapp = $request->has('whatsapp')
            ? preg_replace('/\D/', '', $request->input('whatsapp'))
            : null;

        $payload = $request->all();

        if ($sanitizedWhatsapp !== null) {
            $payload['whatsapp'] = $sanitizedWhatsapp;
        }

        $item->update($payload);

        return response()->json([
            'message' => 'Anúncio atualizado com sucesso',
            'item' => $item
        ]);
    }

    /**
     * Remove um anúncio
     */
    public function destroy($id)
    {
        $item = MarketplaceItem::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas o vendedor ou síndico pode deletar
        if ($item->seller_id !== $user->id && !$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Deletar imagens do storage
        if ($item->images) {
            foreach ($item->images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $item->delete();

        return response()->json([
            'message' => 'Anúncio removido com sucesso'
        ]);
    }
}
