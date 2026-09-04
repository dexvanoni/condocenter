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
            ->where('condominium_id', $user->tenantCondominiumId());

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
        $items->getCollection()->transform(fn (MarketplaceItem $item) => $this->transformItem($item));

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
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!SidebarHelper::canCreateMarketplace($user)) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $imagesPaths = $this->uploadImages($request, (int) $user->tenantCondominiumId());
        $sanitizedWhatsapp = preg_replace('/\D/', '', $request->whatsapp ?? '');

        $item = MarketplaceItem::create([
            'condominium_id' => $user->tenantCondominiumId(),
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
            'item' => $this->transformItem($item->load('seller')),
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
        if ($item->condominium_id !== Auth::user()->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Incrementar visualizações
        $item->incrementViews();

        return response()->json($this->transformItem($item));
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
            'category' => 'sometimes|in:products,services,jobs,real_estate,vehicles,other',
            'condition' => 'sometimes|in:new,used,refurbished,not_applicable',
            'whatsapp' => ['sometimes', 'string', 'regex:/^\d{10,11}$/'],
            'status' => 'sometimes|in:active,sold,inactive',
            'keep_images' => 'sometimes|array|max:3',
            'keep_images.*' => 'string',
            'images' => 'sometimes|array|max:3',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payload = $request->only([
            'title',
            'description',
            'price',
            'category',
            'condition',
            'status',
        ]);

        if ($request->has('whatsapp')) {
            $payload['whatsapp'] = preg_replace('/\D/', '', $request->input('whatsapp'));
        }

        if ($request->has('keep_images') || $request->hasFile('images')) {
            $payload['images'] = $this->syncImages($item, $request);
        }

        $item->update($payload);

        return response()->json([
            'message' => 'Anúncio atualizado com sucesso',
            'item' => $this->transformItem($item->fresh()->load('seller')),
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

        $this->deleteImages($item->images ?? []);

        $item->delete();

        return response()->json([
            'message' => 'Anúncio removido com sucesso'
        ]);
    }

    protected function transformItem(MarketplaceItem $item): MarketplaceItem
    {
        $item->setAttribute('image_urls', $item->image_urls);

        return $item;
    }

    /**
     * @return list<string>
     */
    protected function uploadImages(Request $request, int $condominiumId): array
    {
        $imagesPaths = [];

        if (!$request->hasFile('images')) {
            return $imagesPaths;
        }

        foreach ($request->file('images') as $image) {
            if (!$image || !$image->isValid()) {
                continue;
            }

            $imagesPaths[] = $image->store('marketplace/' . $condominiumId, 'public');
        }

        return array_slice($imagesPaths, 0, 3);
    }

    /**
     * @return list<string>
     */
    protected function syncImages(MarketplaceItem $item, Request $request): array
    {
        $currentImages = $item->images ?? [];
        $keepImages = $request->input('keep_images', $currentImages);

        if (!is_array($keepImages)) {
            $keepImages = [];
        }

        $keepImages = array_values(array_filter(
            $keepImages,
            fn ($path) => is_string($path) && in_array($path, $currentImages, true)
        ));

        foreach ($currentImages as $path) {
            if (!in_array($path, $keepImages, true)) {
                Storage::disk('public')->delete($path);
            }
        }

        $newImages = $this->uploadImages($request, (int) $item->condominium_id);

        return array_slice(array_merge($keepImages, $newImages), 0, 3);
    }

    /**
     * @param list<string> $images
     */
    protected function deleteImages(array $images): void
    {
        foreach ($images as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
