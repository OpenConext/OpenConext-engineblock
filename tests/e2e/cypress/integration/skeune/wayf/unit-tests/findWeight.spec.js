import {findWeight} from '../../../../../../../theme/base/javascripts/wayf/search/findWeight';

context('unit test the findweight function', () => {
  describe('Test title', () => {
    it ('Test full match on title', () => {
      const idp = createIdp();
      expect(findWeight(idp, '1234567')).to.equal(100);
    });

    // should show 30 / 3 = 10
    it ('Test partial match on title', () => {
      const idp = createIdp();
      expect(findWeight(idp, '7')).to.equal(10);
    });
  });

  describe('Test entityId', () => {
    it ('Test full match on entityId', () => {
      const idp = createIdp();
      expect(findWeight(idp, 'id1234')).to.equal(60);
    });

    // should show 20 / 3 = 7
    it ('Test partial match on entityId', () => {
      const idp = createIdp();
      expect(findWeight(idp, 'id')).to.equal(7);
    });
  });

  describe('Test keywords', () => {
    it ('Test full match on keywords', () => {
      const idp = createIdp();
      expect(findWeight(idp, 'zever')).to.equal(60);
    });

    // should show 25 / 3 = 8
    it ('Test partial match on keywords', () => {
      const idp = createIdp();
      expect(findWeight(idp, 'zev')).to.equal(8);
    });
  });

  describe('Test multiple partials', () => {
    // should show (30 + 20) / 3 = 17
    it('Test partial match on title & entityId', () => {
      const idp = createIdp();
      expect(findWeight(idp, '123')).to.equal(17);
    });

    // should show (30 + 25) / 3 = 18
    it('Test partial match on title & keywords', () => {
      const idp = createIdp();
      expect(findWeight(idp, '45')).to.equal(18);
    });

    // should show (20 + 25) / 3 = 15
    it('Test partial match on entityId & keywords', () => {
      const idp = createIdp();
      expect(findWeight(idp, 'd1')).to.equal(15);
    });

    // should show (30 + 20 + 25) / 3 = 25
    it('Test partial match on title, entityId & keywords', () => {
      const idp = createIdp();
      expect(findWeight(idp, '34')).to.equal(25);
    });
  });
});

function createIdp() {
  const idp = document.createElement('article');
  idp.setAttribute('data-title', '1234567');
  idp.setAttribute('data-entityId', 'id1234');
  idp.setAttribute('data-keywords', 'bogus|zever|nie echt|wel tof|3456|d12');

  return idp;
}
