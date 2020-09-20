const { initPlugin } = require('cypress-plugin-snapshots/plugin');
const htmlvalidate = require('cypress-html-validate/dist/plugin');

module.exports = (on, config) => {
    htmlvalidate.install(on);
    initPlugin(on, config);
    return config;
};
