import {terminalLog} from '../../functions/terminalLog';

context('Index verify a11y', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/');
  });


  it('Index contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y(null, null, terminalLog);
  });

  it('Index contains no html errors', () => {
    cy.htmlvalidate();
  });
});
