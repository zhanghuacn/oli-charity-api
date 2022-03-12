@component('mail::message')
### <center><img src="https://vapor-ap-southeast-2-storage-1641439528.s3.ap-southeast-2.amazonaws.com/public/email/imagine2080Logo.svg" alt="{{ config('app.name') }}"></center>
# Dear user
Your verification code is valid for 15 minutes.
<center><font size="16">987654</font></center>

@component('mail::panel')
    In order to ensure the security of your account, please do not provide this verification code to anyone.
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
