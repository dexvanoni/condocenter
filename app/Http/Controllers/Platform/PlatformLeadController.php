<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\SupabaseLeadsService;
use Illuminate\View\View;
use RuntimeException;

class PlatformLeadController extends Controller
{
    public function index(SupabaseLeadsService $leadsService): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $error = null;
        $leads = collect();

        try {
            $leads = $leadsService->fetchLeads()
                ->map(function (array $lead) use ($leadsService) {
                    $lead['whatsapp_url'] = $leadsService->whatsappUrl($lead['telefone']);

                    return $lead;
                });
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        }

        return view('platform.leads.index', [
            'leads' => $leads,
            'error' => $error,
        ]);
    }
}
