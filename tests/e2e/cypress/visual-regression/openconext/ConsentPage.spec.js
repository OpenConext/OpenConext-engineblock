const viewports = [
  {width: 375, height: 667},
  {width: 1920, height: 1080},
];

const pageTests = [
  {
    title: 'default',
    url: 'https://engine.vm.openconext.org/functional-testing/consent'
  }
];

context('Consent', () => {
  viewports.forEach((viewport) => {
    pageTests.forEach((pageDetails) => {
      it('render ' + pageDetails.title + ' ' + viewport.width + 'x' + viewport.height, () => {
        cy.matchImageSnapshots(viewport, pageDetails);
      });
    });
  });
});
