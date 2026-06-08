import store from './store';

import _ from 'lodash';
window._ = _;

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Array.prototype.mapAndSumProperties = function (key) {
    let ret = {};
    this.map((o) => o[key]).forEach((oo) => {
        for (let prop in oo) {
            if (!ret.hasOwnProperty(prop)) {
                ret[prop] = 0;
            }
            ret[prop] += +oo[prop];
        }
    });
    return ret;
};

Array.prototype.groupBy = function (key) {
    let ret = {};
    this.forEach((o) => {
        if (!ret.hasOwnProperty(o[key])) {
            ret[o[key]] = [];
        }
        ret[o[key]].push(o);
    });
    return ret;
};

Array.prototype.sum = function (key = null) {
    if (key) {
        return this.map((o) => o[key]).sum();
    }
    return this.reduce((a, b) => +a + b, 0);
};

String.prototype.$ucfirst = function () {
    return this.substring(0, 1).toUpperCase() + this.substring(1).toLowerCase();
};

String.prototype.$ucwords = function () {
    return this.split(' ')
        .map((o) => o.$ucfirst())
        .join(' ');
};

Number.prototype.$round4 = function () {
    return Math.round(this * 10000) / 10000;
};

const copyToClipboard = function (text) {
    if (typeof navigator.clipboard?.writeText === 'function') {
        navigator.clipboard.writeText(text);
        return;
    }

    let dummy = document.createElement('textarea');
    document.body.appendChild(dummy);
    dummy.value = text;
    dummy.select();
    document.execCommand('copy');
    document.body.removeChild(dummy);
};

window.copyToClipboard = copyToClipboard;
