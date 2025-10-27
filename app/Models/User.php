<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_user';
    public $incrementing = false;  // pas auto-incrément
    protected $keyType = 'string';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'mot_de_passe',
        'type_user',
        'statut',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        // pas de cast int pour id_user
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->id_user) {
                $user->id_user = (string) Str::uuid();
            }
        });
    }
}
