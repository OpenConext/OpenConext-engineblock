#!/usr/bin/env node

/** Simple script to watch based on CLI or chosen theme in parameters.yml
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
  const parameters = yaml.loadAll(fileContents);
  const theme = process.env.EB_THEME || parameters[0].parameters['theme.name'] || 'skeune';

  console.log(`Using theme ${theme} to run the watch.\n`);
  executeShellCommand(`cd ${__dirname}/.. && EB_THEME=${theme} yarn watch:css`);
  executeShellCommand(`cd ${__dirname}/.. && EB_THEME=${theme} yarn watch:js`);
} catch (e) {
  console.log(e);
}

function executeShellCommand(command) {
  var exec = require('child_process').exec;
  var process = exec(command);

  process.stdout.on('data', function (data) {
    console.log(data);
  });
  process.stderr.on('data', function (data) {
    console.log(data);
  });
}
