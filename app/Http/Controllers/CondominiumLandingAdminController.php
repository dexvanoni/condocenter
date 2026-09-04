<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\StoreCondominiumLandingItemRequest;
use App\Http\Requests\UpdateCondominiumLandingItemRequest;
use App\Http\Requests\UpdateCondominiumLandingPageRequest;
use App\Models\CondominiumLandingItem;
use App\Models\CondominiumLandingPage;
use App\Services\CondominiumLandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class CondominiumLandingAdminController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly CondominiumLandingService $landingService,
    ) {
        $this->middleware('can:manage_landing_page');
    }

    public function edit(): View
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);
        $condominium = \App\Models\Condominium::query()->findOrFail($condominiumId);
        $page = $this->landingService->findOrCreateForCondominium($condominium);
        $page->load(['items' => fn ($q) => $q->orderBy('sort_order')->orderByDesc('created_at')]);

        return view('landing.admin.edit', [
            'page' => $page,
            'condominium' => $condominium,
            'itemTypes' => CondominiumLandingItem::TYPES,
        ]);
    }

    public function editItem(CondominiumLandingItem $item): View
    {
        $page = $this->resolvePage();
        abort_unless($item->landing_page_id === $page->id, 404);

        $condominium = \App\Models\Condominium::query()->findOrFail($page->condominium_id);

        return view('landing.admin.item-edit', [
            'page' => $page,
            'item' => $item,
            'condominium' => $condominium,
            'itemTypes' => CondominiumLandingItem::TYPES,
        ]);
    }

    public function update(UpdateCondominiumLandingPageRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);
        $page = CondominiumLandingPage::query()
            ->where('condominium_id', $condominiumId)
            ->firstOrFail();

        $data = $request->validated();

        foreach ([
            'is_published',
            'show_rides_feed',
            'show_marketplace_feed',
            'show_platform_news',
            'show_announcements_feed',
        ] as $booleanField) {
            $data[$booleanField] = $request->boolean($booleanField);
        }

        if ($request->hasFile('hero_image')) {
            if ($page->hero_image) {
                Storage::disk('public')->delete($page->hero_image);
            }
            $data['hero_image'] = $request->file('hero_image')->store("landing/{$condominiumId}", 'public');
        }

        if ($request->hasFile('hero_gallery')) {
            $gallery = $page->hero_gallery ?? [];
            foreach ($request->file('hero_gallery') as $file) {
                $gallery[] = $file->store("landing/{$condominiumId}/gallery", 'public');
            }
            $data['hero_gallery'] = $gallery;
        }

        if ($request->boolean('remove_hero_image') && $page->hero_image) {
            Storage::disk('public')->delete($page->hero_image);
            $data['hero_image'] = null;
        }

        if ($request->boolean('is_published') && !$page->is_published) {
            $data['published_at'] = now();
        }

        if (!$request->boolean('is_published')) {
            $data['published_at'] = null;
        }

        if (!empty($data['slug'])) {
            $data['slug'] = CondominiumLandingPage::generateUniqueSlug($data['slug'], $page->id);
        }

        if (array_key_exists('custom_domain', $data)) {
            $data['custom_domain'] = CondominiumLandingPage::normalizeDomain($data['custom_domain']);
        }

        $page->update($data);

        return back()->with('success', 'Landing page atualizada com sucesso.');
    }

    public function storeItem(StoreCondominiumLandingItemRequest $request): RedirectResponse
    {
        $page = $this->resolvePage();
        $data = $this->prepareItemData($request, $page);
        $data['sort_order'] = ((int) $page->items()->max('sort_order')) + 1;

        CondominiumLandingItem::create($data);

        return back()->with('success', 'Conteúdo adicionado com sucesso.');
    }

    public function updateItem(UpdateCondominiumLandingItemRequest $request, CondominiumLandingItem $item): RedirectResponse
    {
        $page = $this->resolvePage();
        abort_unless($item->landing_page_id === $page->id, 404);

        $data = $this->prepareItemData($request, $page, $item);

        $item->update($data);

        return redirect()
            ->route('condominium.landing.items.edit', $item)
            ->with('success', 'Conteúdo atualizado com sucesso.');
    }

    public function destroyItem(CondominiumLandingItem $item): RedirectResponse
    {
        $page = $this->resolvePage();
        abort_unless($item->landing_page_id === $page->id, 404);

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        foreach ($item->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $item->delete();

        return redirect()
            ->to(route('condominium.landing.edit').'#tab-conteudo')
            ->with('success', 'Conteúdo removido com sucesso.');
    }

    public function removeGalleryImage(Request $request): RedirectResponse
    {
        $page = $this->resolvePage();
        $path = $request->input('path');

        $gallery = collect($page->hero_gallery ?? [])
            ->reject(fn ($item) => $item === $path)
            ->values()
            ->all();

        if ($path && in_array($path, $page->hero_gallery ?? [], true)) {
            Storage::disk('public')->delete($path);
        }

        $page->update(['hero_gallery' => $gallery]);

        return back()->with('success', 'Imagem removida da galeria.');
    }

    public function reorderItems(Request $request): JsonResponse
    {
        $page = $this->resolvePage();

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['integer', 'exists:condominium_landing_items,id'],
        ]);

        foreach ($validated['items'] as $index => $itemId) {
            CondominiumLandingItem::query()
                ->where('landing_page_id', $page->id)
                ->where('id', $itemId)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function qrCode(): Response
    {
        $page = $this->resolvePage();

        abort_unless($page->is_published, 404, 'Publique a landing page antes de gerar o QR Code.');

        $svg = QrCode::format('svg')
            ->size(480)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($page->publicUrl());

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="landing-'.$page->slug.'.svg"',
        ]);
    }

    public function qrCodeDownload(): Response
    {
        $page = $this->resolvePage();

        abort_unless($page->is_published, 404, 'Publique a landing page antes de gerar o QR Code.');

        $png = QrCode::format('png')
            ->size(800)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($page->publicUrl());

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="landing-'.$page->slug.'.png"',
        ]);
    }

    private function resolvePage(): CondominiumLandingPage
    {
        $condominiumId = $this->activeCondominiumId(Auth::user());

        return CondominiumLandingPage::query()
            ->where('condominium_id', $condominiumId)
            ->firstOrFail();
    }

    private function prepareItemData(Request $request, CondominiumLandingPage $page, ?CondominiumLandingItem $item = null): array
    {
        $data = $request->validated();
        $condominiumId = $page->condominium_id;
        $data['landing_page_id'] = $page->id;

        if ($request->hasFile('image')) {
            if ($item?->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $request->file('image')->store("landing/{$condominiumId}/items", 'public');
        }

        if ($request->hasFile('images')) {
            $images = $item?->images ?? [];
            foreach ($request->file('images') as $file) {
                $images[] = $file->store("landing/{$condominiumId}/items", 'public');
            }
            $data['images'] = $images;
        }

        foreach (['is_popup', 'is_featured', 'is_published'] as $booleanField) {
            $data[$booleanField] = $request->boolean($booleanField);
        }

        if (($data['type'] ?? $item?->type) === CondominiumLandingItem::TYPE_POPUP) {
            $data['is_popup'] = true;
        }

        return $data;
    }
}
