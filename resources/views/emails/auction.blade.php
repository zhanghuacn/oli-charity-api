@component('mail::message')
### <center><img src="https://charity-s3.oliview.com.au/prod/logo.svg" alt="{{ config('app.name') }}"></center>
# Dear {{ $name }},

<p>Congratulations! You've won the auction {{ $auction->name }} with an AU ${{ $auction->current_bid_price }}. Next, please make a payment to receive your item.</p>
<p>You placed {{ $bid_num }} bids and beat {{ $user_num }} bidders.</p>
<p>If you have any questions, please contact the administrator of the WeChat group.</p>

<center><img src="{{ $image }}" alt="{{ $auction->name }}" width="500" height="300"/></center>

Thanks,<br>
{{ config('app.name') }} Teams
@endcomponent
