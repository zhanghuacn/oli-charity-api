@component('mail::message')
### <center><img src="https://s3.imagine2080.com.au/public/email/imagine2080Logo.svg" alt="{{ config('app.name') }}"></center>
# Dear user
Your verification code is valid for 15 minutes.
<center><font size="16">{{ $code }}</font></center>

@component('mail::panel')
    In order to ensure the security of your account, please do not provide this verification code to anyone.
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
