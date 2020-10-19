/**
 * Tests for behaviour of the WAYF which depends on clicking
 */
context('WAYF when using the mouse', () => {
  describe('Test logging in', () => {
    it('Should login when selecting an idp', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('.wayf__remainingIdps .wayf__idp')
        .eq(1)
        .click({force: true});
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/');
      });
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    });
  });

  describe('Should show a fully functional no access section when a disabled account is selected', () => {
    it('Should show the no access section on selecting a disabled account', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?displayUnconnectedIdpsWayf=true&unconnectedIdps=5');
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/1"]').click({force: true});
      cy.contains('.noAccess__title', 'Sorry, no access for this account');
    });

    it('Should not show the form elements yet', () => {
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });

    it('Should have a functioning cancel button', () => {
      cy.get('.cta__cancel').click({force: true});
      cy.get('.noAccess__title')
        .should('not.be.visible');
    });

    it('Should show the form fields after clicking request access', () => {
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/4"]').click({force: true});
      cy.get('.cta__showForm').click({force: true});
      cy.get('.noAccess__requestForm fieldset')
        .should('be.visible');
    });

    it('Should hide form fields after clicking cancel', () => {
      cy.get('.cta__cancel').click({force: true});
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/2"]').click({force: true});
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });

    it('Should be able to fill & submit the request access form', () => {
      cy.get('.cta__showForm').click({force: true});
      cy.get('#name').type('Joske');
      cy.get('#email').type('joske.vermeulen@thuis.be');
      cy.get('#motivation').focus().type('tis toapuh dattem tuis is');
      cy.get('.cta__request').click({force: true});
      cy.wait(250);
      cy.get('.noAccess__title').should('not.be.visible');
    });

    it('Should show the success message', () => {
      cy.get('.notification__success').should('be.visible');
    });

    it('Should not show the success message when selecting a new disabled account', () => {
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/3').click({force: true});
      cy.get('.notification__success').should('not.be.visible');
    });

    it('Should also not show the form fields after selecting a new disabled account', () => {
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });
  });

  describe('Should show a fully functional previous selection section', () => {
      it('Loads the WAYF and populates the previous section with an idp', () => {
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
        cy.selectFirstIdpAndReturn();
      });

      it('Test if the previous section exists with the right title', () => {
        cy.contains('.previousSelection__title', 'Your accounts');
      });

      it('Test if the section contains the add account button', () => {
        cy.contains('.previousSelection__addAccount', 'Add another account');
      });

      it('Test if it contains the right amount of idps', () => {
        cy.get('.wayf__previousSelection .wayf__idp h3')
          .should('have.length', 1);
      });

      it('Test if selecting a previously selected idp works', () => {
        cy.selectFirstIdp(true, '.wayf__previousSelection .wayf__idp[data-index="1"]');
        cy.location().should((loc) => {
          expect(loc.href).to.eq('https://engine.vm.openconext.org/');
        });
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      });

      it('Test if the count was raised on the selected idp', () => {
        cy.get('.wayf__previousSelection .wayf__idp[data-index="1"]').should('have.attr', 'data-count', '2');
      });

      it('Test the add account button opens up the search, hides the previous section & puts focus on the search field, then select the focused element.', () => {
        cy.get('.previousSelection__addAccount')
          .click({ force: true });
        cy.focused().should('have.class', 'search__field');
        cy.get('.wayf__previousSelection').should('not.be.visible');
        cy.selectFirstIdpAndReturn();
      });

      it('Test the edit button allows deleting an account', () => {
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
        cy.toggleEditButton();
        cy.hitDeleteButton();
        cy.get('.wayf__previousSelection .wayf__idp h3')
          .should('have.length', 1);
      });

      it('Test deleting the last previously selected idp hides the section, shows the remaining idps and focuses on the searchbar', () => {
        cy.hitDeleteButton(true, '.wayf__previousSelection li:first-of-type .wayf__idp .idp__deleteDisable');
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
