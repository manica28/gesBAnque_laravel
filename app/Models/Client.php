<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

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
        'password',
        'code',
        'solde_initial',
    ];

    protected $attributes = [
        'nci' => null,
    ];

    protected $casts = [
        'solde_initial' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
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
