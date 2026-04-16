import {
    addAccountButtonSelector,
    defaultIdpInformational,
    preferredIdpsSectionSelector,
    remainingIdpsSearchLabelSelector,
} from '../../../../../../theme/base/javascripts/selectors';

const WAYF = 'https://engine.dev.openconext.local/functional-testing/wayf';
const ENTITY_1 = 'https://example.com/entityId/1';
const ENTITY_2 = 'https://example.com/entityId/2';

const withPreferred = `${WAYF}?preferredIdpEntityIds%5B%5D=${encodeURIComponent(ENTITY_1)}`;
const preferredEqualsDefault = `${WAYF}?defaultIdpEntityId=${encodeURIComponent(ENTITY_1)}&preferredIdpEntityIds%5B%5D=${encodeURIComponent(ENTITY_1)}&showIdPBanner=1`;
const preferredDiffersFromDefault = `${WAYF}?defaultIdpEntityId=${encodeURIComponent(ENTITY_2)}&preferredIdpEntityIds%5B%5D=${encodeURIComponent(ENTITY_1)}&showIdPBanner=1`;

describe('Search label above regular IdP list', () => {
    it('shows search label when preferred IdPs are configured', () => {
        cy.visit(withPreferred);
        cy.get(remainingIdpsSearchLabelSelector).should('be.visible');
    });

    it('does not show search label when no preferred IdPs are configured', () => {
        cy.visit(WAYF);
        cy.get(remainingIdpsSearchLabelSelector).should('not.exist');
    });
});

describe('Preferred IdPs section visibility', () => {
    it('shows the preferred section when preferred IdPs are configured', () => {
        cy.visit(withPreferred);
        cy.get(preferredIdpsSectionSelector).should('be.visible');
    });

    it('does not show preferred section when no preferred IdPs are configured', () => {
        cy.visit(WAYF);
        cy.get(preferredIdpsSectionSelector).should('not.exist');
    });

    it('shows preferred IdP in preferred section and not in remaining list', () => {
        cy.visit(withPreferred);

        // entityId/1 appears inside the preferred section
        cy.get(`${preferredIdpsSectionSelector} [data-entityid="${ENTITY_1}"]`).should('exist');

        // entityId/1 must NOT appear in the remaining (non-preferred) IdP list
        cy.get(`.wayf__remainingIdps .wayf__idpList:not(.wayf__idpList--preferred) [data-entityid="${ENTITY_1}"]`)
            .should('not.exist');
    });

    it('shows exactly one item in the preferred section when one preferred IdP is configured', () => {
        // addDiscoveries=false prevents discovery entries from inflating the preferred count
        cy.visit(`${withPreferred}&addDiscoveries=false`);
        cy.get(`${preferredIdpsSectionSelector} li`).should('have.length', 1);
    });

    it('shows exactly two items in the preferred section when two preferred IdPs are configured', () => {
        const withTwoPreferred = `${WAYF}?preferredIdpEntityIds%5B%5D=${encodeURIComponent(ENTITY_1)}&preferredIdpEntityIds%5B%5D=${encodeURIComponent(ENTITY_2)}&addDiscoveries=false`;
        cy.visit(withTwoPreferred);
        cy.get(`${preferredIdpsSectionSelector} li`).should('have.length', 2);
    });
});

describe('Preferred IdPs with previous selection', () => {
    beforeEach(() => {
        cy.addOnePreviouslySelectedIdp(false, withPreferred);
    });

    it('hides preferred section when a previous selection is present', () => {
        cy.visit(withPreferred);
        cy.get(preferredIdpsSectionSelector).should('not.be.visible');
    });

    it('reveals preferred section after clicking "Use another account"', () => {
        cy.visit(withPreferred);
        cy.get(addAccountButtonSelector).click({ force: true });
        cy.get(preferredIdpsSectionSelector).should('be.visible');
    });
});

describe('Three display scenarios', () => {
    it('scenario 1: no preferred IdPs: banner visible, preferred section absent', () => {
        cy.visit(`${WAYF}?showIdPBanner=1`);
        cy.get(defaultIdpInformational).should('be.visible');
        cy.get(preferredIdpsSectionSelector).should('not.exist');
    });

    it('scenario 3: default IdP is preferred: preferred section visible, banner suppressed', () => {
        cy.visit(preferredEqualsDefault);
        cy.get(preferredIdpsSectionSelector).should('be.visible');
        cy.get(defaultIdpInformational).should('not.exist');
    });

    it('scenario 4: preferred IdP differs from default: both preferred section and banner visible', () => {
        cy.visit(preferredDiffersFromDefault);
        cy.get(preferredIdpsSectionSelector).should('be.visible');
        cy.get(defaultIdpInformational).should('be.visible');
    });
});
