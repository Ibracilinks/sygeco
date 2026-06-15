@props(['href', 'label' => 'Modifier', 'iconOnly' => false])

<x-action variant="edit" icon="pencil" :href="$href" :label="$label" :icon-only="$iconOnly" {{ $attributes }} />
