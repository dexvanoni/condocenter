<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseLeadsService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function fetchLeads(): Collection
    {
        $url = config('supabase.url');
        $key = config('supabase.key');
        $adminToken = config('supabase.leads_admin_token');
        $rpc = config('supabase.leads_rpc');

        if (!$url || !$key || !$adminToken) {
            throw new RuntimeException('Integração Supabase não configurada. Defina SUPABASE_URL, SUPABASE_KEY e SUPABASE_LEADS_ADMIN_TOKEN no .env.');
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders([
                    'apikey' => $key,
                    'Authorization' => 'Bearer '.$key,
                ])
                ->post("{$url}/rest/v1/rpc/{$rpc}", [
                    'admin_token' => $adminToken,
                ])
                ->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('message')
                ?? $exception->response?->body()
                ?? $exception->getMessage();

            throw new RuntimeException('Não foi possível carregar os leads do Supabase: '.$message, 0, $exception);
        }

        return collect($response->json())
            ->map(fn (array $lead) => [
                'id' => (string) ($lead['id'] ?? ''),
                'nome' => (string) ($lead['nome'] ?? ''),
                'email' => (string) ($lead['email'] ?? ''),
                'telefone' => (string) ($lead['telefone'] ?? ''),
                'condominio' => filled($lead['condominio'] ?? null) ? (string) $lead['condominio'] : null,
                'mensagem' => filled($lead['mensagem'] ?? null) ? (string) $lead['mensagem'] : null,
                'created_at' => (string) ($lead['created_at'] ?? ''),
            ])
            ->values();
    }

    public function whatsappUrl(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', (string) config('whatsapp.default_country_code', '55')) ?: '55';

        if (! str_starts_with($digits, $countryCode) && strlen($digits) <= 11) {
            $digits = $countryCode.$digits;
        }

        return 'https://wa.me/'.$digits;
    }
}
