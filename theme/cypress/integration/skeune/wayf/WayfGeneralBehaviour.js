/**
 * Tests for behaviour that has nothing to do with clicking / pressing enter.
 */
context('WAYF behaviour not tied to mouse / keyboard navigation', () => {
  describe('Test elements shown on page', () => {
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

    it('Should show no connected IdPs when cutoff point is configured', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5');
      cy.get('.wayf__remainingIdps .wayf__idp')
        .should('not.be.visible');
    });

    it('Should show found IdPs when cutoff point is configured and user searched', () => {
      cy.get('.search__field').type('IdP');
      cy.get('.wayf__idp')
        .should('have.length', 6)
        .should('be.visible');
    });

    it('Should show 5 disconnected IdPs', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?displayUnconnectedIdpsWayf=1&unconnectedIdps=5');
      cy.get('.wayf__idp--noAccess')
        .should('have.length', 5)
        .should('be.visible');
    });

    it('Should show no disconnected IdPs when the flag is false', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?displayUnconnectedIdpsWayf=0&unconnectedIdps=5');
      cy.get('.wayf__idp--noAccess')
        .should('not.exist');
    });

    it('Should show 5 disconnected IdPs', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?displayUnconnectedIdpsWayf=1&unconnectedIdps=5');
      cy.get('.wayf__idp--noAccess')
        .should('have.length', 5)
        .should('be.visible');
    });

    it.only('Should show no disconnected IdPs when the flag is false', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?displayUnconnectedIdpsWayf=0&unconnectedIdps=5');
      cy.get('.wayf__idp--noAccess')
        .should('not.exist');
    });
  });

  describe('Test if search works as it should', () => {
    it('Should show no results when no IdPs are found', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('.wayf__search').type('OllekebollekeKnol');
      cy.get('.wayf__noResults').should('be.visible');
    });

    it('Should be able to search for an idp', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('.wayf__search').type('4');

      // After filtering the search results, verify one result is visible
      cy.get('.wayf__remainingIdps .wayf__idp:not([data-weight="0"])')
        .should('have.length', 1)
        .should('contain.text', 'Connected IdP 4 en');
    });

    it('Should get the correct weight for an idp with a full match on the title', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=50');
      cy.get('.wayf__search').type('Connected Idp 4 en');
      cy.get('.wayf__remainingIdps .wayf__idp[data-weight="215"]')
        .should('have.length', 1)
        .should('contain.text', 'Connected IdP 4 en');
    });

    it('Should get the correct weight for an idp with a partial match on the title', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=50');
      cy.get('.wayf__search').type('Connected Idp 4');
      cy.get('.wayf__remainingIdps .wayf__idp[data-weight="82"]')
        .should('have.length', 10);
    });

    it('Should get the correct weight for an idp with a full match on the keyword', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=50');
      cy.get('.wayf__search').type('awesome idp');
      cy.get('.wayf__remainingIdps .wayf__idp[data-weight="100"]')
        .should('have.length', 50);
    });

    it('Should get the correct weight for an idp with a partial match on the keyword', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=50');
      cy.get('.wayf__search').type('awesome');
      cy.get('.wayf__remainingIdps .wayf__idp[data-weight="8"]')
        .should('have.length', 50);
    });

    it('Should get the correct weight for an idp with a full match on the entityId', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=50');
      cy.get('.wayf__search').type('https://example.com/entityid/1');
      cy.get('.wayf__remainingIdps .wayf__idp[data-weight="60"]')
        .should('have.length', 1);
    });

    it('Should get the correct weight for an idp with a partial match on the entityId', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=50');
      cy.get('.wayf__search').type('https://example.com/entityid/1');
      cy.get('.wayf__remainingIdps .wayf__idp[data-weight="7"]')
        .should('have.length', 10);
    });

    it('Should not take into account the space at the end of a searchTerm', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('.wayf__search').type('con 1');
      cy.get('.wayf__remainingIdps .wayf__idp')
        .should('have.length', 5);
    });
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

    it('Ensure the CTA is not present when the feature flag is disabled', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?showIdpBanner=0');
      cy.get('.remainingIdps__eduId').should('not.exist');
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

  describe('Should show the return to service link when configured', () => {
      it('Load the page', () => {
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5&backLink=true');
      });

      it('The link should be below the IdPs list', () => {
        cy.get('.wayf a.wayf__backLink')
          .should('have.text', 'Return to Service Provider');
      });
  });
});
