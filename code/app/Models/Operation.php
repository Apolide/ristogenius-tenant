<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;


class Operation extends Model
{
    use HasFactory;


    protected $fillable = ['title', 'message', 'url', 'type'];

    /**
     * I ruoli associati a questa notifica.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'operation_role');
    }

    /**
     * Gli utenti che hanno ricevuto questa notifica.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_operation')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

}
