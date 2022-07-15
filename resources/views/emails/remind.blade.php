@component('mail::message')
### <center><img src="https://charity-s3.oliview.com.au/prod/logo.png" alt="{{ config('app.name') }}"></center>
# Dear {{ $name }},

The lucky draw of <font size="5">{{ $event }}</font> will be held as scheduled after <font size="5">{{ $days }}</font> days.

@component('mail::button', ['url' => $url, 'color' => 'success'])
View Event
@endcomponent

@component('mail::panel')
All registers will have a chance to win the prize, Please confirm your registration and Raffle ticket number.
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
