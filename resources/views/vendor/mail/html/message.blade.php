<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
Caisse Nationale d'Assurance Maladie (CANAM)<br>
© {{ date('Y') }} {{ config('app.name') }} — Logiciel d'évaluation des activités de la CANAM. Tous droits réservés.<br>
<span style="color: #b0adc5; font-size: 11px;">Ce message est généré automatiquement, merci de ne pas y répondre.</span>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
