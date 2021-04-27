import {terminalLog} from '../../functions/terminalLog';

context('Cookie removal page verify a11y', () => {
  /**
   * Setting the required parameter.yml variables and then clearing cache to execute this test correctly.
   * Using library js-yaml, repo can be found at: https://github.com/nodeca/js-yaml
   * Tutorial can be found at https://stackabuse.com/reading-and-writing-yaml-to-a-file-in-node-js-javascript/
   */
  before(() => {
    try {
      const fs = require('fs');
      const yaml = require('js-yaml');
      const config = `${__dirname}/../../app/config/parameters.yml`;

      const fileContents = fs.readFileSync(config, 'utf8');
      const parameters = yaml.safeLoadAll(fileContents);
      parameters[0].parameters['wayf.remember_choice'] = true;
      fs.writeFileSync(config, yaml.safeDump(parameters[0]));
      executeShellCommand(`php bin/console cache:clear`);
    } catch(e) {
      console.log(e);
    }
  });

  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/authentication/idp/remove-cookies');
  });

  it('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y(null, null, terminalLog);
  });

  it('contains no html errors', () => {
    cy.htmlvalidate();
  });
});

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
