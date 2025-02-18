import {indexPageHeader} from '../testSelectors';
import {siteNoticeSelector} from '../../../../../../theme/base/javascripts/selectors';

context('Index on Skeune theme', () => {
  beforeEach(() => {
    cy.visit('https://engine.dev.openconext.local/');
  });

  it('Renders the index page and has all relevant data', () => {
    cy.beVisible(indexPageHeader).should('have.text', 'IdP Certificate and Metadata');
    cy.contains('SP Certificate and Metadata').should('be.visible');
    cy.contains('This is a service connected through').should('be.visible');
    cy.contains('Terms of Service').should('be.visible');
  });

  it('Shows the global site notice', () => {
    cy.visit('https://engine.dev.openconext.local/functional-testing/consent?showGlobalSiteNotice=1');
    cy.beVisible(siteNoticeSelector);
  });
});
