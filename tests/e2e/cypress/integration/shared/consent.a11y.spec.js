context('Consent verify a11y', () => {
  beforeEach(() => {
    cy.visit('https://engine.dev.openconext.local/functional-testing/consent');
  });

  it('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y();
  });

  it('contains no html errors', () => {
    cy.htmlvalidate();
  });
});
