@php
    // Mise en forme inline (lue par Excel / LibreOffice à l'import HTML).
    $cell = 'border:1px solid #000000;padding:4px 6px;font-size:11px;vertical-align:top;font-family:Calibri,Arial,sans-serif;';
    $th = 'border:1px solid #000000;padding:4px 6px;font-size:11px;font-weight:bold;text-align:center;background-color:#dce6f1;font-family:Calibri,Arial,sans-serif;';
    $banner = 'border:1px solid #000000;padding:8px;font-size:13px;font-weight:bold;text-align:left;background-color:#ffffff;font-family:Calibri,Arial,sans-serif;';
    $rsRow = 'border:1px solid #000000;padding:6px;font-size:12px;font-weight:bold;text-align:center;background-color:#e2efda;font-family:Calibri,Arial,sans-serif;';
    $extrantRow = 'border:1px solid #000000;padding:6px;font-size:12px;font-weight:bold;text-align:center;background-color:#fce4d6;font-family:Calibri,Arial,sans-serif;';
    $center = 'text-align:center;';
    $right = 'text-align:right;';
    $rang = 0;
@endphp
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta charset="utf-8">
    <title>PTA Exercice {{ $exercice->annee }}</title>
</head>
<body>
    <table border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
        @forelse ($objectifs as $objectif)
            {{-- Bannière objectif --}}
            <tr>
                <td colspan="10" style="{{ $banner }}">Objectif stratégique du PDDSS : {{ $objectif->description ?: $objectif->libelle }}</td>
            </tr>

            {{-- En-tête du tableau --}}
            <tr>
                <td rowspan="2" style="{{ $th }}width:70px;">NOMBRE D'ACTIVITES</td>
                <td rowspan="2" style="{{ $th }}width:280px;">ACTIVITES</td>
                <td rowspan="2" style="{{ $th }}width:150px;">INDICATEUR OBJECTIVEMENT VERIFIABLE</td>
                <td rowspan="2" style="{{ $th }}width:120px;">MOYEN DE VERIFICATION</td>
                <td colspan="4" style="{{ $th }}">CHRONOGRAMME</td>
                <td rowspan="2" style="{{ $th }}width:120px;">RESPONSABLES</td>
                <td rowspan="2" style="{{ $th }}width:110px;">COÛTS</td>
            </tr>
            <tr>
                <td style="{{ $th }}width:45px;">1er TRI</td>
                <td style="{{ $th }}width:45px;">2ème TRI</td>
                <td style="{{ $th }}width:45px;">3ème TRI</td>
                <td style="{{ $th }}width:45px;">4ème TRI</td>
            </tr>

            @forelse ($objectif->resultats as $resultat)
                {{-- Ligne Résultat stratégique --}}
                <tr>
                    <td colspan="10" style="{{ $rsRow }}">{{ $resultat->code }} {{ $resultat->libelle }}</td>
                </tr>

                @forelse ($resultat->extrants as $extrant)
                    {{-- Ligne Extrant --}}
                    <tr>
                        <td colspan="10" style="{{ $extrantRow }}">{{ $extrant->code }} {{ $extrant->libelle }}</td>
                    </tr>

                    @forelse ($extrant->activites as $activite)
                        @php $rang++; @endphp
                        <tr>
                            <td style="{{ $cell }}{{ $center }}">{{ $rang }}</td>
                            <td style="{{ $cell }}">{{ $activite->nom_activite }}</td>
                            <td style="{{ $cell }}">{{ $activite->indicateur_objectivement_verifiable }}</td>
                            <td style="{{ $cell }}">{{ $activite->moyen_verification }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->trimestre_1 === 'oui' ? 'X' : '' }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->trimestre_2 === 'oui' ? 'X' : '' }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->trimestre_3 === 'oui' ? 'X' : '' }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->trimestre_4 === 'oui' ? 'X' : '' }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->departements->isNotEmpty() ? $activite->departements->pluck('nom')->join('/') : ($activite->departement?->nom ?? '') }}</td>
                            <td style="{{ $cell }}{{ $right }}">{{ (float) $activite->cout > 0 ? number_format((float) $activite->cout, 0, '.', ',') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="{{ $cell }}{{ $center }}color:#888;">Aucune activité</td>
                        </tr>
                    @endforelse
                @empty
                    <tr>
                        <td colspan="10" style="{{ $cell }}{{ $center }}color:#888;">Aucun extrant</td>
                    </tr>
                @endforelse
            @empty
                <tr>
                    <td colspan="10" style="{{ $cell }}{{ $center }}color:#888;">Aucun résultat</td>
                </tr>
            @endforelse

            {{-- Espace entre objectifs --}}
            <tr><td colspan="10" style="border:none;height:14px;">&nbsp;</td></tr>
        @empty
            <tr><td style="{{ $cell }}">Aucun objectif pour cet exercice {{ $exercice->annee }}.</td></tr>
        @endforelse
    </table>
</body>
</html>
