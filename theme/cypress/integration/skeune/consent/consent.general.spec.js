context('Consent on Skeune theme', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Handles additional attributes correctly', () => {
    it('shows the correct amount of attributes on load', () => {
      cy.get('ul.consent__attributes > li')
        .should('have.length', '11');
      cy.notBeVisible('ul.consent__attributes li:nth-of-type(6)');
    });

    it('Should not show the extra attributes on load', () => {
      cy.get('ul.consent__attributes--nested')
        .should('not.be.visible');
    });

    it('Should show the more info label', () => {
      cy.contains('label', 'Show more information');
    });
  });

  describe('Hides the correct content on load', () => {
    it('Hides the tooltip on load', () => {
      cy.get('label.tooltip[for="tooltip3"]:not(:first-child)')
        .next()
        .should('not.be.visible');
    });

    it('Should not show the nok-modal on load', () => {
      cy.notBeVisible('label[for="cta_consent_nok"] + section h3');
    });

    it('Should not show the decline consent modal on load', () => {
      cy.notBeVisible('label[for="cta_consent_nok"] + section h3');
    });

    it('Should not show the nok-section on load', () => {
      cy.notBeVisible('.consent__nok');
    });
  });
});
