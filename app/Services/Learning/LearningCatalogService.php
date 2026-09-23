<?php

namespace App\Services\Learning;

use App\Models\User;
use App\Support\Learning\LearningCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LearningCatalogService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function modulesFor(User $user): Collection
    {
        $counts = $this->tutorialsFor($user)->groupBy('module')->map->count();

        return collect(LearningCatalog::modules())
            ->map(function (array $module, string $key) use ($counts) {
                return array_merge($module, [
                    'key' => $key,
                    'tutorial_count' => (int) ($counts[$key] ?? 0),
                ]);
            })
            ->filter(fn (array $module) => $module['tutorial_count'] > 0)
            ->sortBy('order')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function tutorialsFor(User $user): Collection
    {
        return collect(LearningCatalog::tutorials())
            ->filter(fn (array $tutorial) => $this->userCanAccess($user, $tutorial))
            ->map(fn (array $tutorial) => $this->enrich($tutorial))
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function byModule(User $user, string $moduleKey): Collection
    {
        return $this->tutorialsFor($user)
            ->where('module', $moduleKey)
            ->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(User $user, string $slug): ?array
    {
        $tutorial = $this->tutorialsFor($user)->firstWhere('slug', $slug);

        return $tutorial;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function search(User $user, string $query): Collection
    {
        $needle = Str::lower(trim($query));
        if ($needle === '') {
            return collect();
        }

        return $this->tutorialsFor($user)
            ->filter(function (array $tutorial) use ($needle) {
                $haystack = Str::lower(implode(' ', [
                    $tutorial['title'],
                    $tutorial['summary'],
                    $tutorial['module_label'] ?? '',
                    implode(' ', $tutorial['tags'] ?? []),
                    implode(' ', $tutorial['objectives'] ?? []),
                    collect($tutorial['steps'] ?? [])->pluck('body')->implode(' '),
                ]));

                return Str::contains($haystack, $needle);
            })
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function critical(User $user): Collection
    {
        return $this->tutorialsFor($user)
            ->where('critical', true)
            ->values();
    }

    public function userCanOpenCenter(User $user): bool
    {
        $roles = config('learning.audiences.sindico', ['Síndico']);

        return $user->hasAnyRole($roles);
    }

    /**
     * @param  array<string, mixed>  $tutorial
     */
    protected function userCanAccess(User $user, array $tutorial): bool
    {
        $audience = $tutorial['audience'] ?? 'sindico';
        $roles = config("learning.audiences.{$audience}", ['Síndico']);

        return $user->hasAnyRole($roles);
    }

    /**
     * @param  array<string, mixed>  $tutorial
     * @return array<string, mixed>
     */
    protected function enrich(array $tutorial): array
    {
        $modules = LearningCatalog::modules();
        $moduleKey = $tutorial['module'];
        $tutorial['module_label'] = $modules[$moduleKey]['label'] ?? $moduleKey;
        $tutorial['module_icon'] = $modules[$moduleKey]['icon'] ?? 'bi-book';
        $tutorial['has_video'] = $this->resolveVideo($tutorial)['available'];
        $tutorial['video_resolved'] = $this->resolveVideo($tutorial);

        return $tutorial;
    }

    /**
     * @param  array<string, mixed>  $tutorial
     * @return array{available: bool, type: ?string, src: ?string}
     */
    public function resolveVideo(array $tutorial): array
    {
        if (! empty($tutorial['video_url'])) {
            return [
                'available' => true,
                'type' => $this->isEmbedUrl($tutorial['video_url']) ? 'embed' : 'url',
                'src' => $tutorial['video_url'],
            ];
        }

        $file = $tutorial['video'] ?? null;
        if (! $file) {
            return ['available' => false, 'type' => null, 'src' => null];
        }

        $relative = trim(config('learning.video_disk_path', 'videos/learning'), '/').'/'.$file;
        $absolute = public_path($relative);

        if (! is_file($absolute)) {
            return [
                'available' => false,
                'type' => 'file_missing',
                'src' => asset($relative),
            ];
        }

        return [
            'available' => true,
            'type' => 'file',
            'src' => asset($relative),
        ];
    }

    protected function isEmbedUrl(string $url): bool
    {
        return Str::contains($url, ['youtube.com', 'youtu.be', 'vimeo.com']);
    }
}
