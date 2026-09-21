<?php

namespace App\Console\Commands;

use App\Models\MessageAttachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PurgeCommunicationsCommand extends Command
{
    protected $signature = 'communications:purge
                            {--force : Confirma exclusão irreversível de todas as conversas e mensagens}';

    protected $description = 'Remove todas as conversas (diretas, avisos, sigilosas), mensagens, anexos e reuniões vinculadas';

    public function handle(): int
    {
        if (!$this->option('force')) {
            $this->error('Operação destrutiva. Use: php artisan communications:purge --force');

            return self::FAILURE;
        }

        if (!app()->environment(['local', 'testing']) && !$this->confirm('Ambiente não-local. Confirma apagar TODA a comunicação do banco?')) {
            $this->warn('Cancelado.');

            return self::FAILURE;
        }

        $paths = MessageAttachment::query()->pluck('path')->filter()->all();
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }

        Schema::disableForeignKeyConstraints();

        DB::table('message_attachments')->delete();
        DB::table('meetings')->delete();
        DB::table('messages')->delete();
        DB::table('conversation_participants')->delete();
        DB::table('conversation_recipients')->delete();
        DB::table('conversations')->delete();

        Schema::enableForeignKeyConstraints();

        $this->info('Comunicação apagada: conversas, mensagens, participantes, destinatários, anexos e reuniões.');

        return self::SUCCESS;
    }
}
