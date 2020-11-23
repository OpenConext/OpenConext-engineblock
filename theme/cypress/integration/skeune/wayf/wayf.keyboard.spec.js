/**
 * Tests for behaviour of the WAYF which depends on the keyboard.
 */
context('WAYF when using the keyboard', () => {
  describe('Test logging in', () => {
    it('Should login when selecting an idp', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('.wayf__remainingIdps .wayf__idp')
        .eq(1)
        .focus()
        .type('{enter}');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/2');
      });
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    });

    it('Should login to first IdP when hitting enter', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('#wayf_search')
        .type('{enter}');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/1');
      });
    });

    it('Should login to topmost  IdP when hitting enter', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('#wayf_search')
        .type('2')
        .type('{enter}');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/2');
      });
    });


  });

  // todo if html spec is changed, or cypress fixes bug 6207, get rid of the manual focus on search.  See https://github.com/cypress-io/cypress/issues/6207
  describe('Should be able to traverse the remaining idp section with arrow keys', () => {
    it('check if pressing down works as expected', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?showIdpBanner=1');
      cy.get('.search__field').focus();
      cy.pressArrowOnIdpList('down', 'search__field');
      cy.pressArrowOnIdpList('down', 'wayf__defaultIdpLink');
      cy.pressArrowOnIdpList('down', 'wayf__idp', '1');
      cy.pressArrowOnIdpList('down', 'wayf__idp', '2');
      cy.pressArrowOnIdpList('down', 'wayf__idp', '3');
      cy.pressArrowOnIdpList('down', 'wayf__idp', '4');
      cy.pressArrowOnIdpList('down', 'wayf__idp', '5');
      cy.pressArrowOnIdpList('down', 'search__field');
    });

    it('check if pressing up works as expected', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?showIdpBanner=1');
      cy.get('.search__field').focus();
      cy.pressArrowOnIdpList('up', 'search__field');
      cy.pressArrowOnIdpList('up', 'wayf__idp', '5');
      cy.pressArrowOnIdpList('up', 'wayf__idp', '4');
      cy.pressArrowOnIdpList('up', 'wayf__idp', '3');
      cy.pressArrowOnIdpList('up', 'wayf__idp', '2');
      cy.pressArrowOnIdpList('up', 'wayf__idp', '1');
      cy.pressArrowOnIdpList('up', 'wayf__defaultIdpLink');
      cy.pressArrowOnIdpList('up', 'search__field');
    });
  });

  describe('Should show a fully functional no access section when a disabled account is selected', () => {
    it('Should show the no access section on selecting a disabled account', () => {
      cy.openUnconnectedIdp();
      cy.contains('.noAccess__title', 'Sorry, no access for this account');
    });

    it('Should not show the form elements yet', () => {
      cy.openUnconnectedIdp();
      cy.notBeVisible('.noAccess__requestForm fieldset');
    });

    it('Should have a functioning cancel button', () => {
      cy.openUnconnectedIdp();
      cy.focusAndEnter('.cta__showForm');
      cy.getAndEnter('.cta__cancel');
      cy.notBeVisible('.noAccess__title');
    });

    it('Should show the form fields after hitting request access', () => {
      cy.openUnconnectedIdp();
      cy.getAndEnter('.cta__showForm');
      cy.beVisible('.noAccess__requestForm fieldset');
    });

    it('Should hide form fields after hitting cancel', () => {
      cy.openUnconnectedIdp();
      cy.getAndEnter('.cta__showForm');
      cy.getAndEnter('.cta__cancel');
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/2"]');
      cy.notBeVisible('.noAccess__requestForm fieldset');
    });

    it('Should be able to fill the request access form', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
    });

    it('Should be able to partially fill the request access form and get validation message', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.get('#name').clear();
      cy.getAndEnter('.cta__request');
      cy.get('#name + form_error')
        .should('not.have.class', 'hidden');
      cy.notBeVisible('This is an invalid email address');
    });

    it('Email validation should be triggered', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.get('#email').clear();
      cy.getAndEnter('.cta__request');
      cy.notBeVisible('Your name needs to be at least 2 characters long');
      cy.doesNotHaveClass('#email + .form__error', 'hidden');
    });

    it.skip('Should show the success message', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.getAndEnter('.cta__request');
      cy.wait(500);
      cy.notBeVisible('.noAccess__title');
      cy.beVisible('.notification__success');
    });

    it('Should not show the success message when selecting a new disabled account', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.focusAndEnter('.cta__request');
      cy.wait(500);
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/3');
      cy.notBeVisible('.notification__success');
    });

    it('Should also not show the form fields after selecting a new disabled account', () => {
      cy.openUnconnectedIdp();
      cy.fillNoAccessForm();
      cy.focusAndEnter('.cta__request');
      cy.wait(500);
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityId/3');
      cy.notBeVisible('.noAccess__requestForm fieldset');
    });
  });

  describe('Should have a working default Idp Banner', () => {
    it('Should have a default Idp banner visible', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?showIdpBanner=1');
      cy.beVisible('.wayf__defaultIdpLink');
    });

    it('Should scroll to the default Idp when clicking the banner link', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10&defaultIdpEntityId=https://example.com/entityId/9&showIdpBanner=1');

      // click the banner link & check if it did what it should have
      cy.focusAndEnter('.wayf__defaultIdpLink');
      cy.get('#defaultIdp')
        .should('be.visible')
        .should('have.focus');
    });
  });

  describe('Should show a fully functional previous selection section', () => {
    it('Test if the previous section exists with the right title', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.beVisible('.previousSelection__title');
    });

    it('Test if the section contains the use account button', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.beVisible('.previousSelection__addAccount');
    });

    it('Test if it contains the right amount of idps', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.get('.wayf__previousSelection .wayf__idp')
        .should('have.length', 1);
    });

    it('Test if selecting a previously selected idp works', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectFirstIdp(false, '.wayf__previousSelection .wayf__idp[data-index="1"]');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/1');
      });
    });

    it('Test if the count was raised on the selected idp', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectFirstIdp(false, '.wayf__previousSelection .wayf__idp[data-index="1"]');
      cy.loadWayf();
      cy.get('.wayf__previousSelection .wayf__idp[data-index="1"]').should('have.attr', 'data-count', '2');
    });

    it('Test the edit button allows deleting an account', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectAccountButton();
      cy.selectFirstIdpAndReturn(false);
      cy.toggleEditButton(false);
      cy.hitDeleteButton(false);
      cy.notBeVisible('.wayf__previousSelection');
    });

    it('Test the add account button opens up the search & puts focus on the search field, then select the focused element.', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.selectAccountButton();
      cy.focused().should('have.class', 'search__field');
      cy.notBeVisible('.wayf__previousSelection');
    });

    it('Test deleting the last previously selected idp hides the section, shows the remaining idps, focuses on the searchbar & adds the deleted idp to the list', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.hitDeleteButton(false, '.wayf__previousSelection li:first-of-type .wayf__idp .idp__deleteDisable');
      cy.focused().should('have.class', 'search__field');
      cy.notBeVisible('.previousSelection__addAccount');
    });

    it('Test the remaining list contains the deleted idp & is sorted alphabetically', () => {
      cy.addOnePreviouslySelectedIdp();
      cy.hitDeleteButton(false, '.wayf__previousSelection li:first-of-type .wayf__idp .idp__deleteDisable');
      cy.get('.wayf__remainingIdps .wayf__idp[data-entityid="https://example.com/entityId/1"]').should('exist');
      cy.get('.wayf__remainingIdps li:first-of-type .wayf__idp').should('have.attr', 'data-index', '1');
    });
  });
});
