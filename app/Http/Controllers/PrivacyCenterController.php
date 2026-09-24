<?php

namespace App\Http\Controllers;

use App\Models\MediaConsent;
use App\Models\PrivacyConsent;
use App\Models\PrivacyRequest;
use App\Models\Term;
use App\Models\TermAcceptance;
use App\Services\LgpdConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivacyCenterController extends Controller
{
    public function __construct(private LgpdConsentService $lgpd) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $acceptances = TermAcceptance::query()
            ->with(['term', 'version'])
            ->where('user_id', $user->id)
            ->orderByDesc('accepted_at')
            ->get();

        $privacyConsents = PrivacyConsent::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $mediaConsents = MediaConsent::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $privacyRequests = PrivacyRequest::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $this->lgpd->ensureDefaultTerms();
        $activeTerms = Term::query()
            ->where('is_active', true)
            ->get()
            ->map(fn (Term $term) => [
                'term' => $term,
                'version' => $term->activeVersion(),
            ]);

        return view('privacy.index', compact(
            'acceptances',
            'privacyConsents',
            'mediaConsents',
            'privacyRequests',
            'activeTerms'
        ));
    }

    public function accept(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'term_version_id' => ['required', 'exists:term_versions,id'],
            'accept_privacy_processing' => ['nullable', 'boolean'],
            'accept_media' => ['nullable', 'boolean'],
        ]);

        $version = \App\Models\TermVersion::query()->findOrFail($data['term_version_id']);
        abort_unless($version->is_active, 422);

        $this->lgpd->acceptTerm($user, $version, $request);

        if ($request->boolean('accept_privacy_processing')) {
            $this->lgpd->recordPrivacyConsent($user, PrivacyConsent::STATUS_AUTHORIZED, $request, $version);
        }

        if ($request->has('accept_media')) {
            $status = $request->boolean('accept_media')
                ? MediaConsent::STATUS_AUTHORIZED
                : MediaConsent::STATUS_DENIED;
            $this->lgpd->recordMediaConsent($user, $status, $request, $version);
        }

        return back()->with('success', 'Aceite registrado.');
    }

    public function revokeMedia(Request $request): RedirectResponse
    {
        $this->lgpd->revokeMediaConsent($request->user(), $request);

        return back()->with('success', 'Consentimento de imagem revogado.');
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:export,correction,deletion'],
            'details' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->lgpd->createPrivacyRequest($request->user(), $data['type'], $data['details'] ?? null);

        return back()->with('success', 'Solicitação registrada. A equipe analisará o pedido.');
    }
}
