context('Index on Skeune theme', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/');
  });

  it('Renders the index page and has all relevant data', () => {
    cy.beVisible('main div.main:first-of-type h1').should('have.text', 'IdP Certificate and Metadata');
    cy.contains('SP Certificate and Metadata').should('be.visible');
    cy.contains('This is a service connected through').should('be.visible');
    cy.contains('Terms of Service').should('be.visible');
  });
});
