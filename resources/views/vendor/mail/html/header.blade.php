@props(['url', 'logo' => null])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $logo ?? asset('logo_canam.png') }}" class="logo" alt="CANAM" width="64" height="64" style="width: 64px; height: 64px;">
<br>
<span style="display: inline-block; margin-top: 8px; font-size: 18px; font-weight: bold; color: #2d6a4f;">{{ config('app.name', 'LEAC') }}</span>
</a>
</td>
</tr>
