/**
 * Tests for the general behaviour of the error page.
 */
context('Error pages on skeune theme', () => {
  it('Test if the error page loads with the unknown error notice & all components', () => {
    cy.visit('https://engine.vm.openconext.org/feedback/unknown-error', {failOnStatusCode: false
    });
    cy.beVisible('.error-title__heading');
    cy.beVisible('.error-title__error-message');
    cy.beVisible('.comp-language.error');
    cy.contains('Error - An error occurred');
    cy.contains('we don\'t know exactly why');
    cy.contains('OpenConext Wiki');
    cy.contains('Helpdesk');
  });

  it('Test if a faulty url loads the 404 page with all components', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/a;dkfj;ad', {failOnStatusCode: false
    });
    cy.beVisible('.error-title__heading');
    cy.beVisible('.error-title__error-message');
    cy.beVisible('.comp-language.error');
    cy.contains('404 - Page not found');
    cy.contains('This page has not been found');
    cy.contains('Helpdesk');
  });
});
