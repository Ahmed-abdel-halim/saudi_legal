@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    @isset($message)
        <img src="{{ $message->embed(public_path('images/icon.png')) }}" alt="Radiif Logo" style="max-height: 50px; width: auto; max-width: 100%; border: none; display: block;">
    @else
        <img src="{{ config('app.url') }}/images/icon.png" alt="Radiif Logo" style="max-height: 50px; width: auto; max-width: 100%; border: none; display: block;">
    @endisset
</a>
</td>
</tr>
