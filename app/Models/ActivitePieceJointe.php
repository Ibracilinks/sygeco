<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitePieceJointe extends Model
{
    use HasFactory;

    protected $table = 'activite_pieces_jointes';

    protected $fillable = [
        'activite_id',
        'user_id',
        'nom_original',
        'chemin',
        'mime_type',
        'taille',
        'description',
    ];

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTailleLisibleAttribute(): string
    {
        $size = (float) $this->taille;
        $units = ['o', 'Ko', 'Mo', 'Go'];

        foreach ($units as $index => $unit) {
            if ($size < 1024 || $index === array_key_last($units)) {
                return number_format($size, $unit === 'o' ? 0 : 1, ',', ' ') . ' ' . $unit;
            }

            $size /= 1024;
        }

        return number_format($size, 1, ',', ' ') . ' Go';
    }
}
