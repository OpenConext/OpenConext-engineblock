context('Consent on Skeune theme', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Handles additional attributes correctly', () => {
    it('shows the correct amount of attributes on load', () => {
      cy.get('ul.consent__attributes > li')
        .should('have.length', '7');
    });

    it('Should not show the extra attributes on load', () => {
      cy.get('ul.consent__attributes--nested')
        .should('not.be.visible');
    });

    it('Should show the more info label', () => {
      cy.contains('label', 'Show more information')
        .click();
    });

    it('Should show the extra attributes after clicking the label', () => {
      cy.contains('label', 'Show more information')
        .click();
      cy.contains('label', 'Show less information');
      cy.get('ul.consent__attributes--nested')
        .should('be.visible');
    });
  });

  describe('gives openconext information', () => {
    it('Should not show the about-modal on load', () => {
      cy.get('label[for="consent_disclaimer_about"] + section h3')
        .should('not.be.visible');
    });

    it('Should show the about-modal after selection', () => {
      cy.contains('label', 'OpenConext')
        .click({force: true})
        .next()
        .contains('h3', 'Logging in through OpenConext');
    });
  });

  describe('shows information on how to report incorrect data', () => {
    it('Should not show the nok-modal on load', () => {
      cy.get('label[for="cta_consent_nok"] + section h3')
        .should('not.be.visible');
    });

    it('Should show the nok-modal after selection', () => {
      cy.contains('label', 'Something incorrect?')
        .click({force:true});
      cy.get('.idpRow > section.modal__value')
        .contains('h3', 'Is the data shown incorrect?');
    });
  });

  describe('gives explanation about the unique identifier', () => {
    it('Should not show the number modal on load', () => {
      cy.get('label[for="consent_disclaimer_number"] + section a[href="https://example.org"]')
        .should('not.be.visible');
    });

    it('Should show the number modal after selection', () => {
      cy.contains('label', 'a number that uniquely identifies you for this service.')
        .click({force: true})
        .next()
        .contains('a', 'Read more');
    });
  });

  describe('can decline consent', () => {
    it('Should not show the decline consent modal on load', () => {
      cy.get('label[for="cta_consent_nok"] + section h3')
        .should('not.be.visible');
    });

    it('Should show the decline consent modal after selection', () => {
      cy.contains('label', 'No, I do not agree')
        .click({force: true});
      cy.get('.ctas .modal__value')
        .should('be.visible');
    });
  });
});
