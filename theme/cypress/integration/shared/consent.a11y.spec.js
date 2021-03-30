import {terminalLog} from '../../functions/terminalLog';

context('Consent verify a11y', () => {
  beforeEach(() => {
    cy.visitAndRemoveDebugToolbar('https://engine.vm.openconext.org/functional-testing/consent');
  });

  it('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y(null, null, terminalLog);
  });

  it('contains no html errors', () => {
    cy.htmlvalidate();
  });
});
