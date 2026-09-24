<?php

namespace App\Services;

use App\Models\MediaConsent;
use App\Models\PrivacyConsent;
use App\Models\PrivacyRequest;
use App\Models\SecurityLog;
use App\Models\Term;
use App\Models\TermAcceptance;
use App\Models\TermVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LgpdConsentService
{
    public function ensureDefaultTerms(): void
    {
        $defaults = [
            Term::TYPE_TERMS_OF_USE => ['title' => 'Termos de Uso', 'required' => true],
            Term::TYPE_PRIVACY_POLICY => ['title' => 'Política de Privacidade', 'required' => true],
            Term::TYPE_MEDIA_CONSENT => ['title' => 'Consentimento de uso de imagem', 'required' => false],
        ];

        foreach ($defaults as $type => $meta) {
            Term::query()->firstOrCreate(
                ['type' => $type],
                [
                    'title' => $meta['title'],
                    'is_required' => $meta['required'],
                    'is_active' => true,
                ]
            );
        }
    }

    public function publishVersion(Term $term, string $version, string $title, string $content, ?User $publisher = null): TermVersion
    {
        return DB::transaction(function () use ($term, $version, $title, $content, $publisher) {
            $termVersion = TermVersion::query()->create([
                'term_id' => $term->id,
                'version' => $version,
                'title' => $title,
                'content' => $content,
                'published_by' => $publisher?->id,
            ]);

            $termVersion->publish($publisher);

            SecurityLog::record(
                'terms_publish',
                $publisher,
                TermVersion::class,
                (int) $termVersion->id,
                null,
                ['term_id' => $term->id, 'version' => $version]
            );

            return $termVersion;
        });
    }

    public function acceptTerm(User $user, TermVersion $version, Request $request): TermAcceptance
    {
        $acceptance = TermAcceptance::query()->create([
            'user_id' => $user->id,
            'term_id' => $version->term_id,
            'term_version_id' => $version->id,
            'accepted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_hash' => $version->content_hash,
        ]);

        SecurityLog::record(
            'terms_acceptance',
            $user,
            TermAcceptance::class,
            (int) $acceptance->id,
            null,
            ['term_id' => $version->term_id, 'term_version_id' => $version->id]
        );

        return $acceptance;
    }

    public function recordPrivacyConsent(
        User $user,
        string $status,
        Request $request,
        ?TermVersion $version = null,
        string $purpose = 'data_processing'
    ): PrivacyConsent {
        $consent = PrivacyConsent::query()->create([
            'user_id' => $user->id,
            'term_version_id' => $version?->id,
            'purpose' => $purpose,
            'status' => $status,
            'accepted_at' => $status === PrivacyConsent::STATUS_AUTHORIZED ? now() : null,
            'revoked_at' => $status === PrivacyConsent::STATUS_REVOKED ? now() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        SecurityLog::record('consent_change', $user, PrivacyConsent::class, (int) $consent->id, null, [
            'purpose' => $purpose,
            'status' => $status,
        ]);

        return $consent;
    }

    public function recordMediaConsent(
        User $user,
        string $status,
        Request $request,
        ?TermVersion $version = null,
        string $purpose = 'image_use'
    ): MediaConsent {
        $consent = MediaConsent::query()->create([
            'user_id' => $user->id,
            'term_version_id' => $version?->id,
            'purpose' => $purpose,
            'status' => $status,
            'accepted_at' => $status === MediaConsent::STATUS_AUTHORIZED ? now() : null,
            'revoked_at' => $status === MediaConsent::STATUS_REVOKED ? now() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        SecurityLog::record('consent_change', $user, MediaConsent::class, (int) $consent->id, null, [
            'purpose' => $purpose,
            'status' => $status,
        ]);

        return $consent;
    }

    public function revokeMediaConsent(User $user, Request $request): MediaConsent
    {
        return $this->recordMediaConsent($user, MediaConsent::STATUS_REVOKED, $request);
    }

    public function createPrivacyRequest(User $user, string $type, ?string $details = null): PrivacyRequest
    {
        $request = PrivacyRequest::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'status' => PrivacyRequest::STATUS_PENDING,
            'details' => $details,
        ]);

        SecurityLog::record('privacy_request', $user, PrivacyRequest::class, (int) $request->id, null, [
            'type' => $type,
        ]);

        return $request;
    }
}
