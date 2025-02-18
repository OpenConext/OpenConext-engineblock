import {
  addAccountButtonSelector,
  cancelButtonSelector,
  defaultIdpClass,
  defaultIdpItemSelector,
  defaultIdpSelector,
  emailErrorSelector,
  emailFieldSelector,
  idpClass,
  nameErrorSelector,
  nameFieldSelector,
  noAccessTitle,
  noAccessFieldsetsSelector,
  previousSelectionTitleSelector,
  remainingIdpSelector,
  searchFieldClass,
  searchFieldSelector,
  selectedIdpsSelector,
  selectedIdpsSectionSelector,
  showFormSelector,
  submitRequestSelector,
  succesMessageSelector
} from '../../../../../../theme/base/javascripts/selectors';
import {firstRemainingIdp, firstSelectedIdpDeleteDisable, selectedIdpDataIndex1} from '../testSelectors';

/**
 * Tests for behaviour of the WAYF which depends on the keyboard.
 */
context('WAYF when using the keyboard', () => {
  describe('Test logging in', () => {
    it('Should login when selecting an idp', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(remainingIdpSelector)
        .eq(1)
        .focus()
        .type('{enter}');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.dev.openconext.local/?idp=https%3A//example.com/entityId/2');
      });
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
    });

    it('Should login to first IdP when hitting enter', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector)
        .type('{enter}');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.dev.openconext.local/?idp=https%3A//example.com/entityId/1');
      });
    });

    it('Should login to topmost  IdP when hitting enter', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf');
      cy.get(searchFieldSelector)
        .type('2')
        .type('{enter}');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.dev.openconext.local/?idp=https%3A//example.com/entityId/2');
      });
    });
  });

  // todo if html spec is changed, or cypress fixes bug 6207, get rid of the manual focus on search.  See https://github.com/cypress-io/cypress/issues/6207
  describe('Should be able to traverse the remaining idp section with arrow keys', () => {
    it('check if pressing down works as expected', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?showIdpBanner=1');
      cy.get(searchFieldSelector).focus();
      cy.pressArrowOnIdpList('down', searchFieldClass);
      cy.pressArrowOnIdpList('down', defaultIdpClass);
      cy.pressArrowOnIdpList('down', idpClass, '1');
      cy.pressArrowOnIdpList('down', idpClass, '2');
      cy.pressArrowOnIdpList('down', idpClass, '3');
      cy.pressArrowOnIdpList('down', idpClass, '4');
      cy.pressArrowOnIdpList('down', idpClass, '5');
      cy.pressArrowOnIdpList('down', searchFieldClass);
    });

    it('check if pressing up works as expected', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?showIdpBanner=1');
      cy.get(searchFieldSelector).focus();
      cy.pressArrowOnIdpList('up', searchFieldClass);
      cy.pressArrowOnIdpList('up', idpClass, '5');
      cy.pressArrowOnIdpList('up', idpClass, '4');
      cy.pressArrowOnIdpList('up', idpClass, '3');
      cy.pressArrowOnIdpList('up', idpClass, '2');
      cy.pressArrowOnIdpList('up', idpClass, '1');
      cy.pressArrowOnIdpList('up', defaultIdpClass);
      cy.pressArrowOnIdpList('up', searchFieldClass);
    });
  });

  describe.skip('Should show a fully functional no access section when a disabled account is selected', () => {
    it('Should show the no access section on selecting a disabled account', () => {
      cy.openUnconnectedIdp();
      cy.contains(noAccessTitle, 'Sorry, no access for this account');
    });

    it('Should not show the form elements yet', () => {
      cy.openUnconnectedIdp();
      cy.notBeVisible(noAccessFieldsetsSelector);
    });

    it('Should have a functioning cancel button', () => {
      cy.openUnconnectedIdp();
      cy.focusAndEnter(showFormSelector);
      cy.getAndEnter(cancelButtonSelector);
      cy.notBeVisible(noAccessTitle);
    });

    it('Should show the form fields after hitting request access', () => {
      cy.openUnconnectedIdp();
      cy.focusAndEnter(showFormSelector);
      cy.beVisible(noAccessFieldsetsSelector);
    });

    it('Should hide form fields after hitting cancel', () => {
      cy.openUnconnectedIdp();
      cy.getAndEnter(showFormSelector);
      cy.getAndEnter(cancelButtonSelector);
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/2"]');
      cy.notBeVisible(noAccessFieldsetsSelector);
    });

    it('Should be able to fill the request access form', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
    });

    it('Should be able to partially fill the request access form and get validation message', () => {
      cy.clearAllCookies();
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?displayUnconnectedIdpsWayf=true&unconnectedIdps=5');
      cy.openUnconnectedIdp();
      cy.focusAndEnter(showFormSelector);
      cy.fillNoAccessForm();
      cy.get(nameFieldSelector).clear({force:true});
      cy.focusAndEnter(submitRequestSelector);
      cy.doesNotHaveClass(nameErrorSelector, 'hidden');
      cy.notBeVisible('This is an invalid email address');
    });

    it('Email validation should be triggered', () => {
      cy.clearCookies();
      cy.openUnconnectedIdp();
      cy.focusAndEnter(showFormSelector);
      cy.fillNoAccessForm();
      cy.get(emailFieldSelector).clear({force:true});
      cy.getAndEnter(submitRequestSelector);
      cy.notBeVisible('Your name needs to be at least 2 characters long');
      cy.doesNotHaveClass(emailErrorSelector, 'hidden');
    });

    it.skip('Should show the success message', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.getAndEnter(submitRequestSelector);
      cy.wait(500);
      cy.notBeVisible(noAccessTitle);
      cy.beVisible(succesMessageSelector);
    });

    it('Should not show the success message when selecting a new disabled account', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.focusAndEnter(submitRequestSelector);
      cy.wait(500);
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/3');
      cy.notBeVisible(succesMessageSelector);
    });

    it('Should also not show the form fields after selecting a new disabled account', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.focusAndEnter(submitRequestSelector);
      cy.wait(500);
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/3');
      cy.notBeVisible(noAccessFieldsetsSelector);
    });
  });

  describe('Should have a working default Idp Banner', () => {
    it('Should have a default Idp banner visible', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?showIdpBanner=1');
      cy.beVisible(defaultIdpSelector);
    });

    it('Should scroll to the default Idp when clicking the banner link', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=10&defaultIdpEntityId=https://example.com/entityId/9&showIdpBanner=1');

      // click the banner link & check if it did what it should have
      cy.focusAndEnter(defaultIdpSelector);
      cy.get(defaultIdpItemSelector)
        .should('be.visible')
        .should('have.focus');
    });
  });

  describe('Should show a fully functional previous selection section', () => {
    it('Test if the previous section exists with the right title', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.beVisible(previousSelectionTitleSelector);
    });

    it('Test if the section contains the use account button', () => {
      cy.addOnePreviouslySelectedIdp();
        cy.beVisible(addAccountButtonSelector);
    });

    it('Test if it contains the right amount of idps', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.get(selectedIdpsSelector)
        .should('have.length', 1);
    });

    it('Test if selecting a previously selected idp works', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectFirstIdp(false, selectedIdpDataIndex1);
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.dev.openconext.local/?idp=https%3A//example.com/entityId/1');
      });
    });

    it('Test if the count was raised on the selected idp', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectFirstIdp(false, selectedIdpDataIndex1);
      cy.loadWayf();
      cy.get(selectedIdpDataIndex1).should('have.attr', 'data-count', '3');
    });

    it.skip('Test the edit button allows deleting an account', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectAccountButton();
      cy.selectFirstIdpAndReturn(false);
      cy.toggleEditButton(false);
      cy.hitDeleteButton(false);
      cy.notBeVisible(selectedIdpsSectionSelector);
    });

    it.skip('Test the add account button opens up the search & puts focus on the search field, then select the focused element.', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectAccountButton();
      cy.focused().should('have.class', searchFieldClass);
      cy.notBeVisible(selectedIdpsSectionSelector);
    });

    it.skip('Test deleting the last previously selected idp hides the section, shows the remaining idps, focuses on the searchbar & adds the deleted idp to the list', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.hitDeleteButton(false, firstSelectedIdpDeleteDisable);
      cy.focused().should('have.class', searchFieldClass);
      cy.notBeVisible(addAccountButtonSelector);
    });

    it.skip('Test the remaining list contains the deleted idp & is sorted alphabetically', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.hitDeleteButton(false, firstSelectedIdpDeleteDisable);
      cy.get('.wayf__remainingIdps .wayf__idp[data-entityid="https://example.com/entityId/1"]').should('exist');
      cy.get(firstRemainingIdp).should('have.attr', 'data-index', '1');
    });
  });
});
