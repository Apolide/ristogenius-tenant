<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Message;

class LeadsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString(); // es. new, open, won, lost...
        $leadId = $request->input('lead');                // uuid o int, dipende dal tuo schema
        $conversationId = $request->input('conversation'); // id conversazione selezionata

        // Sidebar: contatori rapidi per stato (facoltativi)
        $statusCounts = Lead::query()
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        // Lista leads (colonna centrale)
        // $leadsQuery = Lead::query()
        //     ->with([
        //         'contact:id,name,email,telegram_handle,lang',
        //         'latestMessage:id,conversation_id,contact_id,source,direction,channel,message_type,lang,payload,sent_at',
        //         'conversations:id,lead_id,contact_id,status,last_update,subject'
        //     ])
        //     ->orderByDesc('updated_at');

        // $leadsQuery = Lead::query()
        //     ->with([
        //         'contact:id,name,email,telegram_handle,lang',
        //         'latestMessage:id,lead_id,conversation_id,contact_id,source,direction,channel,message_type,lang,payload,sent_at',
        //         'conversation:id,contact_id,channel,external_thread_id,topic,status,last_update_at'
        //     ])
        //     ->orderByDesc('updated_at');

        $leadsQuery = Lead::query()
            ->with([
                'contact:id,name,email,telegram_handle,lang',
                'latestMessage' => function ($q) {
                    $q->select([
                        'messages.id',
                        'messages.lead_id',
                        'messages.conversation_id',
                        'messages.contact_id',
                        'messages.user_id',
                        'messages.source',
                        'messages.external_id',
                        'messages.direction',
                        'messages.channel',
                        'messages.message_type',
                        'messages.lang',
                        'messages.payload',
                        'messages.sent_at',
                        'messages.created_at',
                        'messages.updated_at',
                    ]);
                },
                'conversations:id,lead_id,contact_id,channel,external_thread_id,topic,status,last_update_at',
            ])
            ->orderByDesc('updated_at');


        if ($status) {
            $leadsQuery->where('status', $status);
        }

        $leads = $leadsQuery->paginate(25)->withQueryString();

        // Lead selezionato (se presente)
        $activeLead = null;
        $activeConversation = null;
        $messages = collect();

        // if ($leadId) {
        //     $activeLead = Lead::query()
        //         ->with(['contact', 'conversations' => function ($q) {
        //             $q->orderByDesc('last_update');
        //         }])
        //         ->find($leadId);

        //     if ($activeLead) {
        //         // Conversazione attiva: o quella richiesta, o la più recente del lead
        //         $activeConversation = $conversationId
        //             ? $activeLead->conversations()->where('id', $conversationId)->first()
        //             : $activeLead->conversations()->orderByDesc('last_update')->first();

        //         if ($activeConversation) {
        //             $messages = Message::query()
        //                 ->where('conversation_id', $activeConversation->id)
        //                 ->orderBy('sent_at')
        //                 ->get();
        //         }
        //     }
        // }

        if ($leadId) {
            $activeLead = Lead::query()
                ->with(['contact', 'conversation'])
                ->find($leadId);
        
            if ($activeLead && $activeLead->conversation) {
                $activeConversation = $conversationId
                    ? ($activeLead->conversation->id == $conversationId ? $activeLead->conversation : null)
                    : $activeLead->conversation;
        
                if ($activeConversation) {
                    $messages = Message::query()
                        ->where('conversation_id', $activeConversation->id)
                        ->orderBy('sent_at')
                        ->get();
                }
            }
        }

        return view('leads', [
            'leads' => $leads,
            'status' => $status,
            'statusCounts' => $statusCounts,
            'activeLead' => $activeLead,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
        ]);
    }
}
