@php
    // Cadre logique d'arbitrage : mise en forme inline (lue par Excel / LibreOffice à l'import HTML).
    $cell = 'border:1px solid #000000;padding:4px 6px;font-size:11px;vertical-align:top;font-family:Calibri,Arial,sans-serif;';
    $th = 'border:1px solid #000000;padding:4px 6px;font-size:11px;font-weight:bold;text-align:center;background-color:#c6ccc2;font-family:Calibri,Arial,sans-serif;';
    $thCote = 'border:1px solid #000000;padding:4px 6px;font-size:11px;font-weight:bold;text-align:center;background-color:#a9b8c9;font-family:Calibri,Arial,sans-serif;';
    $banner = 'border:1px solid #000000;padding:8px;font-size:13px;font-weight:bold;text-align:left;background-color:#ffffff;font-family:Calibri,Arial,sans-serif;';
    $rsRow = 'border:1px solid #000000;padding:6px;font-size:12px;font-weight:bold;text-align:center;background-color:#c5d9c1;font-family:Calibri,Arial,sans-serif;';
    $extrantRow = 'border:1px solid #000000;padding:6px;font-size:12px;font-weight:bold;text-align:center;background-color:#e8dedb;font-family:Calibri,Arial,sans-serif;';
    $center = 'text-align:center;';
    $montant = 'text-align:right;font-weight:bold;mso-number-format:"\#\,\#\#0";';
    // 9 colonnes : n° | activités | IOV | moyen de vérification | T1 | T2 | T3 | T4 | responsables | coûts
    $nbColonnes = 10;
@endphp
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta charset="utf-8">
    <title>Cadre logique — arbitrage</title>
</head>
<body>
    <table border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
        <tr>
            <td colspan="{{ $nbColonnes }}" style="border:none;padding:8px;font-size:16px;font-weight:bold;text-align:center;font-family:Calibri,Arial,sans-serif;">
                CADRE LOGIQUE — ARBITRAGE / VALIDATION {{ now()->year }}
            </td>
        </tr>

        @forelse ($objectifs as $bloc)
            @php
                $objectif = $bloc['objectif'];
                $rang = 0;
                $totalObjectif = 0;
            @endphp

            {{-- Bannière objectif stratégique --}}
            <tr>
                <td colspan="{{ $nbColonnes }}" style="{{ $banner }}">
                    Objectif stratégique du PDDSS : {{ $objectif?->description ?: ($objectif?->libelle ?? 'Non rattaché') }}
                </td>
            </tr>

            {{-- En-tête du tableau --}}
            <tr>
                <td rowspan="2" style="{{ $thCote }}width:60px;">NOMBRE D'ACTIVITES</td>
                <td rowspan="2" style="{{ $th }}width:300px;">ACTIVITES</td>
                <td rowspan="2" style="{{ $th }}width:150px;">INDICATEUR OBJECTIVEMENT VERIFIABLE</td>
                <td rowspan="2" style="{{ $th }}width:150px;">MOYEN DE VERIFICATION</td>
                <td colspan="4" style="{{ $th }}">CHRONOGRAMME</td>
                <td rowspan="2" style="{{ $th }}width:130px;">RESPONSABLES</td>
                <td rowspan="2" style="{{ $th }}width:110px;">COÛTS</td>
            </tr>
            <tr>
                <td style="{{ $th }}width:55px;">1er TRI</td>
                <td style="{{ $th }}width:55px;">2ème TRI</td>
                <td style="{{ $th }}width:55px;">3ème TRI</td>
                <td style="{{ $th }}width:55px;">4ème TRI</td>
            </tr>

            @forelse ($bloc['resultats'] as $blocResultat)
                @php $resultat = $blocResultat['resultat']; @endphp
                {{-- Ligne Résultat stratégique --}}
                <tr>
                    <td colspan="{{ $nbColonnes }}" style="{{ $rsRow }}">{{ $resultat?->code }} {{ $resultat?->libelle }}</td>
                </tr>

                @foreach ($blocResultat['extrants'] as $blocExtrant)
                    @php $extrant = $blocExtrant['extrant']; @endphp
                    {{-- Ligne Extrant --}}
                    <tr>
                        <td colspan="{{ $nbColonnes }}" style="{{ $extrantRow }}">{{ $extrant?->code }} {{ $extrant?->libelle }}</td>
                    </tr>

                    @foreach ($blocExtrant['activites'] as $activite)
                        @php
                            $rang++;
                            $totalObjectif += (float) $activite->cout;
                            $responsables = $activite->departements->isNotEmpty()
                                ? $activite->departements->pluck('nom')->join('/ ')
                                : ($activite->departement?->nom ?? '');
                        @endphp
                        <tr>
                            <td style="{{ $cell }}{{ $center }}font-weight:bold;">{{ $rang }}</td>
                            <td style="{{ $cell }}">{{ $activite->nom_activite }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->indicateur_objectivement_verifiable }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->moyen_verification }}</td>
                            @foreach ([1, 2, 3, 4] as $trimestre)
                                <td style="{{ $cell }}{{ $center }}font-weight:bold;">{{ $activite->{'trimestre_'.$trimestre} === 'oui' ? 'X' : '' }}</td>
                            @endforeach
                            <td style="{{ $cell }}{{ $center }}">{{ $responsables }}</td>
                            <td style="{{ $cell }}{{ $montant }}">{{ (float) $activite->cout > 0 ? number_format((float) $activite->cout, 0, ',', ' ') : '-' }}</td>
                        </tr>
                    @endforeach
                @endforeach

                {{-- Total du résultat stratégique --}}
                @php
                    $totalResultat = collect($blocResultat['extrants'])
                        ->flatMap(fn ($blocExtrant) => $blocExtrant['activites'])
                        ->sum(fn ($activite) => (float) $activite->cout);
                @endphp
                <tr>
                    <td colspan="9" style="{{ $th }}text-align:right;">TOTAL {{ $resultat?->code }}</td>
                    <td style="{{ $th }}{{ $montant }}">{{ number_format($totalResultat, 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $nbColonnes }}" style="{{ $cell }}{{ $center }}color:#888;">Aucune activité</td>
                </tr>
            @endforelse

            {{-- Total de l'objectif --}}
            <tr>
                <td colspan="9" style="{{ $thCote }}text-align:right;">TOTAL GENERAL ({{ $rang }} activité{{ $rang > 1 ? 's' : '' }})</td>
                <td style="{{ $thCote }}{{ $montant }}">{{ number_format($totalObjectif, 0, ',', ' ') }}</td>
            </tr>

            {{-- Espace entre objectifs --}}
            <tr><td colspan="{{ $nbColonnes }}" style="border:none;height:14px;">&nbsp;</td></tr>
        @empty
            <tr><td style="{{ $cell }}">Aucune activité à arbitrer.</td></tr>
        @endforelse
    </table>
</body>
</html>
