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
            Authorization: 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWYyMDkyNTcwZmU3MzE5MTBlM2M0ZDljZTFmMDc2Nzg5NzQ0ZmViMzJhMzNiYmZiYTg2MDkxMDIzMzYyM2Q3Mjk5N2E2ODdmZGMxODJhMzMiLCJpYXQiOjE2NTAyNjU4MjguMzM3MzY0LCJuYmYiOjE2NTAyNjU4MjguMzM3MzY3LCJleHAiOjE2ODE4MDE4MjguMTI0NzY4LCJzdWIiOiI0MDEiLCJzY29wZXMiOlsicGxhY2UtYXBwIl19.U912IpOjj7cEoVNb5Gu8Dc8oKZZ9HglIrINwhLgGz49s-JFRC51BN89JvomjImW0m-jPv52o4zDRQPmUkk_LrrAnO1FUo0vMr_qLU_xZxNCPqBvSlM6Qn7Z_s49-hVrvwBO-7FscxEDb_zeFMWau6nStAdD8_dk8nZuEOr_iSpS1PC0rPXjIW_POvvbMdro9XQuki5KpJE6HNBfzPv4V8LvA1NN4ZJL8wjGvLqPD1CyvyLGfhGE2M1Tu-WNBkrAsHkkLDyWdHFHvSoMn_9_Sp9DxAeZf_Qz6DawTmft_qyFUot8nArw72BBtelHAmh9yVmxRGSfAMy0hw34FZJYVk3Xuq07RBFRGapptI6hYka2cehyR8-lgAVhTYpZ3EDP0RFL9uNEdu4X-gFFhPULQaT-h-OEZkpfLjr_1eal2gx1hODWxu5j1kYBsFWCSp7BFEeRvkOU79J3uiluTXtv2x4WnRRCLQPUqX7a7lSBjt6ccjuhfXhBRr222Oc7JInHwBkoOdoB_xuCIycXNgaa_jFRTGNjfTXBpUyjjXcRLl-IZRlFwWl9i1v01GwluZ2z3l1-6IacgIyoG-kifcRXqkUmQX0BAtiJFdUyrEkoKXjp0ZOjPTixOyL0sL-kLTQDOszeiNkfO2bk54Tvw2RKE2Tz7uk2RZ99zKswFsmRMEf8'
        },
    },
});

window.Echo.private('auction.1')
    .listen('AuctionBidEvent', (e) => {
        console.log(e)
    });
