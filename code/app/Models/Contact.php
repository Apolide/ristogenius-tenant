<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
        
class Contact extends Model
{
    use HasUuids;
    use HasFactory;

    protected $fillable = [
        'name',
        'system_name',
        'email',
        'phone',
        'company_name',
        'piva',
        'riferimento_mandato',
        'region_id',
        'province_id',
        'comuni_id',
        'address',
        'legal_officer',
        'legal_address',
        'legal_phone',
        'legal_email',
    ];

    protected $casts = [

    ];


    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }    
}