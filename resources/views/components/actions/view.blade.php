@props(['href', 'label' => 'Voir', 'iconOnly' => false])

<x-action variant="view" icon="eye" :href="$href" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
