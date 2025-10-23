<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CompteQueryScope;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_compte';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
        });

        // Appliquer le scope global CompteQueryScope
        static::addGlobalScope(new CompteQueryScope());
    }

    protected $fillable = 
    [
        'numero_compte',
        'id_client',
        'titulaire',
        'type_compte',
        'solde',
        'statut',
        'devise',
        'motifBlocage',
        'metadata',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'date_creation' => 'datetime',
        'type_compte' => 'string',
        'statut' => 'string',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($compte) {
            $compte->numero_compte = 'CPT' . rand(100000, 999999); // Numéro unique
        });
    }

    // Un compte appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }

    // Un compte peut avoir plusieurs transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'id_compte', 'id_compte');
    }

    // Vérifier si le compte est actif
    public function isActive()
    {
        return $this->statut === 'actif';
    }

    // Les scopes sont maintenant dans CompteQueryScope
}
