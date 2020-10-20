Cypress.Commands.add('countIdps', (expectedCount, idpSelector = '#idp-picker h3') => {
  cy.get(idpSelector)
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

Cypress.Commands.add('selectFirstIdp', (click = true, firstElementSelector = '.wayf__remainingIdps .wayf__idp[data-index="1"]') => {
  if (click) {
    cy.get(firstElementSelector).click({force: true});
    return;
  }

  cy.get(firstElementSelector).type('{enter}');
});

Cypress.Commands.add('selectFirstIdpAndReturn', (click = true, url = 'https://engine.vm.openconext.org/functional-testing/wayf') => {
  cy.selectFirstIdp(click);
  cy.visit(url);
});

Cypress.Commands.add('toggleEditButton', (click = true, buttonSelector = '.previousSelection__edit') => {
  if (click) {
    cy.get(buttonSelector).click({ force: true });
    return;
  }

  cy.get(buttonSelector).type('{enter}');
});

Cypress.Commands.add('hitDeleteButton', (click = true, deleteSelector = '.wayf__previousSelection .wayf__idp[data-index="1"] .idp__deleteDisable') => {
  if (click) {
    cy.get(deleteSelector).focus().click({ force: true });
    return;
  }

  cy.get(deleteSelector).focus().type('{enter}');
});

Cypress.Commands.add('openUnconnectedIdp', (keyboard = true, url = 'https://engine.vm.openconext.org/functional-testing/wayf?displayUnconnectedIdpsWayf=true&unconnectedIdps=5', idpSelector = '.wayf__idp[data-entityid="https://unconnected.example.com/entityid/4"]') => {
  cy.visit(url);

  if (keyboard) {
    cy.focusAndEnter(idpSelector);
  } else {
    cy.get(idpSelector).click({force: true});
  }
});

Cypress.Commands.add('fillNoAccessForm', (keyboard = true, showFormSelector = '.cta__showForm') => {
  cy.openUnconnectedIdp(keyboard);

  if (keyboard) {
    cy.focusAndEnter(showFormSelector);
  } else {
    cy.get(showFormSelector).click({force:true});
  }

  cy.get('#name').type('Joske');
  cy.get('#email').type('joske.vermeulen@thuis.be');
  cy.get('#motivation').focus().type('tis toapuh dattem tuis is');
});
