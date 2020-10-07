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

  // todo after adding eduId feature flag this should be adjusted to take that into account
  // todo if html spec is changed, or cypress fixes bug 6207, get rid of the manual focus on search.  See https://github.com/cypress-io/cypress/issues/6207
  it('Should be able to traverse the remaining idp section with arrow keys', () => {
    // check if pressing down works as expected
    cy.get('.search__field').focus();
    cy.pressArrowOnIdpList('down', 'search__field');
    cy.pressArrowOnIdpList('down', 'wayf__eduIdLink');
    cy.pressArrowOnIdpList('down', 'wayf__idp', '1');
    cy.pressArrowOnIdpList('down', 'wayf__idp', '2');
    cy.pressArrowOnIdpList('down', 'wayf__idp', '3');
    cy.pressArrowOnIdpList('down', 'wayf__idp', '4');
    cy.pressArrowOnIdpList('down', 'wayf__idp', '5');
    cy.pressArrowOnIdpList('down', 'search__field');

    // check if pressing up works as expected
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');
    cy.get('.search__field').focus();
    cy.pressArrowOnIdpList('up', 'search__field');
    cy.pressArrowOnIdpList('up', 'wayf__idp', '5');
    cy.pressArrowOnIdpList('up', 'wayf__idp', '4');
    cy.pressArrowOnIdpList('up', 'wayf__idp', '3');
    cy.pressArrowOnIdpList('up', 'wayf__idp', '2');
    cy.pressArrowOnIdpList('up', 'wayf__idp', '1');
    cy.pressArrowOnIdpList('up', 'wayf__eduIdLink');
    cy.pressArrowOnIdpList('up', 'search__field');
  });

  // todo: test once no access has been implemented
  it.skip('Should show a fully functional no access section when a disabled account is selected', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf');

    // Test if the no access section shows up
    cy.contains('.noAccess__title', 'Sorry, no access for this account');

    // Test if the cancel button works
    cy.focusAndEnter('.cta__cancel');
    cy.get('.noAccess__title')
      .should('not.exist');

    // Test if the disabled account can be chosen
    cy.get('.idp__disabled')
      .eq(1)
      .type('{enter}');

    // Test if the request access button works
    cy.focusAndEnter('.cta__request');
    cy.contains('.noAccess__requestForm label', 'Your name');
  });

  // todo: test once previous selection has been implemented
  it.skip('Should show a fully functional previous selection section', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf'); // adjust so we have a previous section

    // Test if section exists with the right title
    cy.contains('.previousSelection__title', 'Your accounts');

    // Test if it contains the right amount of idps
    cy.get('.wayf__previousSelection .wayf__idp h3')
      .should('have.length', 2);

    // todo: add test for autofocus on first Idp, once bug 6207 for cypress is resolved.  See https://github.com/cypress-io/cypress/issues/6207

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
