/**
 * Tests for behaviour of the consent screen which depends on the keyboard.
 */
context('Consent when using the keyboard', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Test showing / hiding the extra attributes', () => {
    it('Should show the extra attributes after hitting the label', () => {
      cy.contains('label', 'Show more information')
        .focus().type('{enter}');
      cy.contains('label', 'Show less information');
      cy.get('ul.consent__attributes li:nth-of-type(6)')
        .should('not.have.css', 'height', '1px')
        .should('not.have.css', 'width', '1px');
    });

    it('Should hide the extra attributes after hitting the label again', () => {
      // first click the show more label to show the attributes
      cy.contains('label', 'Show more information')
        .focus().type('{enter}');

      // try to hide them again
      cy.contains('label', 'Show less information')
        .focus().type('{enter}');

      // test assertions
      cy.contains('label', 'Show more information');
      cy.get('ul.consent__attributes li:nth-of-type(6)')
        .should('have.css', 'height', '1px')
        .should('have.css', 'width', '1px');
    });
  });

  describe('Shows / hides the tooltips on enter', () => {
    it('Shows the tooltip', () => {
      cy.focusAndEnter('.ie11__label > label.tooltip[for="tooltip3consent_attribute_source_idp"]')
        .parent()
        .next()
        .should('be.visible');
    });

    it('Hides the tooltip', () => {
      // Make it visible
      cy.focusAndEnter('.ie11__label > label.tooltip[for="tooltip3consent_attribute_source_idp"]')
        .parent()
        .next();

      // Hide and check if it worked
      cy.focusAndEnter('.ie11__label > label.tooltip[for="tooltip3consent_attribute_source_idp"]')
        .parent()
        .next()
        .should('not.be.visible');
    });
  });

  describe('Shows the modals on enter', () => {
    it('Should show the incorrect modal', () => {
      cy.contains('label', 'Something incorrect?')
        .focus().type('{enter}');
      cy.contains('Is the data shown incorrect?')
        .should('be.visible');
    });
  });

  describe('Shows / hides the nok-section on enter', () => {
    it('Shows the nok-section when hitting the nok button', () => {
      cy.getAndEnter('.consent__ctas > .button--tertiary');
      cy.beVisible('.consent__nok');
      cy.notBeVisible('.consent__content');
    });

    it('Hides the nok-section when hitting the back button', () => {
      cy.getAndEnter('.consent__nok-back');
      cy.notBeVisible('.consent__nok');
      cy.beVisible('.consent__content');
    });
  });
});
