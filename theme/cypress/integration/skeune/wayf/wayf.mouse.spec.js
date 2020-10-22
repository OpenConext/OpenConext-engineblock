/**
 * Tests for behaviour of the WAYF which depends on the mouse
 */
context('WAYF when using the mouse', () => {
  describe('Test logging in', () => {
    it('Should login when selecting an idp', () => {
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
      cy.get('.wayf__remainingIdps .wayf__idp')
        .eq(1)
        .click({force: true});
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/2');
      });
      cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    });
  });

  describe('Should show a fully functional no access section when a disabled account is selected', () => {
    it('Should show the no access section on selecting a disabled account', () => {
      cy.openUnconnectedIdp(false);
      cy.contains('.noAccess__title', 'Sorry, no access for this account');
    });

    it('Should not show the form elements yet', () => {
      cy.openUnconnectedIdp(false);
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });

    it('Should have a functioning cancel button', () => {
      cy.openUnconnectedIdp(false);
      cy.get('.cta__showForm').click({force: true});
      cy.get('.cta__cancel').click({force: true});
      cy.get('.noAccess__title')
        .should('not.be.visible');
    });

    it('Should show the form fields after hitting request access', () => {
      cy.openUnconnectedIdp(false);
      cy.get('.cta__showForm').click({force: true});
      cy.get('.noAccess__requestForm fieldset')
        .should('be.visible');
    });

    it('Should hide form fields after hitting cancel', () => {
      cy.openUnconnectedIdp(false);
      cy.get('.cta__showForm').click({force: true});
      cy.get('.cta__cancel').click({force: true});
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/2"]').click({force: true});
      cy.get('.noAccess__requestForm fieldset')
        .should('not.be.visible');
    });

    it('Should be able to fill the request access form', () => {
      cy.fillNoAccessForm(false);
    });

    it('Should show the success message', () => {
      cy.fillNoAccessForm(false);
      cy.get('.cta__request').click({force: true});
      cy.wait(500);
      cy.get('.noAccess__title').should('not.be.visible');
      cy.get('.notification__success').should('be.visible');
    });

    it('Should not show the success message when selecting a new disabled account', () => {
      cy.fillNoAccessForm(false);
      cy.get('.cta__request').click({force: true});
      cy.wait(500);
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/3').click({force: true});
      cy.get('.notification__success').should('not.be.visible');
    });

    it('Should also not show the form fields after selecting a new disabled account', () => {
      cy.fillNoAccessForm(false);
      cy.get('.cta__request').click({force: true});
      cy.wait(500);
      cy.get('.wayf__idp[data-entityid="https://unconnected.example.com/entityid/3').click({force: true});
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
      cy.get('.wayf__defaultIdpLink').click();
      cy.get('#defaultIdp')
        .should('be.visible')
        .should('have.focus');
    });
  });

  describe.only('Should show a fully functional previous selection section', () => {
    it.only('Test if the previous section exists with the right title', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.get('.previousSelection__title')
        .should('be.visible');
    });

      it('Test if the section contains the use account button', () => {
        cy.addOnePreviouslySelectedIdp(false);
        cy.get('.previousSelection__addAccount')
          .should('be.visible');
      });

    it('Test if it contains the right amount of idps', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.get('.wayf__previousSelection .wayf__idp')
        .should('have.length', 1);
    });

    it('Test if selecting a previously selected idp works', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectFirstIdp(true, '.wayf__previousSelection .wayf__idp[data-index="1"]');
      cy.location().should((loc) => {
        expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/2');
      });
    });

    it('Test if the count was raised on the selected idp', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectFirstIdp(true, '.wayf__previousSelection .wayf__idp[data-index="1"]');
      cy.loadWayf();
      cy.get('.wayf__previousSelection .wayf__idp[data-index="1"]').should('have.attr', 'data-count', '2');
    });

    it('Test the add account button opens up the search & puts focus on the search field, then select the focused element.', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectAccountButton(false);
      cy.focused().should('have.class', 'search__field');
      cy.get('.wayf__previousSelection').should('not.be.visible');
    });

    it('Test the edit button allows deleting an account', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.selectAccountButton(false);
      cy.selectFirstIdpAndReturn(false);
      cy.toggleEditButton(false);
      cy.hitDeleteButton(false);
      cy.get('.wayf__previousSelection .wayf__idp h3')
        .should('have.length', 1);
    });

    it('Test deleting the last previously selected idp hides the section, shows the remaining idps, focuses on the searchbar & adds the deleted idp to the list', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.hitDeleteButton(true, '.wayf__previousSelection li:first-of-type .wayf__idp .idp__deleteDisable');
      cy.focused().should('have.class', 'search__field');
      cy.get('.previousSelection__addAccount').should('not.be.visible');
    });

    it('Test the remaining list contains the deleted idp & is sorted alphabetically', () => {
      cy.addOnePreviouslySelectedIdp(false);
      cy.hitDeleteButton(true, '.wayf__previousSelection li:first-of-type .wayf__idp .idp__deleteDisable');
      cy.get('.wayf__remainingIdps .wayf__idp[data-entityid="https://example.com/entityId/1"]').should('exist');
      cy.get('.wayf__remainingIdps li:first-of-type .wayf__idp').should('have.attr', 'data-index', '1');
    });
  });
});
