window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    wsHost: process.env.MIX_PUSHER_HOST,
    wsPort: process.env.MIX_PUSHER_PORT,
    wssPort: process.env.MIX_PUSHER_PORT,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws','wss'],
    authEndpoint: '/api/broadcasting/auth',
    auth: {
        headers: {
            Authorization: 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMDMwMzk3YjNiOGZlODBjNDZiOTA2N2NhZDJhYmM1ZDgzNjc1MDA0ZGE3ZTFlNzFhZjViYTBmMTc2MjVjZjE5N2Y0YzA1MGU0NDMyNjE0ODciLCJpYXQiOjE2NTA2MzI5MjQuNTI3NjE5LCJuYmYiOjE2NTA2MzI5MjQuNTI3NjIyLCJleHAiOjE2ODIxNjg5MjQuMTY4ODg4LCJzdWIiOiIzNzUiLCJzY29wZXMiOlsicGxhY2UtYXBwIl19.qCL7ymOd7GdhT_hlUrLA_GJ062CrKkiU7FXW_3b8JQxzl3HKAZmIPwkwc2Ns27Kd7LzHJneFHwmeNiyzfxMAC6MEsW0_XTSX010Fv1Ns60bYhRN9zRHoUeT0LsAuZjkqVkctomgnzaW8rCXGXXMuLKabtFemr7c-iEfDse5QOzeFla8zNJjJRKIyH6N8mN3iamQKYBz7Gps8H3n1dZDe0Te2nwbgw67YAYmN8XouJ6JrcZ4_RW809YUx-tkWYdOPu0iBoNsDJSRipeslykCUOrQ9FdNwD9w4hspJXyb_Fbr_tWxmfuQaAjW_rEtePleq8hGBObwZB2jNyOuWvTT5M-ZVdcRI7h-vJeDXq-CgsNot4sDB1ZIME7w1K3ZVhls_-UcC1fY2A9sRrL3E_vJdFS35sXNmJVbt4tUX3ns-xKmWJarmoV3B0Qx4X5HHiu880ZaFPfQB3ovs2ccow0jT-5MXWPL0gr81DozWSz6JKgFYGpohmFskDclzMtr62yjbscQRxYcpwkH-x1xGUqtVmBvdMowvK_0tsrg46Kj5pe0IAXwaA92-g8XVfWobKOFP0auH7XA5Kb2GlUIZ3SEyOdou0ZapkFU-6TNSQCSkMueXlgdToso2sFX9UWpaYmnzpJbXH7MyofiHjLHX4fOzmB3FJ4kjOzLhVNyu7najGZ0'
        }
    },
});

window.Echo.private('auction.3')
    .listen('AuctionBidEvent', (e) => {
        console.log(e)
    });
