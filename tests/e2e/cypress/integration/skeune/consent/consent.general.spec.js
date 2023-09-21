import {attributesSelector, siteNoticeSelector} from '../../../../../../theme/base/javascripts/selectors';
import {attribute6, labelSelector, nokSectionTitleSelector, tooltip3Selector} from '../testSelectors';

context('Consent on Skeune theme', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  describe('Handles additional attributes correctly', () => {
    it('shows the correct amount of attributes on load', () => {
      cy.get(attributesSelector)
        .should('have.length', '13');
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
        .should('not.exist');
    });

    it('Should not show the nok-modal on load', () => {
      cy.notExistOrVisible(nokSectionTitleSelector);
    });
  });

  describe('Shows the right content on load', () => {
    it('Shows the global site notice', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/consent?showGlobalSiteNotice=1');
      cy.beVisible(siteNoticeSelector);
    });
  });
});
