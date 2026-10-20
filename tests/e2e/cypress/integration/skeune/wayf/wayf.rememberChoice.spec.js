import {
  idpSelector,
  rememberChoiceId,
  rememberChoicePerIdpClass,
  rememberChoiceTooltipToggleSelector,
  rememberChoiceTooltipValueSelector
} from '../../../../../../theme/base/javascripts/selectors';

/**
 * Kept apart from wayf.general.spec.js so these tests run independently of any it.only in that file.
 */
context('WAYF per-SP remember my choice', () => {
  describe('Should show the per-SP remember my choice option with a tooltip', () => {
    it('Renders the per-SP checkbox with a tooltip toggle', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get(`.${rememberChoicePerIdpClass}`).should('exist');
      cy.get(rememberChoiceTooltipToggleSelector).should('exist');
    });

    it('Renders the per-SP checkbox when only rememberChoicePerIdp is passed', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoicePerIdp=true');
      cy.get(`.${rememberChoicePerIdpClass}`).should('exist');
      cy.onPage('Remember my choice');
    });

    it('Hides the tooltip content until the toggle is activated', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get(rememberChoiceTooltipValueSelector).should('not.be.visible');
      cy.get(rememberChoiceTooltipToggleSelector).click({force: true});
      cy.get(rememberChoiceTooltipValueSelector).should('be.visible');
      cy.get(rememberChoiceTooltipToggleSelector).click({force: true});
      cy.get(rememberChoiceTooltipValueSelector).should('not.be.visible');
    });

    it('Toggles aria-hidden and aria-expanded on the tooltip when the label is clicked', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get(rememberChoiceTooltipValueSelector).should('have.attr', 'aria-hidden', 'true');
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-expanded', 'false');
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-pressed', 'false');
      cy.get('.wayf__rememberChoice label.tooltip').click();
      cy.get(rememberChoiceTooltipValueSelector).should('not.have.attr', 'aria-hidden');
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-expanded', 'true');
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-pressed', 'true');
      cy.get('.wayf__rememberChoice label.tooltip').click();
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-expanded', 'false');
      cy.get(rememberChoiceTooltipToggleSelector).should('have.attr', 'aria-pressed', 'false');
    });

    it('Opens the tooltip when the label is focused and Enter is pressed', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.get('.wayf__rememberChoice label.tooltip').focus().type('{enter}');
      cy.get(rememberChoiceTooltipValueSelector).should('not.have.attr', 'aria-hidden');
    });

    it('Does not show the tooltip toggle for the global (non per-SP) variant', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true&rememberChoicePerIdp=false');
      cy.get(`.${rememberChoicePerIdpClass}`).should('not.exist');
      cy.get(rememberChoiceTooltipToggleSelector).should('not.exist');
      cy.onPage('Remember my choice');
    });

    it('Sets the hidden rememberChoice field to 1 on the submitted IdP form when the checkbox is checked', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=1&addDiscoveries=0&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.window().then((win) => {
        cy.stub(win.HTMLFormElement.prototype, 'submit').as('formSubmit');
      });
      cy.get(`#${rememberChoiceId}`).check();
      cy.get(idpSelector).first().click({force: true});
      cy.get('@formSubmit').should('have.been.calledOnce');
      cy.get(idpSelector).first().find('input[name="rememberChoice"]').should('have.value', '1');
    });

    it('Leaves the hidden rememberChoice field at 0 on the submitted IdP form when the checkbox is left unchecked', () => {
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=1&addDiscoveries=0&rememberChoiceFeature=true&rememberChoicePerIdp=true');
      cy.window().then((win) => {
        cy.stub(win.HTMLFormElement.prototype, 'submit').as('formSubmit');
      });
      cy.get(idpSelector).first().click({force: true});
      cy.get('@formSubmit').should('have.been.calledOnce');
      cy.get(idpSelector).first().find('input[name="rememberChoice"]').should('have.value', '0');
    });

    it('Sets the legacy rememberchoice cookie when the checkbox is checked for the global variant', () => {
      cy.clearCookie('rememberchoice');
      cy.visit('https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=1&addDiscoveries=0&rememberChoiceFeature=true&rememberChoicePerIdp=false');
      cy.window().then((win) => {
        cy.stub(win.HTMLFormElement.prototype, 'submit').as('formSubmit');
      });
      cy.get(`#${rememberChoiceId}`).check();
      cy.get(idpSelector).first().click({force: true});
      cy.get('@formSubmit').should('have.been.calledOnce');
      cy.getCookie('rememberchoice').should('exist');
    });
  });
});
