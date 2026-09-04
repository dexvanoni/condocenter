<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PlatformAnnouncementController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $announcements = PlatformAnnouncement::query()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('platform.announcements.index', compact('announcements'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('platform/announcements', 'public');
        }

        PlatformAnnouncement::create($data);

        return back()->with('success', 'Novidade publicada com sucesso.');
    }

    public function update(Request $request, PlatformAnnouncement $announcement): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $data['image_path'] = $request->file('image')->store('platform/announcements', 'public');
        }

        if ($request->boolean('remove_image') && $announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
            $data['image_path'] = null;
        }

        $announcement->update($data);

        return back()->with('success', 'Novidade atualizada com sucesso.');
    }

    public function destroy(PlatformAnnouncement $announcement): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }

        $announcement->delete();

        return back()->with('success', 'Novidade removida.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:10000'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'badge_label' => ['nullable', 'string', 'max:60'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
