context('Error pages verify a11y', () => {
  it('Unknown error page contains no a11y problems on load', () => {
    cy.visit('https://engine.vm.openconext.org/feedback/unknown-error', {failOnStatusCode: false
    });
    cy.injectAxe();
    cy.checkA11y();
  });

  it('Unknown error page contains no html errors', () => {
    cy.visit('https://engine.vm.openconext.org/feedback/unknown-error', {failOnStatusCode: false
    });
    cy.htmlvalidate();
  });

  it('404 page contains no a11y problems on load', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/a;dkfj;ad', {failOnStatusCode: false
    });
    cy.injectAxe();
    cy.checkA11y();
  });

  it('404 page contains no html errors', () => {
    cy.visit('https://engine.vm.openconext.org/functional-testing/a;dkfj;ad', {failOnStatusCode: false
    });
    cy.htmlvalidate();
  });
});
