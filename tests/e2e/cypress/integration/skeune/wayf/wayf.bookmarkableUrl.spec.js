context('hideBookmarkableUrl', () => {
  describe('When the URL contains a SAMLRequest parameter', () => {
    it('replaces the URL with ?feedback=bookmark', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?SAMLRequest=somevalue');
      cy.url().should('not.include', 'SAMLRequest');
      cy.url().should('include', 'feedback=bookmark');
    });
  });

  describe('When the URL does not contain a SAMLRequest parameter', () => {
    it('leaves the URL unchanged', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.url().should('not.include', 'feedback=bookmark');
    });

    it('leaves other query parameters intact', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5');
      cy.url().should('not.include', 'feedback=bookmark');
      cy.url().should('include', 'connectedIdps=5');
    });
  });
});
