<?php

namespace App\Concerns;

use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait LogsActivityWithDefaults
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $attributes = collect($this->getFillable())
            ->reject(fn (string $attribute) => in_array($attribute, ['password', 'remember_token'], true))
            ->values()
            ->all();

        return LogOptions::defaults()
            ->useLogName(Str::snake(class_basename($this)))
            ->logOnly($attributes === [] ? ['*'] : $attributes)
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => $this->buildActivityDescription($eventName));
    }

    private function buildActivityDescription(string $eventName): string
    {
        $modelName = class_basename($this);

        return match ($eventName) {
            'created' => sprintf('Creation de %s', $modelName),
            'updated' => sprintf('Mise a jour de %s', $modelName),
            'deleted' => sprintf('Suppression de %s', $modelName),
            default => sprintf('%s : %s', $modelName, Str::headline($eventName)),
        };
    }
}
