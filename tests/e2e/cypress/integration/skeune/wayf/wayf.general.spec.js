import {idpTitle, unconnectedIdpSelector, weight100Selector, weight215Selector, weight60Selector, weight7Selector, weight8Selector, weight82Selector} from '../testSelectors';
import {
  defaultIdpInformational,
  idpSelector,
  matchSelector,
  noResultSectionSelector,
  remainingIdpSelector,
  rememberChoiceId,
  rememberChoicePerIdpClass,
  rememberChoiceTooltipToggleSelector,
  rememberChoiceTooltipValueSelector,
  searchFieldSelector,
  searchResetSelector,
  searchSubmitSelector,
  siteNoticeSelector
} from '../../../../../../theme/base/javascripts/selectors';

/**
 * Tests for behaviour that has nothing to do with clicking / pressing enter.
 */
context('WAYF behaviour not tied to mouse / keyboard navigation', () => {
  describe('Test elements shown on page', () => {
    it('Should not show backLink and rememberChoice', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.notOnPage('Identity providers without access').should('not.exist');
      cy.notOnPage('Remember my choice');
      cy.notOnPage('Return to service provider');
    });

    it('Should show ten connected IdPs', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=10&addDiscoveries=0');
      // 11 because of the template div
      cy.get(idpTitle)
        .should('have.length', 11);
    });

    it('Should show no connected IdPs when cutoff point is configured', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5');
      cy.get(remainingIdpSelector)
        .should('not.be.visible');
    });

    it('Should show found IdPs when cutoff point is configured and user searched', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5&addDiscoveries=0');
      cy.get(searchFieldSelector).type('IdP');
      // 7 because of template div
      cy.get(idpSelector)
        .should('have.length', 7)
        .should('be.visible');
    });

    it('Should show 5 disconnected IdPs', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?displayUnconnectedIdpsWayf=1&unconnectedIdps=5&addDiscoveries=0');
      //
      cy.get(unconnectedIdpSelector)
        .should('have.length', 6)
        .should('be.visible');
    });

    it('Should show no disconnected IdPs when the flag is false', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?displayUnconnectedIdpsWayf=0&unconnectedIdps=5');
      cy.get(unconnectedIdpSelector)
        .should('not.be.visible')
        .should('have.length', 1);
    });

    it.only('Shows the global site notice', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?showGlobalSiteNotice=1');
      cy.beVisible(siteNoticeSelector);
    });
  });


  describe('Test if search works as it should', () => {
    it('Should show no results when no IdPs are found', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector).type('OllekebollekeKnol');
      cy.get(noResultSectionSelector).should('be.visible');
      cy.get(searchSubmitSelector).should('have.class', 'visually-hidden');
      cy.get(searchResetSelector).should('be.visible');
    });

    it('Should be able to search for an idp', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector).type('4');
      // When the user starts typing, the reset (x) button should appear, replacing the search icon
      cy.get(searchSubmitSelector).should('have.class', 'visually-hidden');
      cy.get(searchResetSelector).should('be.visible');

      // After filtering the search results, verify one result is visible (checking for two as the template div is not visible)
      cy.get(matchSelector)
        .should('have.length', 2)
        .should('contain.text', 'Connected IdP 4 en');
    });

    it('Should be able to find IDP by entering keyword for discovery idp', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector).type('royal');
      cy.get(matchSelector)
        .should('have.length', 2)
        .should('contain.text', 'National University of the Netherlands');
    });

    it('Should get the correct weight for an idp with a full match on the title', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=50');
      cy.get(searchFieldSelector).type('Connected Idp 4 en');
      cy.get(weight215Selector)
        .should('have.length', 1)
        .should('contain.text', 'Connected IdP 4 en');
    });

    it('Should get the correct weight for an idp with a partial match on the title', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=50');
      cy.get(searchFieldSelector).type('Connected Idp 4');
      cy.get(weight82Selector)
        .should('have.length', 10);
    });

    // Skipped pending investigation — see #2110
    it.skip('Should get the correct weight for an idp with a full match on the keyword', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=50');
      cy.get(searchFieldSelector).type('awesome idp');
      cy.get(weight100Selector)
        .should('have.length', 50);
    });

    it('Should get the correct weight for an idp with a partial match on the keyword', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=50');
      cy.get(searchFieldSelector).type('awesome');
      cy.get(weight8Selector)
        .should('have.length', 50);
    });

    // Skipped pending investigation — see #2110
    it.skip('Should get the correct weight for an idp with a full match on the entityId', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=50');
      cy.get(searchFieldSelector).type('https://example.com/entityId/1');
      cy.get(weight60Selector)
        .should('have.length', 1);
    });

    // Skipped pending investigation — see #2110
    it.skip('Should get the correct weight for an idp with a partial match on the entityId', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=50');
      cy.get(searchFieldSelector).type('/1');
      cy.get(weight7Selector)
        .should('have.length', 11);
    });

    // Skipped pending investigation — see #2110
    it.skip('Should not take into account the space at the end of a searchTerm', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector).type('con 1');
      cy.get(remainingIdpSelector)
        .should('have.length', 5);
    });

    // Skipped pending investigation — see #2110
    it.skip('Should reset the search text when clicking the reset button', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector).type('con 1');
      cy.get(searchResetSelector).click({force:true});
      cy.get(searchSubmitSelector).should('be.visible');
      cy.get(searchResetSelector).should('have.class', 'visually-hidden');
      cy.get(defaultIdpInformational).should('be.visible');
      cy.get(remainingIdpSelector)
        .should('have.length', 5);
    });
  });

  describe('Should show five connected IdPs, the search field and the defaultIdp CTA', () => {
    it('Get the connected IdPs & check if it\'s correct', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?addDiscoveries=0');
      cy.get(idpTitle)
        .should('have.length', 6)
        .eq(2)
        .should('have.text', 'Login with Connected IdP 3 en');
    });

    it('Check if the search field is present', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector).should('exist');
    });

    // Skipped pending investigation — see #2110
    it.skip('Check if the defaultIdp is present', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.contains(defaultIdpInformational, 'is available as an alternative');
    });

    it('Ensure the CTA is not present when the feature flag is disabled', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?showIdPBanner=0');
      cy.get(defaultIdpInformational).should('not.exist');
    });

    it('Ensure the default IdP has the correct data attribute', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?defaultIdpEntityId=https://example.com/entityId/3');
      cy.get('div[data-entityid="https://example.com/entityId/3"]')
        .should('have.id', 'defaultIdp');
    });
  });

  describe('Should show the remember my choice option', () => {
    it('Ensure some elements are on the page', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true');
      cy.onPage('Select an account to login');
      cy.onPage('Remember my choice');
    });

    it('Ensure some elements are NOT on the page', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true');
      cy.notOnPage('Identity providers without access');
      cy.notOnPage('Return to service provideraccess');
    });
  });

  describe('Should show the per-SP remember my choice option with a tooltip', () => {
    it('Renders the per-SP checkbox with a tooltip toggle', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get(`.${rememberChoicePerIdpClass}`).should('exist');
      cy.get(rememberChoiceTooltipToggleSelector).should('exist');
    });

    it('Hides the tooltip content until the toggle is activated', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get(rememberChoiceTooltipValueSelector).should('not.be.visible');
      cy.get(rememberChoiceTooltipToggleSelector).click({force: true});
      cy.get(rememberChoiceTooltipValueSelector).should('be.visible');
      cy.get(rememberChoiceTooltipToggleSelector).click({force: true});
      cy.get(rememberChoiceTooltipValueSelector).should('not.be.visible');
    });

    it('Toggles aria-hidden and aria-expanded on the tooltip when the label is clicked', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get(rememberChoiceTooltipValueSelector).should('have.attr', 'aria-hidden', 'true');
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-expanded', 'false');
      cy.get('.wayf__rememberChoice label.tooltip').click();
      cy.get(rememberChoiceTooltipValueSelector).should('not.have.attr', 'aria-hidden');
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-expanded', 'true');
    });

    it('Opens the tooltip when the label is focused and Enter is pressed', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get('.wayf__rememberChoice label.tooltip').focus().type('{enter}');
      cy.get(rememberChoiceTooltipValueSelector).should('not.have.attr', 'aria-hidden');
    });

    it('Does not show the tooltip toggle for the global (non per-SP) variant', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=false');
      cy.get(`.${rememberChoicePerIdpClass}`).should('not.exist');
      cy.get(rememberChoiceTooltipToggleSelector).should('not.exist');
      cy.onPage('Remember my choice');
    });

    it('Sets the hidden rememberChoice field to 1 on the submitted IdP form when the checkbox is checked', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=1&addDiscoveries=0&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.window().then((win) => {
        cy.stub(win.HTMLFormElement.prototype, 'submit').as('formSubmit');
      });
      cy.get(`#${rememberChoiceId}`).check();
      cy.get(idpSelector).first().click({force: true});
      cy.get('@formSubmit').should('have.been.calledOnce');
      cy.get(idpSelector).first().find('input[name="rememberChoice"]').should('have.value', '1');
    });

    it('Leaves the hidden rememberChoice field at 0 on the submitted IdP form when the checkbox is left unchecked', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=1&addDiscoveries=0&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.window().then((win) => {
        cy.stub(win.HTMLFormElement.prototype, 'submit').as('formSubmit');
      });
      cy.get(idpSelector).first().click({force: true});
      cy.get('@formSubmit').should('have.been.calledOnce');
      cy.get(idpSelector).first().find('input[name="rememberChoice"]').should('have.value', '0');
    });
  });

  describe('Preferred IdPs section heading', () => {
    it('Should show the preferred IdPs section with the correct heading when preferred IdPs are configured', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F1');
      cy.get('.wayf__preferredIdps').should('be.visible');
    });
  });

  describe('Should show the return to service link when configured', () => {
      it('Load the page & check if the page is there', () => {
        cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&backLink=true');
        cy.get('.wayf__backLink')
          .should('be.visible');
      });
  });

  describe('Test hides and shows IdP list', () => {
    // Skipped pending investigation — see #2110
    it.skip('Should hide the IdP link when search term is provided', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get('.search__field').type('search-term');
      cy.get(defaultIdpInformational).should('not.be.visible');
    });

    // Skipped pending investigation — see #2110
    it.skip('Should show the IdP link when search term is provided', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get('.search__field').clear();
      cy.onPage('If your organisation is not listed');
    });
  });
});
