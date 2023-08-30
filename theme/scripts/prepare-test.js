#!/usr/bin/env node

/**
 * Prepare the application environment to run a Cypress E2E test
 * The prepare-test.js script is tasked with:
 *
 * 1. Update the parameters.yml (setting the Twig template theme)
 * 2. Clear the CI cache
 * 3. Build the theme using the `yarn build` scripts
 *
 * Note this script is most suitable to use on CI environments.
 * Use with caution on your development environment. As it will
 * overwrite parameters.yml
 */
const fs = require('fs');
const yaml = require('js-yaml');
const config = `${__dirname}/../../app/config/parameters.yml`;

if (process.env.EB_THEME === undefined) {
  console.error('You must specifiy the EB_THEME env var in order to run the tests.\n');
  process.exit(1);
}

try {
    console.log('Running Cypress tests.\n');
    const fileContents = fs.readFileSync(config, 'utf8');
    const parameters = yaml.loadAll(fileContents);

    let theme = process.env.EB_THEME;
    parameters[0].parameters['theme.name'] = theme;
    fs.writeFileSync(config, yaml.dump(parameters[0]));
    executeShellCommand(`${__dirname}/../../app/console ca:cl --env=ci && cd ${__dirname}/../ && EB_THEME=${theme} yarn buildtheme`);
} catch (e) {
    console.log(e);
}

function executeShellCommand(command) {
    const { exec } = require('child_process');
    exec(command, (error, stdOut, stdError) => {
        if (error) {
            console.log(`exec error: ${error}`);
            return;
        }

        if (!stdOut && stdError) {
            console.log(`error info: ${stdError}`);
        }

        console.log(stdOut);
        return process.exit(0);
    });
}
