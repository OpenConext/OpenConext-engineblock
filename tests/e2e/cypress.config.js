const { defineConfig } = require("cypress");
const htmlvalidate = require("cypress-html-validate/plugin");

module.exports = defineConfig({
    e2e: {
        excludeSpecPattern: [
            "**/__snapshots__/*",
            "**/__image_snapshots__/*",
            "**/unit-tests/**"
        ],
        specPattern: "./cypress/integration/**/*.spec.js",
        screenshotOnRunFailure: false,
        video: false,

        setupNodeEvents(on, config) {
            // 🧪 Install htmlvalidate plugin
            htmlvalidate.install(on, {
                rules: {
                    "prefer-button": "off",
                    "prefer-native-element": ["error", {
                        exclude: ["button"]
                    }],
                    "require-sri": ["error", {
                        target: "crossorigin"
                    }]
                }
            });

            // Custom logging tasks
            on("task", {
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
        }
    }
});
