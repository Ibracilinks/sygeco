@props(['href', 'label' => 'Nouveau', 'iconOnly' => false])

<x-action variant="create" icon="plus" :href="$href" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
