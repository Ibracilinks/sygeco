{{-- Récapitulatif des erreurs de validation, champ par champ. --}}
@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">
            {{ $errors->count() }} champ(s) à corriger avant d'enregistrer :
        </p>
        <ul class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-300">
            @foreach ($errors->messages() as $champ => $messages)
                @php
                    // participants.0.nom_complet → « Participant n°1 » pour situer la ligne fautive.
                    $segments = explode('.', $champ);
                    $libelle = match ($segments[0]) {
                        'participants' => count($segments) > 1 ? 'Participant n°'.((int) $segments[1] + 1) : 'Participants',
                        'signataires' => count($segments) > 1 ? 'Signataire n°'.((int) $segments[1] + 1) : 'Signataires',
                        'etapes' => count($segments) > 1 ? 'Étape n°'.((int) $segments[1] + 1) : 'Étapes',
                        default => null,
                    };
                @endphp
                @foreach ($messages as $message)
                    <li class="flex gap-2">
                        <span aria-hidden="true">•</span>
                        <span>
                            @if ($libelle)
                                <span class="font-semibold">{{ $libelle }} —</span>
                            @endif
                            {{ $message }}
                        </span>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif
