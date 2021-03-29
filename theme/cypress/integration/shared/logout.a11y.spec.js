import {terminalLog} from '../../functions/terminalLog';

context('Logout page verify a11y', () => {
  it('Logout page contains no a11y problems on load', () => {
    cy.visit('https://engine.vm.openconext.org/logout', {failOnStatusCode: false
    });
    cy.injectAxe();
    cy.checkA11y(null, null, terminalLog);
  });

  it('Logout page contains no html errors', () => {
    cy.visit('https://engine.vm.openconext.org/logout', {failOnStatusCode: false
    });
    cy.htmlvalidate();
  });
});
