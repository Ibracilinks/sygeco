@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('logo_canam.png') }}" class="logo" alt="CANAM" style="max-height: 64px; height: auto;">
<br>
<span style="display: inline-block; margin-top: 8px; font-size: 18px; font-weight: bold; color: #2d6a4f;">{{ config('app.name', 'SYGECO') }}</span>
</a>
</td>
</tr>
