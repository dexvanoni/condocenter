<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\TermVersion;
use App\Services\LgpdConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformTermsController extends Controller
{
    public function __construct(private LgpdConsentService $lgpd) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->lgpd->ensureDefaultTerms();

        $terms = Term::query()->with(['versions' => fn ($q) => $q->orderByDesc('id')])->orderBy('title')->get();

        return view('platform.terms.index', compact('terms'));
    }

    public function storeVersion(Request $request, Term $term): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $request->validate([
            'version' => ['required', 'string', 'max:32'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'publish' => ['nullable', 'boolean'],
        ]);

        $version = TermVersion::query()->create([
            'term_id' => $term->id,
            'version' => $data['version'],
            'title' => $data['title'],
            'content' => $data['content'],
            'published_by' => auth()->id(),
        ]);

        if ($request->boolean('publish', true)) {
            $version->publish(auth()->user());
        }

        return back()->with('success', 'Versão do termo salva.');
    }
}
