<?php

namespace App\Services\Operation;

use App\Models\Operation;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OperationService
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
     * @return Operation
     * @throws \Exception
     */
    public function createAndDispatch(string $title, string $message, string $url, $roles = ['all'])
    {
        DB::beginTransaction();

        try {
            // Crea la notifica
            $operation = Operation::create([
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
            $operation->roles()->attach($roleModels);

            // Recupera gli utenti appartenenti ai ruoli destinatari
            $users = User::role($roles)->get();

            // Assegna la notifica agli utenti
            foreach ($users as $user) {
                $user->operations()->attach($operation->id);
            }

            DB::commit();

            return $operation;
        } catch (\Exception $e) {
            DB::rollBack();
            // Gestisci l'errore come preferisci
            throw $e;
        }
    }
}
