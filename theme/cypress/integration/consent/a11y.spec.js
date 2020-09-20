/// <reference types="cypress" />

context('Consent', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  it('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y();
  });

  it('contains no html errors', () => {
    cy.htmlvalidate();
    // todo: figure out how to configure SRI for scripts/link(css)/link(ico) elements
  });
});
