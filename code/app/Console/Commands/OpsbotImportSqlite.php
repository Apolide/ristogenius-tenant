<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class OpsbotImportSqlite extends Command
{
    // php artisan opsbot:import-sqlite
    protected $signature = 'opsbot:import-sqlite
        {--path= : Override OPSBOT_SQLITE_PATH}
        {--wipe : Truncate target tables before import (DANGEROUS)}
        {--limit=0 : Limit number of interactions imported (0 = no limit)}';

    protected $description = 'Import opsbot ops.sqlite (SQLite) into Laravel MySQL tables (contacts, conversations, messages, activity_logs)';

    public function handle(): int
    {
        $path = $this->option('path') ?: env('OPSBOT_SQLITE_PATH');
        if (!$path || !is_file($path)) {
            $this->error("SQLite file not found. Set OPSBOT_SQLITE_PATH or pass --path=/path/to/ops.sqlite");
            return self::FAILURE;
        }

        $this->info("Using SQLite: {$path}");
        $pdo = $this->openSqlitePdo($path);

        // optional wipe
        if ($this->option('wipe')) {
            $this->warn("WIPING target tables...");
            $this->wipeTargets();
        }

        // Preload useful info about target schema (avoid crashing if columns differ)
        $contactsCols = Schema::getColumnListing('contacts');
        $this->line("contacts columns: " . implode(',', $contactsCols));

        // Map old opsbot user IDs -> new contact UUID
        $userIdToContactId = [];

        // Map old thread_id (TEXT) -> new conversation bigint id
        $threadToConversationId = [];

        // Map old interaction numeric id -> new message bigint id
        $interactionIdToMessageId = [];

        // 1) Import users -> contacts
        $this->section("Importing users → contacts");
        $users = $this->sqliteAll($pdo, "SELECT id, name, email, telegram_handle, lang FROM users");
        $this->info("Found " . count($users) . " users in SQLite");

        // dd($users);

        foreach ($users as $u) {
            $email = strtolower(trim((string)($u['email'] ?? '')));

            if ($email !== '') {

                if ($email === '') {
                    // If no email, create a stable synthetic key (rare but possible)
                    $email = "no-email-opsbot-user-{$u['id']}@invalid.local";
                }

                $system_name = strtolower($u['name']);
                $system_name = str_replace(" ", "", $system_name);
                $system_name = Str::slug($system_name);
    
                
                $contactId = $this->findOrCreateContact($email, [
                    'id' => $u['id'] ?? null,
                    'name' => $u['name'] ?? null,
                    'telegram_handle' => $u['telegram_handle'] ?? null,
                    'lang' => $u['lang'] ?? null,
                ]);
    
                $userIdToContactId[(int)$u['id']] = $contactId;
    
            }
        }

        // 2) Import threads -> conversations
        $this->section("Importing threads → conversations");
        // NOTE: your opsbot threads table has (id TEXT PK, status, last_update, user_id, channel, topic, ...)
        $threads = $this->sqliteAll($pdo, "SELECT * FROM threads");
        $this->info("Found " . count($threads) . " threads in SQLite");

        foreach ($threads as $t) {
            $externalThreadId = (string)$t['id'];
            $existing = DB::table('conversations')->where('external_thread_id', $externalThreadId)->first();
            if ($existing) {
                $threadToConversationId[$externalThreadId] = (int)$existing->id;
                continue;
            }

            $contactId = null;
            if (isset($t['user_id']) && $t['user_id'] !== null) {
                $contactId = $userIdToContactId[(int)$t['user_id']] ?? null;
            }

            $insert = [
                'contact_id' => $contactId,
                'channel' => $t['channel'] ?? null,
                'external_thread_id' => $externalThreadId,
                'topic' => $t['topic'] ?? null,
                'status' => $t['status'] ?? 'open',
                'last_update_at' => $this->toNullableDateTime($t['last_update'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $convId = (int)DB::table('conversations')->insertGetId($insert);
            $threadToConversationId[$externalThreadId] = $convId;
        }

        // 3) Import interactions -> messages
        $this->section("Importing interactions → messages");
        $limit = (int)$this->option('limit');
        $sql = "SELECT * FROM interactions ORDER BY id ASC" . ($limit > 0 ? " LIMIT " . $limit : "");
        $interactions = $this->sqliteAll($pdo, $sql);
        $this->info("Found " . count($interactions) . " interactions in SQLite");

        $bar = $this->output->createProgressBar(count($interactions));
        $bar->start();


        // Cache conversazione->lead_id per ridurre query
        static $convLeadCache = [];


        foreach ($interactions as $i) {
            $bar->advance();

            $externalId = (string)($i['external_id'] ?? '');
            if ($externalId === '') {
                // fallback (should not happen): stable fallback id
                $externalId = 'opsbot:interaction:' . (string)$i['id'];
            }

            // idempotency
            $existing = DB::table('messages')->where('external_id', $externalId)->first();
            if ($existing) {
                $interactionIdToMessageId[(int)$i['id']] = (int)$existing->id;
                continue;
            }

            $threadId = (string)($i['thread_id'] ?? '');
            $conversationId = $threadId !== '' ? ($threadToConversationId[$threadId] ?? null) : null;
            if (!$conversationId) {
                // Create a placeholder conversation if missing thread
                $conversationId = (int)DB::table('conversations')->insertGetId([
                    'contact_id' => null,
                    'channel' => $i['channel'] ?? null,
                    'external_thread_id' => $threadId ?: ('missing-thread:' . $externalId),
                    'topic' => null,
                    'status' => 'open',
                    'last_update_at' => $this->toNullableDateTime($i['created_at'] ?? null),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $threadToConversationId[$threadId] = $conversationId;
            }

            // Try to infer contact from thread->contact (best)
            $contactId = DB::table('conversations')->where('id', $conversationId)->value('contact_id');

            // $payload = $this->decodeJsonSafe($i['payload_json'] ?? null);
            $payload = $i['payload_json'];

            // created_at in opsbot interactions is the message timestamp: we keep it in sent_at + created_at
            $sentAt = $this->toNullableDateTime($i['created_at'] ?? null);

            $insert = [
                'conversation_id' => $conversationId,
                'contact_id' => $contactId,
                'user_id' => null, // you can later map outbound drafts/sends to CRM agents
                'source' => $i['source'] ?? null,
                'external_id' => $externalId,
                'direction' => $i['direction'] ?? null,
                'channel' => $i['channel'] ?? null,
                'message_type' => $i['message_type'] ?? null,
                'lang' => $i['lang'] ?? null,
                'payload' => $payload,
                'sent_at' => $sentAt,
                'created_at' => $sentAt ?: now(),
                'updated_at' => $sentAt ?: now(),
            ];



            // $convId = $insert['conversation_id'] ?? null;
            // if ($convId) {
            //     if (!array_key_exists($convId, $convLeadCache)) {
            //         $convLeadCache[$convId] = DB::table('conversations')->where('id', $convId)->value('lead_id');
            //     }
            //     $insert['lead_id'] = $convLeadCache[$convId];
            // }



            $msgId = (int)DB::table('messages')->insertGetId($insert);

            // // Ensure payload is a JSON string for MySQL
            // if (isset($insert['payload']) && is_array($insert['payload'])) {
            //     $insert['payload'] = json_encode($insert['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            // }

            // // Also handle cases where payload is null/empty
            // if (!isset($insert['payload']) || $insert['payload'] === null) {
            //     // $insert['payload'] = json_encode(new stdClass());
            //     $insert['payload'] = null;
            // }


            $interactionIdToMessageId[(int)$i['id']] = $msgId;
        }

        $bar->finish();
        $this->newLine(2);

        // 4) Import actions_log -> activity_logs
        $this->section("Importing actions_log → activity_logs");
        $logs = $this->sqliteAll($pdo, "SELECT * FROM actions_log ORDER BY id ASC");
        $this->info("Found " . count($logs) . " actions_log rows in SQLite");

        $bar2 = $this->output->createProgressBar(count($logs));
        $bar2->start();

        foreach ($logs as $a) {
            $bar2->advance();

            $oldInteractionId = isset($a['interaction_id']) ? (int)$a['interaction_id'] : null;
            $messageId = $oldInteractionId ? ($interactionIdToMessageId[$oldInteractionId] ?? null) : null;

            // idempotency-ish: if we already have same (message_id, action, created_at) we skip
            $createdAt = $this->toNullableDateTime($a['created_at'] ?? null);
            $exists = DB::table('activity_logs')
                ->where('message_id', $messageId)
                ->where('action', (string)($a['action'] ?? ''))
                ->where('created_at', $createdAt ?: now())
                ->first();

            if ($exists) {
                continue;
            }

            DB::table('activity_logs')->insert([
                'message_id' => $messageId,
                'user_id' => null,
                'action' => $a['action'] ?? 'unknown',
                // 'details' => $this->decodeJsonSafe($a['details_json'] ?? null),
                'details' => $a['details_json'],
                'created_at' => $createdAt ?: now(),
                'updated_at' => $createdAt ?: now(),
            ]);
        }

        $bar2->finish();
        $this->newLine(2);

        $this->info("✅ Import completed.");
        $this->line("Contacts imported: " . DB::table('contacts')->count());
        $this->line("Conversations imported: " . DB::table('conversations')->count());
        $this->line("Messages imported: " . DB::table('messages')->count());
        $this->line("Activity logs imported: " . DB::table('activity_logs')->count());

        return self::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->info(str_repeat('-', 70));
        $this->info($title);
        $this->info(str_repeat('-', 70));
    }

    private function openSqlitePdo(string $path): \PDO
    {
        $pdo = new \PDO('sqlite:' . $path, null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }

    private function sqliteAll(\PDO $pdo, string $sql): array
    {
        $stmt = $pdo->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    private function decodeJsonSafe($value): ?array
    {
        if ($value === null) return null;
        $s = trim((string)$value);
        if ($s === '') return null;

        try {
            $decoded = json_decode($s, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function toNullableDateTime($value): ?string
    {
        if (!$value) return null;
        $v = trim((string)$value);
        if ($v === '') return null;

        // opsbot stores ISO strings (now_iso) typically.
        try {
            return \Carbon\Carbon::parse($v)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }

    private function wipeTargets(): void
    {
        // Disable FK checks for truncation order
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['activity_logs', 'messages', 'leads', 'conversations'] as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)->truncate();
            }
        }
        // contacts truncate only if you want. Here we keep contacts by default.
        // DB::table('contacts')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info("Targets wiped (except contacts by default).");
    }

    private function findOrCreateContact(string $email, array $attrs): string
    {
        // Try by email if the contacts table has it.
        $contact = DB::table('contacts')->where('email', $email)->first();
        if ($contact) {
            // Optionally update name/lang if missing
            $updates = [];
            if (isset($attrs['name']) && $attrs['name'] && empty($contact->name)) $updates['name'] = $attrs['name'];
            if (isset($attrs['lang']) && $attrs['lang'] && empty($contact->lang)) $updates['lang'] = $attrs['lang'];
            if (isset($attrs['telegram_handle']) && $attrs['telegram_handle'] && empty($contact->telegram_handle)) $updates['telegram_handle'] = $attrs['telegram_handle'];

            if ($updates) {
                $updates['updated_at'] = now();
                DB::table('contacts')->where('id', $contact->id)->update($updates);
            }
            return (string)$contact->id;
        }

        // Create new contact UUID
        $id = (string) Str::uuid();

        $systemName = $this->makeSystemName($attrs['name'] ?? null, $id);

        $insert = [
            'id' => $id,
            'system_name' => $systemName,
            'email' => $email,
            'name' => $attrs['name'],
            'telegram_handle' => $attrs['telegram_handle'] ?? null,
            'lang' => $attrs['lang'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
            'phone' => '060606',
        ];

        // Only set fields if the columns exist in your contacts table
        if (Schema::hasColumn('contacts', 'name')) $insert['name'] = $attrs['name'] ?? null;
        if (Schema::hasColumn('contacts', 'lang')) $insert['lang'] = $attrs['lang'] ?? null;
        if (Schema::hasColumn('contacts', 'telegram_handle')) $insert['telegram_handle'] = $attrs['telegram_handle'] ?? null;

        DB::table('contacts')->insert($insert);

        return $id;
    }

    protected function makeSystemName(?string $name, string $uuid): string
    {
        if ($name && trim($name) !== '') {
            return Str::slug($name);
        }

        return 'contact-' . substr($uuid, 0, 8);
    }
}
