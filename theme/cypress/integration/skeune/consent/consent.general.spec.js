import {attributesSelector} from '../../../../base/javascripts/selectors';
import {attribute6, labelSelector, nokSectionTitleSelector, tooltip3Selector} from '../testSelectors';

context('Consent on Skeune theme', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Handles additional attributes correctly', () => {
    it('shows the correct amount of attributes on load', () => {
      cy.get(attributesSelector)
        .should('have.length', '11');
      cy.get(attribute6)
        .should('have.css', 'height', '1px')
        .should('have.css', 'width', '1px');
    });

    it('Should show the more info label', () => {
      cy.contains(labelSelector, 'Show more information');
    });
  });

  describe('Hides the correct content on load', () => {
    it('Hides the tooltip on load', () => {
      cy.get(tooltip3Selector)
        .next()
        .should('not.be.visible');
    });

    it('Should not show the nok-modal on load', () => {
      cy.notBeVisible(nokSectionTitleSelector);
    });
  });
});
