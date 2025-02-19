context('WayfMouseBehaviour', () => {

  it('Should show five connected IdPs and the search field', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');

      // Load the connected IdPs by selecting their h3 titles
      cy.countIdps(7)
          .eq(2)
          .should('have.text', 'Connected IdP 3 en');

      // Test the search option by filtering on IdP 4, should yield one search result
      cy.get('.mod-search-input').type('IdP 4');

      // After filtering the search results, verify one result is visible
      cy.countIdps(1).should('have.text', 'Connected IdP 4 en');

      cy.onPage('Select an organisation to login to the service');
      // Ensure some elements are NOT on the page
      cy.notOnPage('Identity providers without access').should('not.exist');
      cy.notOnPage('Remember my choice');
      cy.notOnPage('Return to service provider');

  });

  it('Should show ten connected IdPs', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=10&addDiscoveries=0');
      cy.countIdps(10);
  });

  it('Should show no connected IdPs when cutoff point is configured', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5');
      cy.countIdps(0);

      cy.get('.mod-search-input').type('IdP');
      cy.countIdps(6);
  });

  it('Should show the return to service link when configured', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&backLink=true');
      cy.onPage('Select an organisation to login to the service');
      cy.onPage('Return to service provider');

      // Ensure some elements are NOT on the page
      cy.notOnPage('Identity providers without access');
      cy.notOnPage('Remember my choice');

      // To be more precise, the links should be in the header and footer
      cy.get('.mod-header .comp-links li:nth-child(1) a').should('have.text', 'Return to service provider');
      cy.get('.footer-menu .comp-links li:nth-child(2) a').should('have.text', 'Return to service provider');
  });

  it('Should show the remember my choice option', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true');
      // Ensure some elements are on the page
      cy.onPage('Select an organisation to login to the service');
      cy.onPage('Remember my choice');
      // Ensure some elements are NOT on the page
      cy.notOnPage('Identity providers without access');
      cy.notOnPage('Return to service provideraccess');
  });
})
