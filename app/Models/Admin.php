<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $primaryKey = 'id_admin';
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
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array', // Cast JSON en array
    ];

    // Relation vers User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Vérifier si l'admin a une permission spécifique
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        // Pour les admins, on utilise le mot de passe de la table users liée
        return $this->user ? $this->user->mot_de_passe : null;
    }
}
