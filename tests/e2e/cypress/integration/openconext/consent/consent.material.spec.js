context('Consent on Material theme', () => {

  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  it('gives openconext information', () => {
    cy.get('a.help[data-slidein="about"]')
      .click()
      .get('section h1')
      .should('be.visible')
      .and('contain.text', 'Logging in through OpenConext');

    cy.get('div.about a.close')
      .click();
  });

  it('shows information on how to report incorrect data', () => {
    cy.get('a.small')
      .click()
      .get('section h1')
      .should('be.visible')
      .and('contain.text', 'Is the data shown incorrect?');

    cy.get('div.correction-idp a.close')
      .click();
  });

  it('can show additional attributes', () => {
    cy.get('span.show-more')
      .click()
      .get('td[data-identifier="urn:mace:dir:attribute-def:isMemberOf"]')
      .should('be.visible')
      .and('contain.text', 'Member of organization');
  });

  it('can decline consent', () => {
    cy.get('div.slidein.reject')
      .should('be.hidden');

    cy.get('a#decline-terms')
      .click()
      .get('section h1')
      .should('be.visible')
      .and('contain.text', 'You don\'t want to share your data with the service');

    cy.get('div.slidein.reject')
      .should('be.visible');
  });

});
