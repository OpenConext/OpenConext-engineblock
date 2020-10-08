/**
 * Tests for behaviour that has nothing to do with clicking / pressing enter.
 */
context('WAYF behaviour not tied to mouse / keyboard navigation', () => {
  // todo: test after search is functional
  it.skip('Should be able to search for an idp', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    // Test the search option by filtering on IdP 4, should yield one search result
    cy.get('#wayf__search').type('IdP 4');

    // After filtering the search results, verify one result is visible
    // cy.countIdps(1).should('have.text', 'Connected IdP 4 en'); todo add this once search is functional
  });

  it('Should not show backLink and rememberChoice', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    cy.notOnPage('Identity providers without access').should('not.exist');
    cy.notOnPage('Remember my choice');
    cy.notOnPage('Return to service provider');
  });

  it('Should show ten connected IdPs', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10');
      cy.get('.wayf__idp h3')
        .should('have.length', 10);
  });

  // todo: test after search is functional
  it.skip('Should show no connected IdPs when cutoff point is configured', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5');
      cy.get('.wayf__idp h3')
        .should('have.length', 0);

      cy.get('#wayf__search').type('IdP');
      cy.get('.wayf__idp h3')
        .should('have.length', 6);
  });

  // todo: test after search is functional
  it.skip('Should show no results when no IdPs are found', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    cy.get('#wayf__search').type('OllekebollekeKnol');
    cy.get('wayf__noresults').should('be.visible');
  });

  describe('Should show five connected IdPs, the search field and the eduId CTA', () => {
    it('Load the page', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    });

    it('Get the connected IdPs & check if it\'s correct', () => {
      cy.get('.wayf__idp h3')
        .should('have.length', 5)
        .eq(2)
        .should('have.text', 'Login with Connected IdP 3 en');
    });

    it('Check if the search field is present', () => {
      cy.get('.wayf__search').should('exist');
    });

    it('Check if the eduId is present', () => {
      cy.contains('.remainingIdps__eduId', 'eduID is available as an alternative');
    });
  });

  // todo: test after backLink is added
  describe('Should show the return to service link when configured', () => {
      it.skip('Load the page', () => {
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5&backLink=true');
      });

      it.skip('To be more precise, the links should be in the header and footer', () => {
        cy.get('.mod-header .comp-links li:nth-child(1) a').should('have.text', 'Return to service provider');
        cy.get('.footer-menu .comp-links li:nth-child(2) a').should('have.text', 'Return to service provider');
      });
  });

  describe('Should show the remember my choice option', () => {
      it('Load the page with rememberChoiceFeature', () => {
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true');
      });

      it('Ensure some elements are on the page', () => {
        cy.onPage('Select an organisation to login to the service');
        cy.onPage('Remember my choice');
      });

      it('Ensure some elements are NOT on the page', () => {
        cy.notOnPage('Identity providers without access');
        cy.notOnPage('Return to service provideraccess');
      });
  });
});
