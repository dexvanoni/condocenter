<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Services\Learning\LearningCatalogService;
use App\Support\Learning\LearningCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningCenterController extends Controller
{
    public function __construct(
        private readonly LearningCatalogService $catalog,
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $this->authorizeAccess($user);

        $modules = $this->catalog->modulesFor($user);
        $critical = $this->catalog->critical($user);
        $query = trim((string) $request->input('q', ''));
        $results = $query !== '' ? $this->catalog->search($user, $query) : collect();

        return view('learning.index', [
            'modules' => $modules,
            'critical' => $critical,
            'query' => $query,
            'results' => $results,
            'totalTutorials' => $this->catalog->tutorialsFor($user)->count(),
        ]);
    }

    public function module(string $module)
    {
        $user = Auth::user();
        $this->authorizeAccess($user);

        $modules = LearningCatalog::modules();
        if (! isset($modules[$module])) {
            abort(404);
        }

        $tutorials = $this->catalog->byModule($user, $module);
        if ($tutorials->isEmpty()) {
            abort(404);
        }

        return view('learning.module', [
            'moduleKey' => $module,
            'module' => array_merge($modules[$module], ['key' => $module]),
            'tutorials' => $tutorials,
        ]);
    }

    public function show(string $slug)
    {
        $user = Auth::user();
        $this->authorizeAccess($user);

        $tutorial = $this->catalog->find($user, $slug);
        if (! $tutorial) {
            abort(404);
        }

        $siblings = $this->catalog->byModule($user, $tutorial['module']);
        $index = $siblings->search(fn ($item) => $item['slug'] === $slug);
        $previous = $index !== false && $index > 0 ? $siblings[$index - 1] : null;
        $next = $index !== false && $index < $siblings->count() - 1 ? $siblings[$index + 1] : null;

        $actionUrl = null;
        if (! empty($tutorial['route_hint']) && \Illuminate\Support\Facades\Route::has($tutorial['route_hint'])) {
            try {
                $actionUrl = route($tutorial['route_hint']);
            } catch (\Throwable) {
                $actionUrl = null;
            }
        }

        return view('learning.show', [
            'tutorial' => $tutorial,
            'previous' => $previous,
            'next' => $next,
            'actionUrl' => $actionUrl,
            'siblings' => $siblings,
        ]);
    }

    protected function authorizeAccess($user): void
    {
        if (! $user || ! $this->catalog->userCanOpenCenter($user)) {
            abort(403, 'A Central de Aprendizagem está disponível para o perfil de gestão (síndico e afins).');
        }
    }
}
