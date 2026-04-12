<?php

namespace App\Support;

use App\Models\Exercice;
use Illuminate\Support\Facades\Session;

class ActiveExercice
{
    public const SESSION_KEY = 'exercice_actif_id';

    public static function id(): ?int
    {
        if (Session::has(self::SESSION_KEY)) {
            $id = (int) Session::get(self::SESSION_KEY);
            if (Exercice::query()->whereKey($id)->exists()) {
                return $id;
            }
        }

        $default = Exercice::query()->actif()->ordered()->value('id');

        return $default ? (int) $default : Exercice::query()->ordered()->value('id');
    }

    public static function model(): ?Exercice
    {
        $id = self::id();

        return $id ? Exercice::query()->find($id) : null;
    }

    public static function set(?int $exerciceId): void
    {
        if ($exerciceId === null) {
            Session::forget(self::SESSION_KEY);

            return;
        }

        Session::put(self::SESSION_KEY, $exerciceId);
    }
}
