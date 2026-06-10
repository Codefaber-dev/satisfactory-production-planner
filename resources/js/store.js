const getItem = (key, def = null) => {
    const v = window.localStorage.getItem(`satisfactory.${key}`);

    return v ? JSON.parse(v) : def;
};

const setItem = (key, value) => {
    window.localStorage.setItem(`satisfactory.${key}`, JSON.stringify(value));
};

export default { getItem, setItem };
