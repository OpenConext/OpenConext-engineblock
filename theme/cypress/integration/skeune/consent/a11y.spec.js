context('Consent a11y verify', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  it.skip('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y();
  });

  it.skip('contains no html errors', () => {
    cy.htmlvalidate();
  });
});
