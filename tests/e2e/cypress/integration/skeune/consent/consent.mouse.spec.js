import {attribute6, labelSelector, primaryTooltip3Selector} from '../testSelectors';
import {backButtonSelector, contentSectionSelector, nokButtonSelector, nokSectionSelector} from '../../../../../../theme/base/javascripts/selectors';

/**
 * Tests for behaviour of the consent screen which depends on the mouse.
 */
context('Consent when using the mouse', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Test showing / hiding the extra attributes', () => {
    it('Should show the extra attributes after clicking the label', () => {
      cy.contains(labelSelector, 'Show more information')
        .click();
      cy.contains(labelSelector, 'Show less information');
      cy.get(attribute6)
        .should('not.have.css', 'height', '1px')
        .should('not.have.css', 'width', '1px');
    });

    it('Should hide the extra attributes after clicking the label again', () => {
      // first click the show more label to show the attributes
      cy.contains(labelSelector, 'Show more information')
        .click({force: true});

      // try to hide them again
      cy.contains(labelSelector, 'Show less information')
        .click({force: true});

      // test assertions
      cy.contains(labelSelector, 'Show more information');
      cy.get(attribute6)
        .should('have.css', 'height', '1px')
        .should('have.css', 'width', '1px');
    });
  });

  describe('Shows / hides the tooltips on click', () => {
    it('Shows the tooltip', () => {
      cy.get(primaryTooltip3Selector)
        .click({force: true})
        .parent()
        .next()
        .should('be.visible');
    });

    it('Hides the tooltip', () => {
      // Make it visible
      cy.get(primaryTooltip3Selector)
        .click({force: true})
        .parent()
        .next();

      // Hide and check if it worked
      cy.get(primaryTooltip3Selector)
        .click({force: true})
        .parent()
        .next()
        .should('not.be.visible');
    });
  });

  describe('Shows the modals on click', () => {
    it('Should show the incorrect modal', () => {
      cy.contains(labelSelector, 'Something incorrect?')
        .click({force: true});
      cy.contains('Is the data shown incorrect?')
        .should('be.visible');
    });
  });

  describe('Shows / hides the nok-section on click', () => {
    it('Shows the nok-section when clicking the nok button', () => {
      cy.get(nokButtonSelector).click({force: true});
      cy.beVisible(nokSectionSelector);
      cy.notBeVisible(contentSectionSelector);
    });

    it('Hides the nok-section when clicking the back button', () => {
      cy.get(backButtonSelector).click({force: true});
      cy.notBeVisible(nokSectionSelector);
      cy.beVisible(contentSectionSelector);
    });
  });
});
