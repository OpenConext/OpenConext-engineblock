import {addAccountButtonSelector, cancelButtonSelector, defaultIdpItemSelector, defaultIdpSelector, noAccessTitle, noAccessFieldsetsSelector, previousSelectionTitleSelector, remainingIdpSelector, searchFieldClass,  selectedIdpsSelector, selectedIdpsSectionSelector, showFormSelector, submitRequestSelector, succesMessageSelector} from '../../../../../../theme/base/javascripts/selectors';
import {firstRemainingIdp, firstSelectedIdpDeleteDisable, selectedIdpDataIndex1} from '../testSelectors';

/**
 * Tests for behaviour of the WAYF which depends on the mouse
 */
context('WAYF when using the mouse', () => {
  describe('Test logging in', () => {
    it('Should login when selecting an idp', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(remainingIdpSelector)
        .eq(1)
        .click({force: true});
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.dev.openconext.local/?idp=https%3A//example.com/entityId/2');
      });
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
    });
  });

  describe('Should show a fully functional no access section when a disabled account is selected', () => {
    it('Should show the no access section on selecting a disabled account', () => {
      cy.openUnconnectedIdp(false);
      cy.contains(noAccessTitle, 'Sorry, no access for this account');
    });

    it('Should not show the form elements yet', () => {
      cy.openUnconnectedIdp(false);
      cy.get(noAccessFieldsetsSelector)
        .should('not.be.visible');
    });

    it('Should have a functioning cancel button', () => {
      cy.openUnconnectedIdp(false);
      cy.get(showFormSelector).click({force: true});
      cy.get(cancelButtonSelector).click({force: true});
      cy.get(noAccessTitle)
        .should('not.be.visible');
    });

    it('Should show the form fields after hitting request access', () => {
      cy.openUnconnectedIdp(false);
      cy.get(showFormSelector).click({force: true});
      cy.get(noAccessFieldsetsSelector)
        .should('be.visible');
    });

    it('Should hide form fields after hitting cancel', () => {
      cy.openUnconnectedIdp(false);
      cy.get(showFormSelector).click({force: true});
      cy.get(cancelButtonSelector).click({force: true});
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/2"]').click({force: true});
      cy.get(noAccessFieldsetsSelector)
        .should('not.be.visible');
    });

    it.skip('Should show the success message', () => {
      cy.fillNoAccessForm(false);
      cy.get(submitRequestSelector).click({force: true});
      cy.get(noAccessTitle).should('not.be.visible');
      cy.get(succesMessageSelector).should('be.visible');
    });

    it('Should be able to fill the request access form', () => {
      cy.fillNoAccessForm(false);
    });

    it.skip('Should not show the success message when selecting a new disabled account', () => {
      cy.fillNoAccessForm(false);
      cy.get(submitRequestSelector).click({force: true});
      cy.wait(500);
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/3').click({force: true});
      cy.get(succesMessageSelector).should('not.be.visible');
    });

    it.skip('Should also not show the form fields after selecting a new disabled account', () => {
      cy.fillNoAccessForm(false);
      cy.get(submitRequestSelector).click({force: true});
      cy.wait(500);
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/3').click({force: true});
      cy.get(noAccessFieldsetsSelector)
        .should('not.be.visible');
    });
  });

  describe('Should have a working default Idp Banner', () => {
    it('Should have a default Idp banner visible', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?showIdpBanner=1');
      cy.get(defaultIdpSelector).should('be.visible');
    });

    it('Should scroll to the default Idp when clicking the banner link', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=10&defaultIdpEntityId=https://example.com/entityId/9&showIdpBanner=1');

      // click the banner link & check if it did what it should have
      cy.get(defaultIdpSelector).click();
      cy.get(defaultIdpItemSelector)
        .should('be.visible')
        .should('have.focus');
    });
  });

  describe('Should show a fully functional previous selection section', () => {
    it('Test if the previous section exists with the right title', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.get(previousSelectionTitleSelector)
        .should('be.visible');
    });

      it('Test if the section contains the use account button', () => {
        cy.addOnePreviouslySelectedIdp(false);
        cy.get(addAccountButtonSelector)
          .should('be.visible');
      });

    it('Test if it contains the right amount of idps', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.get(selectedIdpsSelector)
        .should('have.length', 1);
    });

    it('Test if selecting a previously selected idp works', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectFirstIdp(true, selectedIdpDataIndex1);
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.dev.openconext.local/?idp=https%3A//example.com/entityId/1');
      });
    });

    it('Test if the count was raised on the selected idp', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectFirstIdp(true, selectedIdpDataIndex1);
      cy.loadWayf();
      cy.get(selectedIdpDataIndex1).should('have.attr', 'data-count', '2');
    });

    it('Test the add account button opens up the search & puts focus on the search field, then select the focused element.', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectAccountButton(false);
      cy.focused().should('have.class', searchFieldClass);
      cy.get(selectedIdpsSectionSelector).should('not.be.visible');
    });

    it('Test the edit button allows deleting an account', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectAccountButton(false);
      cy.selectFirstIdpAndReturn(false);
      cy.toggleEditButton(false);
      cy.hitDeleteButton();
      // The previous selection is now closed and empty
      cy.get('.wayf__previousSelection .wayf__idp h3')
        .should('have.length', 0);
    });

    it('Test deleting the last previously selected idp hides the section, shows the remaining idps, focuses on the searchbar & adds the deleted idp to the list', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.hitDeleteButton(true, firstSelectedIdpDeleteDisable);
      cy.focused().should('have.class', searchFieldClass);
      cy.get(addAccountButtonSelector).should('not.be.visible');
    });

    it('Test the remaining list contains the deleted idp & is sorted alphabetically', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.hitDeleteButton(true, firstSelectedIdpDeleteDisable);
      cy.get('.wayf__remainingIdps .wayf__idp[data-entityid="https://example.com/entityId/1"]').should('exist');
      cy.get(firstRemainingIdp).should('have.attr', 'data-index', '1');
    });
  });
});
