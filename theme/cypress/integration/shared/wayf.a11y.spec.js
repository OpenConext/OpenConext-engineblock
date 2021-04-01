import {terminalLog} from '../../functions/terminalLog';

context('Wayf verify a11y', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
  });

  it('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y(null, null, terminalLog);
  });

  it('contains no html errors', () => {
    cy.htmlvalidate();
  });
});
