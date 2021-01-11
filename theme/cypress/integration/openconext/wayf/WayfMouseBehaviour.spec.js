context('WayfMouseBehaviour', () => {

  /**
   * Reproduction of the behaviour, described in:
   * https://www.pivotaltracker.com/story/show/165056451
   *
   * Skip this test until proper hover support is added to Cypress.
   */
  it.skip('Disconnected IdPs should be highlighted on mouse hover', () => {
    // Open a dummy wayf with 5 connected IdPs and 5 unconnected IdPs
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10&displayUnconnectedIdpsWayf=true&unconnectedIdps=5');
    cy.get('#unconnected-idp-picker > div > div.idp-list > a.result.active.noaccess:nth-child(1)')
      .hover()
      .should('have.class', 'focussed');
  });
  /**
   * Reproduction of the behaviour, described in:
   * https://www.pivotaltracker.com/story/show/165021022
   */
  it('Connected IdP should respond to mouse click after clearing previous selections', () => {
    // Open a dummy wayf with 5 connected IdPs
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10&displayUnconnectedIdpsWayf=true&unconnectedIdps=5');
    // Click the first IdP, adding it to the list of previously chosen IdPs
    cy.get('a.result.active.access:nth-child(1)').click({force:true});
    // We visit the fake IdP, verify the right redirect is performed
    cy.location().should((loc) => {
      expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/1');
    });
    // Go back to the WAYF
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10&displayUnconnectedIdpsWayf=true&unconnectedIdps=5');
    // Click the second IdP, adding it to the list of previously chosen IdPs
    cy.get('a.result.active.access:nth-child(2)').click({force:true});
    // We visit the fake IdP, verify the right redirect is performed
    cy.location().should((loc) => {
      expect(loc.href).to.eq('https://engine.vm.openconext.org/?idp=https%3A//example.com/entityId/2');
    });
    // Go back to the WAYF
    cy.visit('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10&displayUnconnectedIdpsWayf=true&unconnectedIdps=5');
    cy.get('div.preselection header h2')
      .should('contain.text', 'Previously chosen:');
    cy.get('.edit')
      .should('have.text', 'edit')
      .click();
    cy.get('span.deleteable').first().click({force:true});
    cy.get('span.deleteable').click({force:true});
    cy.get('div.preselection header h2')
      .should('not.be.visible');
  });
});
