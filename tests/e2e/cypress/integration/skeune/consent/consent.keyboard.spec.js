import {attribute6, labelSelector, primaryTooltip3Selector} from '../testSelectors';
import {backButtonSelector, contentSectionSelector, nokButtonSelectorForKeyboard, nokSectionSelector} from '../../../../../../theme/base/javascripts/selectors';

/**
 * Tests for behaviour of the consent screen which depends on the keyboard.
 */
context('Consent when using the keyboard', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Test showing / hiding the extra attributes', () => {
    it('Should show the extra attributes after hitting the label', () => {
      cy.contains(labelSelector, 'Show more information')
        .focus().type('{enter}');
      cy.contains(labelSelector, 'Show less information');
      cy.get(attribute6)
        .should('not.have.css', 'height', '1px')
        .should('not.have.css', 'width', '1px');
    });

    it('Should hide the extra attributes after hitting the label again', () => {
      // first click the show more label to show the attributes
      cy.contains(labelSelector, 'Show more information')
        .focus().type('{enter}');

      // try to hide them again
      cy.contains(labelSelector, 'Show less information')
        .focus().type('{enter}');

      // test assertions
      cy.contains(labelSelector, 'Show more information');
      cy.get(attribute6)
        .should('have.css', 'height', '1px')
        .should('have.css', 'width', '1px');
    });
  });

  describe('Shows / hides the tooltips on enter', () => {
    it('Shows the tooltip', () => {
      cy.focusAndEnter(primaryTooltip3Selector)
        .parent()
        .next()
        .should('be.visible');
    });

    it('Hides the tooltip', () => {
      // Make it visible
      cy.focusAndEnter(primaryTooltip3Selector)
        .parent()
        .next();

      // Hide and check if it worked
      cy.focusAndEnter(primaryTooltip3Selector)
        .parent()
        .next()
        .should('not.be.visible');
    });
  });

  describe('Shows the modals on enter', () => {
    it('Should show the incorrect modal', () => {
      cy.contains(labelSelector, 'Something incorrect?')
        .focus().type('{enter}');
      cy.contains('Is the data shown incorrect?')
        .should('be.visible');
    });
  });

  describe('Shows / hides the nok-section on enter', () => {
    it('Shows the nok-section when hitting the nok button', () => {
      cy.getAndEnter(nokButtonSelectorForKeyboard);
      cy.beVisible(nokSectionSelector);
      cy.notBeVisible(contentSectionSelector);
    });

    it('Hides the nok-section when hitting the back button', () => {
      cy.getAndEnter(backButtonSelector);
      cy.notBeVisible(nokSectionSelector);
      cy.beVisible(contentSectionSelector);
    });
  });
});
