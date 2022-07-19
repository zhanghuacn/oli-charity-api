@component('mail::message')
### <center><img src="{{ $logo }}" width="500" height="300" alt="{{ config('app.name') }}"></center>
# Dear {{ $name }},

<p>Congratulations, you've won the <font size="5">{{ $prize }}</font>  in our <font size="5">{{ $event }}</font>!</p>
<p>You can claim your prize on the day of the banquet.</p>
<center><img src="{{ $image }}" alt="{{ $prize }}" width="300" height="300"/></center>
<p>If you have any questions, please contact the administrator of the WeChat group.</p>

@component('mail::button', ['url' => $url, 'color' => 'success'])
    More Info
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
