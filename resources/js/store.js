const getItem = function(key, def=null) {
    let v = window.localStorage.getItem(`satisfactory.${key}`);

    return v ? JSON.parse(v) : def;
};

const setItem = function(key, value) {
    window.localStorage.setItem(`satisfactory.${key}`, JSON.stringify(value));
};

export default {getItem, setItem};

