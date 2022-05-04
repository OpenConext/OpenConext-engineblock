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

  // debug a11y in ci
  on('task', {
    log(message) {
      console.log(message);

      return null;
    },
    table(message) {
      console.table(message);

      return null;
    }
  });

  return config;
};
