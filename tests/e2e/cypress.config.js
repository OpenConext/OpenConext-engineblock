const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    excludeSpecPattern: [
      "**/__snapshots__/*",
      "**/__image_snapshots__/*",
      "**/unit-tests/**"
    ],
    screenshotOnRunFailure: false,
    setupNodeEvents(on, config) {
      const htmlvalidate = require('cypress-html-validate/dist/plugin');

      module.exports = (on, config) => {
        // require('cypress-terminal-report/src/installLogsPrinter')(on);

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

    },
    specPattern: "./cypress/integration/**/*.spec.js",
    "video": false
  },
});
