<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciceRelance extends Model
{
    use HasFactory;

    protected $table = 'exercice_relances';

    protected $fillable = [
        'exercice_id',
        'palier',
        'destinataires',
        'envoye_le',
    ];

    protected function casts(): array
    {
        return [
            'palier' => 'integer',
            'destinataires' => 'integer',
            'envoye_le' => 'datetime',
        ];
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }
}
