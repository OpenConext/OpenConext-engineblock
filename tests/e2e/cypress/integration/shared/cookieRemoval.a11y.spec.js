/**
 * This doesn't run in CI, which is why it's skipped.  You can run it locally by setting the wayf.remember_choice flag to true in parameters.yml.
 */
context.skip('Cookie removal page verify a11y', () => {
  beforeEach(() => {
    cy.visit('https://engine.vm.openconext.org/authentication/idp/remove-cookies');
  });

  it('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y();
  });

  it('contains no html errors', () => {
    cy.htmlvalidate();
  });
});
