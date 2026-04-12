<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationHistorique extends Model
{
    use HasFactory;

    protected $table = 'validation_historiques';

    protected $fillable = [
        'activite_id',
        'action',
        'utilisateur_id',
        'commentaire',
        'ancien_statut',
        'nouveau_statut',
    ];

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
