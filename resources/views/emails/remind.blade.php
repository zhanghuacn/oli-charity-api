@component('mail::message')
### <center><img src="https://vapor-ap-southeast-2-storage-1641439528.s3.ap-southeast-2.amazonaws.com/public/email/imagine2080Logo.svg" alt="{{ config('app.name') }}"></center>
# Dear user

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
