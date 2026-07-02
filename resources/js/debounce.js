// Minimal this-preserving debounce, replacing the lodash import (V75).
// Default wait of 0 matches lodash's default (defer to next tick).
export function debounce(fn, wait = 0) {
    let timer;

    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), wait);
    };
}
