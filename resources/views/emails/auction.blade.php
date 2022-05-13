@component('mail::message')
### <center><img src="https://s3.imagine2080.com.au/public/email/imagine2080Logo.svg" alt="{{ config('app.name') }}"></center>
# Dear user

<p>Congratulations! You've won the auction with an AU <font size="5">{{ $auction->current_bid_price }}</font>  Next, please make a payment to receive your item.</p>
<p>You placed {{ $bid_num }} bids and beat {{ $user_num }} bidders.</p>
<center><img src="{{ $image }}" alt="{{ $auction->name }}" width="300" height="300"/></center>
<p>If you have any questions, please contact the administrator of the WeChat group.</p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
