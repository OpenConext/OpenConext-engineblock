import {terminalLog} from '../../functions/terminalLog';

context('Error pages verify a11y', () => {
  it('Unknown error page contains no a11y problems on load', () => {
    cy.visitAndRemoveDebugToolbar('https://engine.vm.openconext.org/feedback/unknown-error', false);
    cy.injectAxe();
    cy.checkA11y(null, null, terminalLog);
  });

  it('Unknown error page contains no html errors', () => {
    cy.visitAndRemoveDebugToolbar('https://engine.vm.openconext.org/feedback/unknown-error', false);
    cy.htmlvalidate();
  });

  it('404 page contains no a11y problems on load', () => {
    cy.visitAndRemoveDebugToolbar('https://engine.vm.openconext.org/functional-testing/a;dkfj;ad', false);
    cy.injectAxe();
    cy.checkA11y(null, null, terminalLog);
  });

  it('404 page contains no html errors', () => {
    cy.visitAndRemoveDebugToolbar('https://engine.vm.openconext.org/functional-testing/a;dkfj;ad',false);
    cy.htmlvalidate();
  });
});
