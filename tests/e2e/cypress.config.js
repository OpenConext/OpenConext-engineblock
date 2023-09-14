const { defineConfig } = require("cypress");

module.exports = defineConfig({

  e2e: {
    excludeSpecPattern: [
      "**/__snapshots__/*",
      "**/__image_snapshots__/*",
      "**/unit-tests/**"
    ],
    screenshotOnRunFailure: false,
    setupNodeEvents: function (on, config) {
      const htmlvalidate = require('cypress-html-validate/plugin');

      htmlvalidate.install(on, {
        "rules": {
          "prefer-button": "off",
          "prefer-native-element": ["error", {
            "exclude": ["button"],
          }],
          "require-sri": ["error", {
            "target": "crossorigin",
          }],
        },
      });

      module.exports = (on, config) => {

        // debug a11y in ci
        on('task', {
          log(message) {
            console.log(message);

            return null;
          },
          table(message) {
            console.table(message);

            return null;
          },
          'htmlvalidate:options'(opts) {
            console.log(opts);
            return null;

          }
        });

        return config;
      };

    },
    specPattern: "./cypress/integration/**/*.spec.js",
    "video": false
  },
});
