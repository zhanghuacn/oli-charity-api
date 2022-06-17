@component('mail::message')
### <center><img src="https://charity-s3.oliview.com.au/prod/logo.svg" alt="{{ config('app.name') }}"></center>
# Dear User,
Your verification code is valid for 15 minutes.
<center><font size="16">{{ $code }}</font></center>

@component('mail::panel')
    In order to ensure the security of your account, please do not provide this verification code to anyone.
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
