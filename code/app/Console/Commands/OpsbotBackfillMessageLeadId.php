<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OpsbotBackfillMessageLeadId extends Command
{
    protected $signature = 'opsbot:backfill-message-lead-id {--dry-run} {--chunk=5000}';
    protected $description = 'Backfill messages.lead_id from conversations.lead_id';

    public function handle(): int
    {
        $dry = (bool)$this->option('dry-run');
        $chunk = max(100, (int)$this->option('chunk'));

        // Conta quanti messaggi sono da aggiornare (lead_id null + conversation con lead_id)
        // $toFix = DB::table('messages as m')
        //     ->join('conversations as c', 'c.id', '=', 'm.conversation_id')
        //     ->whereNull('m.lead_id')
        //     ->whereNotNull('c.lead_id')
        //     ->count();

        $toFix = DB::table('messages as m')
            ->join('leads as l', 'l.conversation_id', '=', 'm.conversation_id')
            ->whereNull('m.lead_id')
            ->count();

        $this->info("Messages to backfill: {$toFix}");

        if ($toFix === 0) {
            return self::SUCCESS;
        }

        // Se dry-run, stop qui
        if ($dry) {
            $this->warn("Dry-run enabled: no changes will be written.");
            return self::SUCCESS;
        }

        // Strategia semplice e veloce (MySQL): update join in un colpo solo.
        // Nota: query raw per fare UPDATE ... JOIN
        // $updated = DB::update("
        //     UPDATE messages m
        //     JOIN conversations c ON c.id = m.conversation_id
        //     SET m.lead_id = c.lead_id
        //     WHERE m.lead_id IS NULL AND c.lead_id IS NOT NULL
        // ");

        $updated = DB::statement("
            UPDATE messages m
            JOIN leads l ON l.conversation_id = m.conversation_id
            SET m.lead_id = l.id
            WHERE m.lead_id IS NULL
        ");

        $this->info("Updated rows: {$updated}");

        return self::SUCCESS;
    }
}
