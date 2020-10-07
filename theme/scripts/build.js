#!/usr/bin/env node

/** Simple script to build based on CLI or chosen theme in parameters.yml
 * Using library js-yaml, repo can be found at: https://github.com/nodeca/js-yaml
 * Tutorial can be found at https://stackabuse.com/reading-and-writing-yaml-to-a-file-in-node-js-javascript/
 *
 * Everything else used is part of node
 *
 * Use: node build.js or  EB_THEME=skeune node build.js
 * Replace "skeune" in the above by whatever theme you want to build.
 **/
const fs = require('fs');
const yaml = require('js-yaml');
const config = `${__dirname}/../../app/config/parameters.yml`;

try {
    console.log('Reading contents of parameters.yml.\n');
    const fileContents = fs.readFileSync(config, 'utf8');
    const parameters = yaml.safeLoadAll(fileContents);

    let theme = process.env.EB_THEME || parameters[0].parameters['theme.name'] || 'openconext';

    if (process.env.EB_THEME) {
        theme = process.env.EB_THEME;
    }

    console.log(`Using theme ${theme} to run the build.\nOutput will be printed once the build is finished.\n`);
    executeShellCommand(`cd ${__dirname}/.. && EB_THEME=${theme} npm run buildtheme`);
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
