@php
    // Mise en forme inline (lue par Excel / LibreOffice à l'import HTML).
    $cell = 'border:1px solid #000000;padding:4px 6px;font-size:11px;vertical-align:top;font-family:Calibri,Arial,sans-serif;';
    $th = 'border:1px solid #000000;padding:4px 6px;font-size:11px;font-weight:bold;text-align:center;background-color:#dce6f1;font-family:Calibri,Arial,sans-serif;';
    $banner = 'border:1px solid #000000;padding:8px;font-size:13px;font-weight:bold;text-align:left;background-color:#ffffff;font-family:Calibri,Arial,sans-serif;';
    $rsRow = 'border:1px solid #000000;padding:6px;font-size:12px;font-weight:bold;text-align:left;background-color:#e2efda;font-family:Calibri,Arial,sans-serif;';
    $extrantRow = 'border:1px solid #000000;padding:6px;font-size:12px;font-weight:bold;text-align:center;background-color:#fce4d6;font-family:Calibri,Arial,sans-serif;';
    $center = 'text-align:center;';
    $rang = 0;
@endphp
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta charset="utf-8">
    <title>Cadre logique des activités</title>
</head>
<body>
    <table border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
        <tr>
            <td colspan="9" style="border:none;padding:8px;font-size:16px;font-weight:bold;text-align:center;font-family:Calibri,Arial,sans-serif;">CADRE LOGIQUE DES ACTIVITES {{ now()->year }}</td>
        </tr>

        @forelse ($objectifs as $bloc)
            @php $objectif = $bloc['objectif']; @endphp
            {{-- Bannière objectif --}}
            <tr>
                <td colspan="9" style="{{ $banner }}">Objectif stratégique du PDDSS : {{ $objectif?->description ?: ($objectif?->libelle ?? 'Non rattaché') }}</td>
            </tr>

            {{-- En-tête du tableau --}}
            <tr>
                <td rowspan="2" style="{{ $th }}width:45px;">N° ACT</td>
                <td rowspan="2" style="{{ $th }}width:300px;">ACTIVITES</td>
                <td rowspan="2" style="{{ $th }}width:160px;">INDICATEUR OBJECTIVEMENT VERIFIABLE</td>
                <td rowspan="2" style="{{ $th }}width:130px;">VALEUR DE L'INDICATEUR</td>
                <td colspan="3" style="{{ $th }}">1er SEMESTRE</td>
                <td rowspan="2" style="{{ $th }}width:120px;">RESPONS</td>
                <td rowspan="2" style="{{ $th }}width:160px;">OBSERVATIONS</td>
            </tr>
            <tr>
                <td style="{{ $th }}width:35px;">R</td>
                <td style="{{ $th }}width:35px;">ER</td>
                <td style="{{ $th }}width:35px;">NR</td>
            </tr>

            @forelse ($bloc['resultats'] as $blocResultat)
                @php $resultat = $blocResultat['resultat']; @endphp
                {{-- Ligne Résultat stratégique --}}
                <tr>
                    <td colspan="9" style="{{ $rsRow }}">{{ $resultat?->code }} {{ $resultat?->libelle }}</td>
                </tr>

                @foreach ($blocResultat['extrants'] as $blocExtrant)
                    @php $extrant = $blocExtrant['extrant']; @endphp
                    {{-- Ligne Extrant --}}
                    <tr>
                        <td colspan="9" style="{{ $extrantRow }}">{{ $extrant?->code }} {{ $extrant?->libelle }}</td>
                    </tr>

                    @foreach ($blocExtrant['activites'] as $activite)
                        @php $rang++; @endphp
                        <tr>
                            <td style="{{ $cell }}{{ $center }}">{{ $rang }}</td>
                            <td style="{{ $cell }}">{{ $activite->nom_activite }}</td>
                            <td style="{{ $cell }}">{{ $activite->indicateur_objectivement_verifiable }}</td>
                            <td style="{{ $cell }}">{{ $activite->moyen_verification }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->statut_execution === 'realise' ? 'X' : '' }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->statut_execution === 'en_cours' ? 'X' : '' }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ in_array($activite->statut_execution, ['non_realise', null], true) ? 'X' : '' }}</td>
                            <td style="{{ $cell }}{{ $center }}">{{ $activite->departements->isNotEmpty() ? $activite->departements->pluck('nom')->join('/') : ($activite->departement?->nom ?? '') }}</td>
                            <td style="{{ $cell }}">{{ $activite->execution_commentaire }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr>
                    <td colspan="9" style="{{ $cell }}{{ $center }}color:#888;">Aucune activité</td>
                </tr>
            @endforelse

            {{-- Espace entre objectifs --}}
            <tr><td colspan="9" style="border:none;height:14px;">&nbsp;</td></tr>
        @empty
            <tr><td style="{{ $cell }}">Aucune activité à exporter.</td></tr>
        @endforelse
    </table>
</body>
</html>
