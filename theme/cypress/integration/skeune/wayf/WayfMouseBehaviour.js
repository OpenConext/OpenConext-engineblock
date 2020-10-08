/**
 * Tests for behaviour of the WAYF which depends on clicking
 */
context('WAYF when using the mouse', () => {
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

  // todo: test once no access has been implemented
  it.skip('Should show a fully functional no access section when a disabled account is selected', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');

    // Test if the disabled account can be chosen
    cy.get('.idp__disabled')
      .eq(1)
      .click({ force: true });

    // Test if the no access section shows up
    cy.contains('.noAccess__title', 'Sorry, no access for this account');

    // Test if the cancel button works
    cy.get('.cta__cancel').click({ force: true });
    cy.get('.noAccess__title')
      .should('not.exist');

    // Test if the request access button works
    cy.get('.cta__request').click({ force: true });
    cy.contains('.noAccess__requestForm label', 'Your name');
  });

  describe('Should show a fully functional previous selection section', () => {
      it('Loads the WAYF', () => {
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      });

      it('Should populate the previous section with an idp', () => {
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

      it('Test the add account button opens up the search & puts focus on the search field, then select the focused element.', () => {
        cy.get('.previousSelection__addAccount')
          .click({ force: true });
        cy.focused().should('have.class', 'search__field');
        cy.selectFirstIdpAndReturn();
        cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      });

      it('Test the edit button allows deleting an account', () => {
        cy.toggleEditButton();
        cy.hitDeleteButton();
        cy.get('.wayf__previousSelection .wayf__idp h3')
          .should('have.length', 1);
      });

      it('Test deleting the last previously selected idp hides the section, shows the remaining idps and focuses on the searchbar', () => {
        cy.hitDeleteButton();
        cy.focused().should('have.class', 'search__field');
        cy.get('.previousSelection__addAccount').should('not.be.visible');
      });

      it('Test the last deleted idp is at the top of the list', () => {
        cy.get('.wayf__remainingIdps .wayf__idp[data-index="1"]').should('have.attr', 'data-entityid', 'https://example.com/entityId/2');
      });
  });
});
