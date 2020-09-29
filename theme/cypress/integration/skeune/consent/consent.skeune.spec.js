context('Consent on Skeune theme', () => {

  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/consent');
  });

  it.skip('gives openconext information', () => {
    cy.get('label[for="consent_disclaimer_about"] + section h3')
      .should('be.hidden');

    cy.contains('label', 'OpenConext')
      .click({force: true})
      .next()
      .contains('h3', 'Logging in through OpenConext');
  });

  it.skip('shows information on how to report incorrect data', () => {
    cy.get('label[for="cta_consent_nok"] + section h3')
      .should('be.hidden');

    cy.contains('label', 'Something incorrect?')
      .click()
      .next()
      .contains('h3', 'Is the data shown incorrect?');
  });

  it.skip('can show additional attributes', () => {
    cy.get('ul.consent__attributes--nested')
      .should('be.hidden');

    cy.contains('label', 'Show more information')
      .click();

    cy.contains('label', 'Show less information');
    cy.get('ul.consent__attributes--nested')
      .should('be.visible');
  });

  it.skip('shows the correct amount of attributes on load', () => {
    cy.get('ul.consent__attributes > li')
      .should('have.length', '7');
  });

  it.skip('gives explanation about the unique identifier', () => {
    cy.get('label[for="consent_disclaimer_number"] + section a[href="https://example.org"]')
      .should('be.hidden');

    cy.contains('label', 'a number that uniquely identifies you for this service.')
      .click({force: true})
      .next()
      .contains('a', 'Read more');
  });

  it.skip('can decline consent', () => {
    cy.get('label[for="cta_consent_nok"] + section h3')
      .should('be.hidden');

    cy.contains('label', 'No, I do not agree')
      .click({force: true})
      .next()
      .contains('h3', 'You don\'t want to share your data with the service');
  });
});
