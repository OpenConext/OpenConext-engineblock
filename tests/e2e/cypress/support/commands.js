Cypress.Commands.add('countIdps', (expectedCount, idpSelector = '#idp-picker h3') => {
  cy.get(idpSelector)
  .should('have.length', expectedCount);
});

Cypress.Commands.add('hasClass', (selector, klasse) => {
  cy.get(selector).should('have.class', klasse);
});

Cypress.Commands.add('doesNotHaveClass', (selector, klasse) => {
  cy.get(selector).should('not.have.class', klasse);
});

Cypress.Commands.add('beVisible', (selector) => {
  cy.get(selector).should('not.have.css', 'display', 'none');
});

Cypress.Commands.add('notBeVisible', (selector) => {
  cy.get(selector).should('not.be.visible');
});

Cypress.Commands.add('notExistOrVisible', (selector, isNotExist) => {
  cy.get(selector).should(isNotExist ? 'not.exist' : 'not.be.visible');
});

Cypress.Commands.add('onPage', (expectedText) => {
  cy.get('body').contains(expectedText);
});

Cypress.Commands.add('notOnPage', (notToBeExpectedText) => {
  cy.get('body').contains(notToBeExpectedText).should('not.exist');
});

// Cypress.Commands.add('matchImageSnapshots', (viewport, pageDetails) => {
//   cy.visit(pageDetails.url, {failOnStatusCode: false})
//     .viewport(viewport.width, viewport.height)
//     .then(() => {
//       cy.get('body').toMatchImageSnapshot(pageDetails.title + '-' + viewport.width + 'x' + viewport.height);
//     });
// });

Cypress.Commands.add('buildTheme', (themeName) => {
  cy.exec(`EB_THEME=${themeName} yarn buildtheme`);
});

Cypress.Commands.add('hideDebugBar', () => {
  cy.get('.sf-toolbar').invoke('attr', 'style', 'display: none');
});

Cypress.Commands.add('focusAndEnter', (selector) => {
  cy.get(selector).focus().type('{enter}', {force: true});
});

Cypress.Commands.add('getAndEnter', (selector) => {
  cy.get(selector).type('{enter}', {force: true});
});

Cypress.Commands.add('pressArrowOnIdpList', (direction, className, index) => {
  if (index && className) {
    cy.focused().should('have.class', className);

    if (index) {
      cy.focused().parent().should('have.attr', 'data-index', index).children().first().type(`{${direction}arrow}`);
      return;
    }

    cy.focused().type(`{${direction}arrow}`);
    return;
  }

  cy.focused().should('have.class', className).type(`{${direction}arrow}`);
});

Cypress.Commands.add('selectFirstIdp', (click = true, firstElementSelector = '.wayf__remainingIdps li[data-index="1"] > div') => {
  if (click) {
    cy.get(firstElementSelector).click({force: true});
    return;
  }

  cy.get(firstElementSelector).type('{enter}', {force: true});
});

Cypress.Commands.add('selectFirstIdpAndReturn', (click = true, url = 'https://engine.dev.openconext.local/functional-testing/wayf') => {
  cy.selectFirstIdp(click).then(() => {
    cy.visit(url);
  });
});

Cypress.Commands.add('toggleEditButton', (click = true, buttonSelector = '.previousSelection__toggleLabel') => {
  if (click) {
    cy.get(buttonSelector).click({force: true });
    return;
  }

  cy.get(buttonSelector).type('{enter}', {force: true});
});

Cypress.Commands.add('hitDeleteButton', (click = true, deleteSelector = '.wayf__previousSelection .wayf__idp[data-index="1"] .idp__deleteDisable') => {
  if (click) {
    cy.get(deleteSelector).focus().click({ force: true });
    return;
  }

  cy.get(deleteSelector).focus().type('{enter}', {force: true});
});

Cypress.Commands.add('openUnconnectedIdp', (keyboard = true, url = 'https://engine.dev.openconext.local/functional-testing/wayf?displayUnconnectedIdpsWayf=true&unconnectedIdps=5', idpSelector = '.wayf__idp[data-entityid="https://unconnected.example.com/entityId/4"]') => {
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
    cy.getAndEnter(showFormSelector);
  } else {
    cy.get(showFormSelector).click({force:true});
  }

  cy.get('#name').focus().type('Joske', {force: true});
  cy.get('#email').focus().type('joske.vermeulen@thuis.be', {force: true});
  cy.get('#motivation').focus().type('tis toapuh dattem tuis is', {force: true});
});

Cypress.Commands.add('loadWayf', (url = 'https://engine.dev.openconext.local/functional-testing/wayf') => {
  cy.visit(url);
});

Cypress.Commands.add('addOnePreviouslySelectedIdp', (keyboard = true, url = 'https://engine.dev.openconext.local/functional-testing/wayf') => {
  cy.loadWayf(url).then(() => {
    cy.selectFirstIdpAndReturn(!keyboard, url);
  });
});

Cypress.Commands.add('selectAccountButton', (keyboard = true, selector = '.previousSelection__addAccount') => {
  if (keyboard) {
    cy.get(selector)
      .focus()
      .type('{enter}', {force: true});
    return;
  }

  cy.get(selector).click({force: true});
});
