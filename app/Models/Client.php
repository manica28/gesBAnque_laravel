<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Client extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $primaryKey = 'id_client';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
        });
    }

    protected $fillable = [
        'id_user',
        'nci',
        'email',
        'telephone',
        'adresse',
        'titulaire',
        'mot_de_passe',
        'code',
        'solde_initial',
    ];

    protected $attributes = [
        'nci' => null,
    ];

    protected $casts = [
        'solde_initial' => 'decimal:2',
    ];

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    protected $hidden = [
        'mot_de_passe',
        'code',
    ];

    // Relation vers User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Un client peut avoir plusieurs comptes
    public function comptes()
    {
        return $this->hasMany(Compte::class, 'id_client', 'id_client');
    }
}
