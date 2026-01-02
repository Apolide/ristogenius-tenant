<?php

namespace App\Services\Notification;

use App\Models\Notification;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Crea e distribuisce una notifica.
     *
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array|string $roles
     *      - Array di nomi di ruoli (es. ['admin', 'manager'])
     *      - 'all' per inviare a tutti i ruoli
     * @return Notification
     * @throws \Exception
     */
    public function createAndDispatch(string $title, string $message, string $url, $roles = ['all'])
    {
        DB::beginTransaction();

        try {
            // Crea la notifica
            $notification = Notification::create([
                'title' => $title,
                'message' => $message,
                'url' => $url,
            ]);

            // Determina i ruoli destinatari
            if (is_string($roles) && strtolower($roles) === 'all') {
                // Recupera tutti i ruoli
                $roles = Role::all()->pluck('name')->toArray();
            } elseif (is_array($roles)) {
                // Verifica che i ruoli esistano
                $roles = Role::whereIn('name', $roles)->pluck('name')->toArray();
            } else {
                throw new \Exception('Parametro dei ruoli non valido.');
            }

            if (empty($roles)) {
                throw new \Exception('Nessun ruolo valido trovato per la notifica.');
            }

            // Assegna la notifica ai ruoli
            $roleModels = Role::whereIn('name', $roles)->get();
            $notification->roles()->attach($roleModels);

            // Recupera gli utenti appartenenti ai ruoli destinatari
            $users = User::role($roles)->get();

            // Assegna la notifica agli utenti
            foreach ($users as $user) {
                $user->notifications()->attach($notification->id);
            }

            DB::commit();

            return $notification;
        } catch (\Exception $e) {
            DB::rollBack();
            // Gestisci l'errore come preferisci
            throw $e;
        }
    }
}
