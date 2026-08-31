<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->enum('channel', ['peer', 'syndic'])->nullable()->after('type');
            $table->timestamp('resident_first_message_at')->nullable()->after('closed_by');
            $table->timestamp('syndic_first_response_at')->nullable()->after('resident_first_message_at');

            $table->index(['condominium_id', 'channel']);
        });

        $this->backfillChannels();
        $this->removeAdminsFromSyndicConversations();
        $this->backfillResponseTimestamps();
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['condominium_id', 'channel']);
            $table->dropColumn(['channel', 'resident_first_message_at', 'syndic_first_response_at']);
        });
    }

    private function backfillChannels(): void
    {
        $directIds = DB::table('conversations')
            ->where('type', 'direct')
            ->pluck('id');

        foreach ($directIds as $conversationId) {
            $participantUserIds = DB::table('conversation_participants')
                ->where('conversation_id', $conversationId)
                ->pluck('user_id');

            $isSyndic = $this->conversationHasAssignedRole($participantUserIds, 'Síndico');
            $isAdmin = $this->conversationHasAssignedRole($participantUserIds, 'Administrador');
            $participantCount = $participantUserIds->count();

            $channel = ($isSyndic && ($participantCount > 2 || $isAdmin)) ? 'syndic' : 'peer';

            DB::table('conversations')
                ->where('id', $conversationId)
                ->update(['channel' => $channel]);
        }
    }

    private function removeAdminsFromSyndicConversations(): void
    {
        $syndicConversationIds = DB::table('conversations')
            ->where('channel', 'syndic')
            ->pluck('id');

        if ($syndicConversationIds->isEmpty()) {
            return;
        }

        $adminUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'Administrador')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id');

        DB::table('conversation_participants')
            ->whereIn('conversation_id', $syndicConversationIds)
            ->whereIn('user_id', $adminUserIds)
            ->delete();
    }

    private function backfillResponseTimestamps(): void
    {
        $syndicConversationIds = DB::table('conversations')
            ->where('channel', 'syndic')
            ->pluck('id');

        foreach ($syndicConversationIds as $conversationId) {
            $messages = DB::table('messages')
                ->where('conversation_id', $conversationId)
                ->orderBy('created_at')
                ->get(['from_user_id', 'created_at']);

            if ($messages->isEmpty()) {
                continue;
            }

            $ownerId = DB::table('conversation_participants')
                ->where('conversation_id', $conversationId)
                ->where('role', 'owner')
                ->value('user_id');

            $residentFirst = null;
            $syndicFirst = null;

            foreach ($messages as $message) {
                if ($this->userHasAssignedRole((int) $message->from_user_id, 'Síndico')) {
                    if (!$syndicFirst) {
                        $syndicFirst = $message->created_at;
                    }
                    continue;
                }

                if (!$residentFirst && (int) $message->from_user_id === (int) $ownerId) {
                    $residentFirst = $message->created_at;
                }
            }

            if ($residentFirst || $syndicFirst) {
                DB::table('conversations')
                    ->where('id', $conversationId)
                    ->update([
                        'resident_first_message_at' => $residentFirst,
                        'syndic_first_response_at' => $syndicFirst,
                    ]);
            }
        }
    }

    private function conversationHasAssignedRole($userIds, string $roleName): bool
    {
        foreach ($userIds as $userId) {
            if ($this->userHasAssignedRole((int) $userId, $roleName)) {
                return true;
            }
        }

        return false;
    }

    private function userHasAssignedRole(int $userId, string $roleName): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('model_has_roles.model_id', $userId)
            ->where('roles.name', $roleName)
            ->exists();
    }
};
