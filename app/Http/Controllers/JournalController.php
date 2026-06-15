<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'log_name' => trim((string) $request->string('log_name')),
            'event' => trim((string) $request->string('event')),
        ];

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        if ($filters['search'] !== '') {
            $term = '%' . str_replace(' ', '%', $filters['search']) . '%';

            $query->where(function ($builder) use ($term) {
                $builder
                    ->where('description', 'like', $term)
                    ->orWhere('log_name', 'like', $term)
                    ->orWhere('event', 'like', $term)
                    ->orWhereHasMorph('causer', ['*'], fn ($q) => $q->where('name', 'like', $term))
                    ->orWhereHasMorph('subject', ['*'], fn ($q) => $q->where('id', 'like', $term));
            });
        }

        if ($filters['log_name'] !== '') {
            $query->where('log_name', $filters['log_name']);
        }

        if ($filters['event'] !== '') {
            $query->where('event', $filters['event']);
        }

        $activities = $query
            ->paginate(25)
            ->through(fn (Activity $activity) => [
                'model' => $activity,
                'event_label' => $this->formatEventLabel($activity->event),
                'description' => $this->buildDescription($activity),
                'subject_label' => $this->buildSubjectLabel($activity),
            ])
            ->withQueryString();

        $logNames = Activity::query()
            ->select('log_name')
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');

        $events = Activity::query()
            ->select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('pages.journal.index', compact('activities', 'filters', 'logNames', 'events'));
    }

    private function buildDescription(Activity $activity): string
    {
        $subject = $this->buildSubjectLabel($activity);
        $attributes = $this->sanitizeProperties(Arr::get($activity->properties, 'attributes', []));
        $old = $this->sanitizeProperties(Arr::get($activity->properties, 'old', []));

        return match ($activity->event) {
            'created' => $this->buildCreatedDescription($subject, $attributes),
            'updated' => $this->buildUpdatedDescription($subject, $old, $attributes),
            'deleted' => $this->buildDeletedDescription($subject, $old, $attributes),
            default => $activity->description ?: sprintf('%s sur %s', $this->formatEventLabel($activity->event), $subject),
        };
    }

    private function buildCreatedDescription(string $subject, array $attributes): string
    {
        if ($attributes === []) {
            return sprintf('Creation de %s.', $subject);
        }

        return sprintf(
            'Creation de %s avec les valeurs initiales suivantes : %s.',
            $subject,
            $this->summarizeFieldValues($attributes)
        );
    }

    private function buildUpdatedDescription(string $subject, array $old, array $attributes): string
    {
        $changes = [];

        foreach ($attributes as $field => $newValue) {
            $oldValue = $old[$field] ?? null;

            if ($oldValue === $newValue) {
                continue;
            }

            $changes[] = sprintf(
                '%s : %s -> %s',
                $this->humanizeFieldName($field),
                $this->formatValue($oldValue),
                $this->formatValue($newValue)
            );
        }

        if ($changes === []) {
            return sprintf('Mise a jour de %s.', $subject);
        }

        return sprintf('Mise a jour de %s. Modifications appliquees : %s.', $subject, implode('; ', $changes));
    }

    private function buildDeletedDescription(string $subject, array $old, array $attributes): string
    {
        $referenceValues = $old !== [] ? $old : $attributes;

        if ($referenceValues === []) {
            return sprintf('Suppression de %s.', $subject);
        }

        return sprintf(
            'Suppression de %s. Dernieres valeurs connues : %s.',
            $subject,
            $this->summarizeFieldValues($referenceValues)
        );
    }

    private function buildSubjectLabel(Activity $activity): string
    {
        if (! $activity->subject_type) {
            return 'element inconnu';
        }

        $label = class_basename($activity->subject_type);

        if ($activity->subject_id) {
            return sprintf('%s #%s', $label, $activity->subject_id);
        }

        return $label;
    }

    private function formatEventLabel(?string $event): string
    {
        return match ($event) {
            'created' => 'Creation',
            'updated' => 'Mise a jour',
            'deleted' => 'Suppression',
            default => Str::headline((string) $event),
        };
    }

    private function sanitizeProperties(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->except(['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'])
            ->all();
    }

    private function summarizeFieldValues(array $values): string
    {
        $items = [];

        foreach ($values as $field => $value) {
            $items[] = sprintf('%s=%s', $this->humanizeFieldName((string) $field), $this->formatValue($value));
        }

        if (count($items) <= 5) {
            return implode('; ', $items);
        }

        $visibleItems = array_slice($items, 0, 5);

        return sprintf('%s; +%d autre(s) champ(s)', implode('; ', $visibleItems), count($items) - 5);
    }

    private function humanizeFieldName(string $field): string
    {
        return Str::of($field)
            ->replace('_', ' ')
            ->headline()
            ->toString();
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'vide';
        }

        if (is_bool($value)) {
            return $value ? 'oui' : 'non';
        }

        if (is_array($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $json === false ? '[donnee complexe]' : $json;
        }

        if (is_object($value)) {
            return '[objet]';
        }

        return (string) $value;
    }
}
