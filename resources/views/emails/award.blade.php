@component('mail::message')
### <center><img src="https://s3.imagine2080.com.au/public/email/imagine2080Logo.svg" alt="{{ config('app.name') }}"></center>
# Dear user

<p>Congratulations, you've won the <font size="5">{{ $prize }}</font>  in our <font size="5">{{ $event }}</font>!</p>
<p>You can claim your prize on the day of the banquet.</p>
<center><img src="{{ $image }}" alt="{{ $prize }}" width="300" height="300"/></center>
<p>If you have any questions, please contact the administrator of the WeChat group.</p>

@component('mail::button', ['url' => 'https://mp.weixin.qq.com/s?__biz=MzA5NzIzNjMzNw==&mid=2651418415&idx=2&sn=f068b6676b4b0518403e5138aa489075&chksm=8b5e2b31bc29a2279cd10e0dc10b01ca989642bd5ec402be199b6893492ec890132f1b3c4f3b&token=860683828&lang=zh_CN#rd', 'color' => 'success'])
    More Info
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
