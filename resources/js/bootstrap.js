import store from './store';

window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Array.prototype.mapAndSumProperties = function(key) {
    let ret = {};
    this.map(o => o[key]).forEach(oo => {
        for(let prop in oo) {
            if (!ret.hasOwnProperty(prop)) {
                ret[prop] = 0;
            }
            ret[prop] += +oo[prop];
        }
    })
    return ret;
}

Array.prototype.groupBy = function(key) {
    let ret = {};
    this.forEach(o => {
        if (!ret.hasOwnProperty(o[key])) {
            ret[o[key]] = [];
        }
        ret[o[key]].push(o);
    })
    return ret;
}

Array.prototype.sum = function(key=null) {
    if (key) {
        return this.map(o => o[key]).sum();
    }
    return this.reduce((a,b) => +a+b,0);
}

String.prototype.$ucfirst = function() {
    return this.substring(0,1).toUpperCase() + this.substring(1).toLowerCase();
}

String.prototype.$ucwords = function() {
    return this.split(' ').map(o => o.$ucfirst()).join(' ');
}


/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
