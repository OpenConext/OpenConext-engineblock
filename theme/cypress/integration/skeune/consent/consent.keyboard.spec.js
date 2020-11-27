/**
 * Tests for behaviour of the consent screen which depends on the keyboard.
 */
context('Consent when using the keyboard', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Test showing / hiding the extra attributes', () => {
    it('Should show the extra attributes after clicking the label', () => {
      cy.contains('label', 'Show more information')
        .focus().type('{enter}');
      cy.contains('label', 'Show less information');
      cy.get('ul.consent__attributes--nested')
        .should('be.visible');
    });

    it('Should hide the extra attributes after clicking the label again', () => {
      // first click the show more label to show the attributes
      cy.contains('label', 'Show more information')
        .focus().type('{enter}');

      // try to hide them again
      cy.contains('label', 'Show less information')
        .focus().type('{enter}');

      // test assertions
      cy.contains('label', 'Show more information');
      cy.get('ul.consent__attributes--nested')
        .should('not.be.visible');
    });
  });

  describe('Shows / hides the tooltips on click', () => {
    it('Shows the tooltip', () => {
      cy.focusAndEnter('label.tooltip[for="tooltip3"]:not(:first-child)')
        .next()
        .should('be.visible');
    });

    it('Hides the tooltip', () => {
      // Make it visible
      cy.focusAndEnter('label.tooltip[for="tooltip3"]:not(:first-child)')
        .next();

      // Hide and check if it worked
      cy.focusAndEnter('label.tooltip[for="tooltip3"]:not(:first-child)')
        .next()
        .should('not.be.visible');
    });
  });

  describe('Shows the modals on click', () => {
    it('Should show the nok-modal', () => {
      cy.contains('label', 'Something incorrect?')
        .focus().type('{enter}');
      cy.contains('Is the data shown incorrect?')
        .should('be.visible');
    });

    it('Should show the decline consent modal', () => {
      cy.contains('label', 'Cancel');
      cy.get('#cta_consent_nok')
        .next()
        .focus().type('{enter}');
      cy.contains('You don\'t want to share your data with the service')
        .should('be.visible');
    });
  });
});
