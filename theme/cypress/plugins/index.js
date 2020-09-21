const { initPlugin } = require('cypress-plugin-snapshots/plugin');
const htmlvalidate = require('cypress-html-validate/dist/plugin');

module.exports = (on, config) => {
    htmlvalidate.install(on, {
        "rules": {
            "prefer-button": "off",
            "prefer-native-element": [ "error", {
                "exclude": [ "button" ],
            }],
            "require-sri": [ "error", {
                "target": "crossorigin",
            }],
        },
    });
    initPlugin(on, config);
    return config;
};
