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

Cypress.Commands.add('buildTheme', (themeName) => {
  cy.exec(`EB_THEME=${themeName} npm run buildtheme`);
});

Cypress.Commands.add('hideDebugBar', () => {
  cy.get('.sf-toolbar').invoke('attr', 'style', 'display: none');
});

Cypress.Commands.add('focusAndEnter', (selector) => {
  cy.get(selector).focus().type('{enter}');
});

Cypress.Commands.add('pressArrowOnIdpList', (direction, className, index) => {
  if (index && className) {
    cy.focused().should('have.class', className).should('have.attr', 'data-index', index).type(`{${direction}arrow}`);
    return;
  }

  cy.focused().should('have.class', className).type(`{${direction}arrow}`);
});
