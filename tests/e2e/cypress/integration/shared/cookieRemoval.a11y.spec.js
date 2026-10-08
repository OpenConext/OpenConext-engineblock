/**
 * This doesn't run in CI, which is why it's skipped.  You can run it locally by setting either the wayf.remember_choice flag or the feature_enable_wayf_remember_choice_per_idp flag to true in parameters.yaml.
 */
context.skip('Cookie removal page verify a11y', () => {
  beforeEach(() => {
    cy.visit('https://engine.dev.openconext.local/authentication/idp/remove-cookies');
  });

  it('contains no a11y problems on load', () => {
    cy.injectAxe();
    cy.checkA11y();
  });

  it('contains no html errors', () => {
    cy.htmlvalidate();
  });
});
