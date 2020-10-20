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
        expect(loc.href).to.eq('https://engine.vm.openconext.org?idp=https://example.com/entityId/2/');
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

  describe.only('Should show a fully functional no access section when a disabled account is selected', () => {
    it('Should show the no access section on selecting a disabled account', () => {
      cy.openUnconnectedIdp();
      cy.contains('.noAccess__title', 'Sorry, no access for this account');
    });

    it('Should not show the form elements yet', () => {
      cy.openUnconnectedIdp();
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });

    it('Should have a functioning cancel button', () => {
      cy.openUnconnectedIdp();
      cy.focusAndEnter('.cta__showForm');
      cy.focusAndEnter('.cta__cancel');
      cy.get('.noAccess__title')
        .should('not.be.visible');
    });

    it('Should show the form fields after hitting request access', () => {
      cy.openUnconnectedIdp();
      cy.focusAndEnter('.cta__showForm');
      cy.get('.noAccess__requestForm fieldset')
        .should('be.visible');
    });

    it('Should hide form fields after hitting cancel', () => {
      cy.openUnconnectedIdp();
      cy.focusAndEnter('.cta__showForm');
      cy.focusAndEnter('.cta__cancel');
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/2"]');
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });

    it('Should be able to fill the request access form', () => {
      cy.fillNoAccessForm();
    });

    it('Should show the success message', () => {
      cy.fillNoAccessForm();
      cy.focusAndEnter('.cta__request');
      cy.wait(500);
      cy.get('.noAccess__title').should('not.be.visible');
      cy.get('.notification__success').should('be.visible');
    });

    it('Should not show the success message when selecting a new disabled account', () => {
      cy.fillNoAccessForm();
      cy.focusAndEnter('.cta__request');
      cy.wait(500);
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/3');
      cy.get('.notification__success').should('not.be.visible');
    });

    it('Should also not show the form fields after selecting a new disabled account', () => {
      cy.fillNoAccessForm();
      cy.focusAndEnter('.cta__request');
      cy.wait(500);
      cy.focusAndEnter('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/3');
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });
  });

  describe('Should have a working default Idp Banner', () => {
    it('Should have a default Idp banner visible', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?showIdpBanner=1');
      cy.get('.wayf__defaultIdpLink').should('be.visible');
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

  describe.only('Should show a fully functional previous selection section', () => {
    it('Loads the WAYF and populates the previous section with an idp', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.selectFirstIdpAndReturn(false);
    });

    it('Test if the previous section exists with the right title', () => {
      cy.get('.previousSelection__title')
        .should('be.visible');
    });

    it('Test if the section contains the use account button', () => {
      cy.contains('.previousSelection__addAccount', 'Use another account');
    });

    it('Test if it contains the right amount of idps', () => {
      cy.get('.wayf__previousSelection .wayf__idp')
        .should('have.length', 1);
    });

    it('Test if selecting a previously selected idp works', () => {
      cy.selectFirstIdp(false, '.wayf__previousSelection .wayf__idp[data-index="1"]');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/1');
      });
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    });

    it('Test if the count was raised on the selected idp', () => {
      cy.get('.wayf__previousSelection .wayf__idp[data-index="1"]').should('have.attr', 'data-count', '2');
    });

    it('Test the add account button opens up the search & puts focus on the search field, then select the focused element.', () => {
      cy.get('.previousSelection__addAccount')
        .type('{enter}');
      cy.focused().should('have.class', 'search__field');
      cy.get('.wayf__previousSelection').should('not.be.visible');
      cy.selectFirstIdpAndReturn(false);
    });

    it('Test the edit button allows deleting an account', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.toggleEditButton(false);
      cy.hitDeleteButton(false);
      cy.get('.wayf__previousSelection .wayf__idp h3')
        .should('have.length', 1);
    });

    it('Test deleting the last previously selected idp hides the section, shows the remaining idps and focuses on the searchbar', () => {
      cy.hitDeleteButton(false, '.wayf__previousSelection li:first-of-type .wayf__idp .idp__deleteDisable');
      cy.focused().should('have.class', 'search__field');
      cy.get('.previousSelection__addAccount').should('not.be.visible');
    });

    it('Test the last deleted idp is in the remaining list', () => {
      cy.get('.wayf__remainingIdps .wayf__idp[data-entityid="https://example.com/entityId/2"]').should('exist');
    });

    it('Test the remaining list is sorted alphabetically', () => {
      cy.get('.wayf__remainingIdps li:first-of-type .wayf__idp').should('have.attr', 'data-index', '1');
    });
  });
});
