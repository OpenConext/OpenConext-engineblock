import js from "@eslint/js";
import globals from "globals";

export default [
  js.configs.recommended,
  {
    files: ["**/javascripts/**/*.js"],
    languageOptions: {
      ecmaVersion: 2017,
      sourceType: "module",
      globals: globals.browser,
    },
    rules: {
      // Since we stick to ecma2017, we cannot adhere to some modern constructions, for example, empty catch blocks
      "no-unused-vars": ["error", { "caughtErrors": "none" }],
    },
  },
];
