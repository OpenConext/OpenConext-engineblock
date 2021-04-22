import {terminalLog} from '../../functions/terminalLog';

context('Cookie removal page verify a11y', () => {
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
