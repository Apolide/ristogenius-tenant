@props(['url'])
<tr>
<td class="header">
<a href="https://ristopilot.com" style="display: inline-block;">
{{-- @if (trim($slot) === 'Laravel') --}}
<img
  src="https://ristopilot.com/assets/images/brand-logos/desktop-logo.png"
  class="logo"
  alt="Risto Pilot Logo"
  width="180"
  style="height:40px; max-height:40px; width:auto; display:inline-block; border:0; outline:none; text-decoration:none;"
>
{{-- @else
{{ $slot }}
@endif --}}
</a>
</td>
</tr>
