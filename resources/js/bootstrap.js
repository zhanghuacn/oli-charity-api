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
    wsHost: '159.75.71.224',
    wsPort: process.env.MIX_PUSHER_PORT,
    wssPort: process.env.MIX_PUSHER_PORT,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/api/broadcasting/auth',
    auth: {
        headers: {
            Authorization: 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZmNlODQxM2I5M2FkYjFhMjdkMDlhODdiYTk4NGYwOWRmM2M4ZDgzYjUxYTdhNDU4MTlkZmExMTBmY2Q4NjY0M2U1Yzg0MzE2MmRkNmMxM2IiLCJpYXQiOjE2NTA0NjU4MzIuMTUyNjEsIm5iZiI6MTY1MDQ2NTgzMi4xNTI2MzcsImV4cCI6MTY4MjAwMTgzMi4wMzA3MDcsInN1YiI6IjQwMSIsInNjb3BlcyI6WyJwbGFjZS1hcHAiXX0.n3MkGGjPKWK2plX8IEsMXX1FpvKXW4SwOvfvPdGk37Qv2GUZPpU2UbOK1McrDTy5_85sPtSb9BpQETtSjXz9iwFuptmpLYMXZfLe4bWFgKorJEOb_4eiNaQuCLm_lm-AbrnMHdooCIV_OGfs41BL4uTCBzZjelOL-zTP23U93pEkANU0NG6gsTvPVSxi9aeveQazbBXTXxyA2WMvgyze1NCG-qgCH6VqAI_tGbW-5m6X0AAwAKMzjYP3UUlv6TnKsYvjXpB6-OI3gAEGTA6x1BfeWhvrXQLDZx99fHRgF1tLxOv7key1Vn6Vt3vL-X5EKJMRBZdNk-To12dFnXw2Qz8VSIt-uSc0v_ahG7LfeHfy0GPUcfkjtuKQ3jbvX_1mcrMzbceKcoDVZhMgLq5S9AbuSRysBYhLwrC3S9rBKUwMR_90e7CsAPvGeXv5WjqzySjVHbdiKApmh2mdngBP-jhSaxbKiGS41whWBrNDMdd5xV5Z532DOUNLdmDxbaHrabEXgjBhDIgVvu2DIGlfXkQ7PHbE0oTSH9pIYcyPvuIEq9G5JAYuN6ngizb47Xxmq5mo_Vl4CCYYkg7FZnuWgs3JY0PyNPP79H7YHH0gbJVsStzkXeSGrMoA_IsWXmJw9cIV-YX1gevVGvEofBnE0e68ora_-tC0lFcmhI82cR8'
        },
    },
});

window.Echo.private('auction.3')
    .listen('AuctionBidEvent', (e) => {
        console.log(e)
    });
