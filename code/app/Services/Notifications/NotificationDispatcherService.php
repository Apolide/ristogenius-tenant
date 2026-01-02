<?php

namespace App\Services\Notifications;

use App\Services\Message\MessageService;
use App\Services\Funds\TenantFundsService;
use App\Jobs\Notifications\SendMailJob;
use App\Jobs\Notifications\SendSmsJob;
use App\Jobs\Notifications\SendWhatsappTemplateJob;
use App\Jobs\Notifications\SendTelegramMessageJob;
use Illuminate\Support\Facades\Log;

class NotificationDispatcherService
{
    protected MessageService $messageService;
    protected TenantFundsService $fundsService;

    public function __construct(MessageService $messageService, TenantFundsService $fundsService)
    {
        $this->messageService = $messageService;
        $this->fundsService = $fundsService;
    }

    /**
     * Invia notifiche su tutti i canali abilitati, verificando i fondi per ogni canale.
     *
     * @param array $data I dati della notifica
     * @param string $reason Il motivo dell'invio (transactional, marketing, etc.)
     * @param string $context Contesto per il logging (booking_created, booking_canceled, etc.)
     * @return array Risultato dell'operazione con dettagli per canale
     */
    public function dispatchNotifications(array $data, string $reason = 'transactional', string $context = 'notification'): array
    {
        $channels = json_decode($data["channels"], true);
        $tenantId = $data["tenant_id"];
        
        $results = [
            'sent' => [],
            'failed' => [],
            'insufficient_funds' => [],
            'skipped' => []
        ];

        // EMAIL
        if ($channels["messages_channels"]["email"] ?? false) {
            $result = $this->processChannel($tenantId, 'email', $reason, function() use ($data) {
                SendMailJob::dispatch($data);
            });
            $this->categorizeResult($result, 'email', $results);
        }

        // WHATSAPP
        if ($channels["messages_channels"]["whatsapp"] ?? false) {
            $result = $this->processChannel($tenantId, 'whatsapp', $reason, function() use ($data) {
                SendWhatsappTemplateJob::dispatch($data);
            });
            $this->categorizeResult($result, 'whatsapp', $results);
        }

        // TELEGRAM
        if ($channels["messages_channels"]["telegram"] ?? false) {
            $result = $this->processChannel($tenantId, 'telegram', $reason, function() use ($data) {
                SendTelegramMessageJob::dispatch($data);
            });
            $this->categorizeResult($result, 'telegram', $results);
        }

        // SMS (gestione speciale per blocchi multipli)
        if ($channels['messages_channels']['sms'] ?? false) {
            $result = $this->processSmsChannel($tenantId, $data, $reason);
            $this->categorizeResult($result, 'sms', $results);
        }

        // Log dei risultati
        $this->logResults($tenantId, $results, $context, $data);

        // Gestisci casi speciali
        $this->handleSpecialCases($tenantId, $results, $data);

        return $results;
    }

    /**
     * Processa un singolo canale di notifica
     */
    protected function processChannel(string $tenantId, string $channel, string $reason, callable $sendCallback): array
    {
        try {
            // Verifica se può inviare
            $canSend = $this->messageService->canSendMessage($tenantId, $channel, $reason);
            
            if (!$canSend['can_send']) {
                return [
                    'status' => 'insufficient_funds',
                    'channel' => $channel,
                    'required' => $canSend['cost'],
                    'available' => $canSend['current_balance'] ?? 0,
                    'reason' => $canSend['reason']
                ];
            }

            // Tenta l'invio con decurtazione fondi
            $result = $this->messageService->sendMessage($tenantId, $channel, $reason);
            
            if (!$result['success']) {
                return [
                    'status' => 'failed',
                    'channel' => $channel,
                    'error' => $result['error'] ?? 'unknown_error',
                    'cost' => $result['cost'] ?? 0
                ];
            }

            // Esegui l'invio effettivo
            $sendCallback();

            return [
                'status' => 'sent',
                'channel' => $channel,
                'cost' => $result['cost'],
                'message_id' => $result['message']->id ?? null
            ];

        } catch (\Exception $e) {
            Log::error("Error processing {$channel} channel for tenant {$tenantId}: " . $e->getMessage());
            
            return [
                'status' => 'failed',
                'channel' => $channel,
                'error' => 'processing_exception',
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Processa il canale SMS con gestione dei blocchi multipli
     */
    protected function processSmsChannel(string $tenantId, array $data, string $reason): array
    {
        try {
            if (!isset($data['sms_body'])) {
                return [
                    'status' => 'skipped',
                    'channel' => 'sms',
                    'reason' => 'missing_sms_body'
                ];
            }

            // Calcola blocchi SMS necessari
            $body = $data['sms_body'];
            $length = mb_strlen($body);
            $chunks = (int) ceil($length / 150);
            
            // Verifica fondi per tutti i blocchi
            $totalCost = 0;
            for ($i = 0; $i < $chunks; $i++) {
                $canSend = $this->messageService->canSendMessage($tenantId, 'sms', $reason);
                if (!$canSend['can_send']) {
                    return [
                        'status' => 'insufficient_funds',
                        'channel' => 'sms',
                        'required' => $canSend['cost'] * $chunks,
                        'available' => $canSend['current_balance'] ?? 0,
                        'chunks' => $chunks,
                        'reason' => 'insufficient_funds_for_all_chunks'
                    ];
                }
                $totalCost += $canSend['cost'];
            }

            // Processa tutti i blocchi
            $processedChunks = 0;
            for ($i = 0; $i < $chunks; $i++) {
                $result = $this->messageService->sendMessage($tenantId, 'sms', $reason);
                if (!$result['success']) {
                    // Se un blocco fallisce, logga ma continua (i fondi sono già stati decurtati)
                    Log::warning("SMS chunk {$i}/{$chunks} failed for tenant {$tenantId}: " . ($result['error'] ?? 'unknown'));
                    break;
                }
                $processedChunks++;
            }

            if ($processedChunks === $chunks) {
                // Tutti i blocchi processati, invia SMS
                SendSmsJob::dispatch($data);
                
                return [
                    'status' => 'sent',
                    'channel' => 'sms',
                    'cost' => $totalCost,
                    'chunks' => $chunks,
                    'processed_chunks' => $processedChunks
                ];
            } else {
                return [
                    'status' => 'failed',
                    'channel' => 'sms',
                    'error' => 'partial_chunk_processing',
                    'chunks' => $chunks,
                    'processed_chunks' => $processedChunks,
                    'cost' => $processedChunks * ($totalCost / $chunks) // Costo dei blocchi processati
                ];
            }

        } catch (\Exception $e) {
            Log::error("Error processing SMS channel for tenant {$tenantId}: " . $e->getMessage());
            
            return [
                'status' => 'failed',
                'channel' => 'sms',
                'error' => 'processing_exception',
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Categorizza il risultato nei bucket appropriati
     */
    protected function categorizeResult(array $result, string $channel, array &$results): void
    {
        switch ($result['status']) {
            case 'sent':
                $results['sent'][] = $result;
                break;
            case 'failed':
                $results['failed'][] = $result;
                break;
            case 'insufficient_funds':
                $results['insufficient_funds'][] = $result;
                break;
            case 'skipped':
                $results['skipped'][] = $result;
                break;
        }
    }

    /**
     * Log dei risultati dell'invio
     */
    protected function logResults(string $tenantId, array $results, string $context, array $data): void
    {
        $summary = [
            'sent_count' => count($results['sent']),
            'failed_count' => count($results['failed']),
            'insufficient_funds_count' => count($results['insufficient_funds']),
            'skipped_count' => count($results['skipped'])
        ];

        if ($summary['sent_count'] > 0) {
            Log::info("Notifications sent successfully", [
                'tenant_id' => $tenantId,
                'context' => $context,
                'summary' => $summary,
                'sent_channels' => array_column($results['sent'], 'channel'),
                'reference_data' => $this->extractReferenceData($data)
            ]);
        }

        if ($summary['failed_count'] > 0) {
            Log::error("Some notifications failed to send", [
                'tenant_id' => $tenantId,
                'context' => $context,
                'summary' => $summary,
                'failed_details' => $results['failed']
            ]);
        }

        if ($summary['insufficient_funds_count'] > 0) {
            Log::warning("Notifications blocked due to insufficient funds", [
                'tenant_id' => $tenantId,
                'context' => $context,
                'summary' => $summary,
                'insufficient_funds_details' => $results['insufficient_funds']
            ]);
        }
    }

    /**
     * Gestisce casi speciali come tutti i canali bloccati
     */
    protected function handleSpecialCases(string $tenantId, array $results, array $data): void
    {
        // Se tutti i canali sono bloccati per fondi insufficienti
        if (empty($results['sent']) && !empty($results['insufficient_funds'])) {
            $this->handleAllChannelsBlocked($tenantId, $results['insufficient_funds'], $data);
        }

        // Se ci sono fondi insufficienti su alcuni canali, notifica
        if (!empty($results['insufficient_funds'])) {
            $this->notifyInsufficientFunds($tenantId, $results['insufficient_funds']);
        }
    }

    /**
     * Gestisce il caso in cui tutti i canali sono bloccati
     */
    protected function handleAllChannelsBlocked(string $tenantId, array $insufficientFunds, array $data): void
    {
        Log::critical("All notification channels blocked due to insufficient funds", [
            'tenant_id' => $tenantId,
            'reference_data' => $this->extractReferenceData($data),
            'insufficient_funds' => $insufficientFunds
        ]);

        // Qui potresti implementare una strategia di fallback
        // come inviare una notifica agli amministratori
    }

    /**
     * Notifica quando ci sono fondi insufficienti
     */
    protected function notifyInsufficientFunds(string $tenantId, array $insufficientFunds): void
    {
        $totalRequired = collect($insufficientFunds)->sum('required');
        $currentBalance = collect($insufficientFunds)->first()['available'] ?? 0;
        
        Log::info("Insufficient funds notification triggered", [
            'tenant_id' => $tenantId,
            'total_required' => $totalRequired,
            'current_balance' => $currentBalance,
            'affected_channels' => array_column($insufficientFunds, 'channel')
        ]);

        // Potresti triggerare qui un job per notifiche sui fondi bassi
    }

    /**
     * Estrae dati di riferimento per il logging
     */
    protected function extractReferenceData(array $data): array
    {
        return array_filter([
            'email' => $data['email'] ?? null,
            'booking_date' => $data['booking_date'] ?? null,
            'booking_timeslot' => $data['booking_timeslot'] ?? null,
            'tenant_name' => $data['tenant_name'] ?? null,
        ]);
    }

    /**
     * Verifica fondi disponibili per tutti i canali senza inviare
     */
    public function checkFundsForAllChannels(array $data, string $reason = 'transactional'): array
    {
        $channels = json_decode($data["channels"], true);
        $tenantId = $data["tenant_id"];
        
        $fundsCheck = [
            'sufficient_funds' => true,
            'channels' => [],
            'total_required' => 0,
            'current_balance' => 0
        ];

        $funds = $this->fundsService->getTenantFunds($tenantId);
        $fundsCheck['current_balance'] = $funds->current_balance;

        foreach (['email', 'whatsapp', 'telegram', 'sms'] as $channel) {
            if ($channels["messages_channels"][$channel] ?? false) {
                $canSend = $this->messageService->canSendMessage($tenantId, $channel, $reason);
                
                $channelCost = $canSend['cost'];
                if ($channel === 'sms' && isset($data['sms_body'])) {
                    $chunks = (int) ceil(mb_strlen($data['sms_body']) / 150);
                    $channelCost *= $chunks;
                }
                
                $fundsCheck['channels'][$channel] = [
                    'required' => $channelCost,
                    'can_send' => $canSend['can_send']
                ];
                
                $fundsCheck['total_required'] += $channelCost;
                
                if (!$canSend['can_send']) {
                    $fundsCheck['sufficient_funds'] = false;
                }
            }
        }

        return $fundsCheck;
    }
}