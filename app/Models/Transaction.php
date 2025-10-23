<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_transaction';
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
        'id_compte',
        'type_transaction',
        'montant',
        'statut',
        'description',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_transaction' => 'datetime',
        'type_transaction' => 'string',
        'statut' => 'string',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'id_compte', 'id_compte');
    }

    // Vérifier si la transaction est réussie
    public function isSuccessful()
    {
        return $this->statut === 'success';
    }
}
