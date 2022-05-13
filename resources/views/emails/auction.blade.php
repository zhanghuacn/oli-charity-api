@component('mail::message')
### <center><img src="https://vapor-ap-southeast-2-storage-1641439528.s3.ap-southeast-2.amazonaws.com/public/email/imagine2080Logo.svg" alt="{{ config('app.name') }}"></center>
# Dear user

<p>Congratulations! You've won the auction with an AU <font size="5">{{ $auction->current_bid_price }}</font>  Next, please make a payment to receive your item.</p>
<p>You placed {{ $bid_num }} bids and beat {{ $user_count }} bidders.</p>
<center><img src="{{ collect($auction->images)->first }}" alt="{{ $auction->name }}" width="300" height="300"/></center>
<p>If you have any questions, please contact the administrator of the WeChat group.</p>

@component('mail::button', ['url' => 'https://mp.weixin.qq.com/s?__biz=MzA5NzIzNjMzNw==&mid=2651418415&idx=2&sn=f068b6676b4b0518403e5138aa489075&chksm=8b5e2b31bc29a2279cd10e0dc10b01ca989642bd5ec402be199b6893492ec890132f1b3c4f3b&token=860683828&lang=zh_CN#rd', 'color' => 'success'])
    More Info
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
