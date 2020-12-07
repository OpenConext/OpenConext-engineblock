context('Index verify a11y', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/');
  });


  it('Index contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y();
  });

  it('Index contains no html errors', () => {
    cy.htmlvalidate();
  });
});
