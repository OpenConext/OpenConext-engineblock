/**
 * Tests for behaviour of the WAYF which depends on pressing enter.
 */
context('WAYF when using the keyboard', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
  });

  it('Should login when selecting an idp', () => {
    cy.get('.wayf__remainingIdps .wayf__idp')
      .eq(1)
      .focus()
      .type('{enter}');
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
      .type('{enter}');

    // Test if the no access section shows up
    cy.contains('.noAccess__title', 'Sorry, no access for this account');

    // Test if the cancel button works
    cy.focusAndEnter('.cta__cancel');
    cy.get('.noAccess__title')
      .should('not.exist');

    // Test if the request access button works
    cy.focusAndEnter('.cta__request');
    cy.contains('.noAccess__requestForm label', 'Your name');
  });

  // todo: test once previous selection has been implemented
  it.skip('Should show a fully functional previous selection section', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');

    // Test if section exists with the right title
    cy.contains('.previousSelection__title', 'Your accounts');

    // Test if it contains the right amount of idps
    cy.get('.wayf__previousSelection .wayf__idp h3')
      .should('have.length', 2);

    // Test if the section contains the add account button
    cy.contains('.previousSelection__addAccount', 'Add another account');

    // Test the addacount button opens up the search
    cy.focusAndEnter('.previousSelection__addAccount');
    cy.get('#wayf__search').type('IdP 4');
    cy.focusAndEnter('.search__submit');

    // Test adding another account works
    cy.get('.wayf__remainingIdps .wayf__idp')
      .eq(1)
      .type('{enter}');
    cy.location().should((loc) => {
      expect(loc.href).to.eq('https://engine.vm.openconext.org/');
    });
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    cy.get('.wayf__previousSelection .wayf__idp h3')
      .should('have.length', 3);

    // Test the edit button allows deleting an account
    cy.focusAndEnter('.previousSelection__edit');
    cy.focusAndEnter('.idp__delete');
    cy.get('.wayf__previousSelection .wayf__idp h3')
      .should('have.length', 2);
  });
});
