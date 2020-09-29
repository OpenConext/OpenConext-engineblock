Cypress.Commands.add('countIdps', (expectedCount) => {
  cy.get('#idp-picker h3')
  .should('have.length', expectedCount);
});

Cypress.Commands.add('onPage', (expectedText) => {
  cy.get('body').contains(expectedText);
});

Cypress.Commands.add('notOnPage', (notToBeExpectedText) => {
  cy.get('body').contains(notToBeExpectedText).should('not.exist');
});

Cypress.Commands.add('matchImageSnapshots', (viewport, pageDetails) => {
  cy.visit(pageDetails.url, {failOnStatusCode: false})
    .viewport(viewport.width, viewport.height)
    .then(() => {
      cy.get('body').toMatchImageSnapshot(pageDetails.title + '-' + viewport.width + 'x' + viewport.height);
    });
});
