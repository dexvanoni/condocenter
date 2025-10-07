<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheckController extends Controller
{
    public function index()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'checks' => [],
        ];

        // Verificar banco de dados
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        // Verificar cache
        try {
            Cache::put('health_check', true, 10);
            $cacheWorks = Cache::get('health_check');
            
            $health['checks']['cache'] = [
                'status' => $cacheWorks ? 'ok' : 'error',
                'message' => $cacheWorks ? 'Cache working' : 'Cache not working'
            ];
        } catch (\Exception $e) {
            $health['checks']['cache'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        // Verificar storage
        $health['checks']['storage'] = [
            'status' => is_writable(storage_path()) ? 'ok' : 'error',
            'message' => is_writable(storage_path()) ? 'Storage writable' : 'Storage not writable'
        ];

        // Informações do sistema
        $health['info'] = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
        ];

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }
}

