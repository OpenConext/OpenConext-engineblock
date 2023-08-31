import {assignWeight} from '../../../../../../../theme/base/javascripts/wayf/search/assignWeight';

context('unit test the assignWeight function', () => {
  describe('Test multiple hits', () => {
    // should show:
    // full title (1234567 8 9) = 100
    // partial title + full partial title (1234567) = (40 + 30) / 3 = 23
    // full keyword (8) = 60
    // partial title + entityId + full partial title (9) = (30 + 20 + 40) / 3 = 30
    // = 213
    it('Test full match on title & keywords & partial on entityId & keyword', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '1234567 8 9');

      expect(Number(idpArray[0].getAttribute('data-weight'))).to.equal(213);
    });

    // should show 60 + 60
    it('Test full match on keywords & partial on title', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '8');

      expect(Number(idpArray[0].getAttribute('data-weight'))).to.equal(60);
    });

    // should show 18
    it('Test partial on keywords & partial on title', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '6');

      expect(Number(idpArray[0].getAttribute('data-weight'))).to.equal(18);
    });

    // should show 30
    it('Test partial match on title & entityId & full partial title', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '9');

      expect(Number(idpArray[0].getAttribute('data-weight'))).to.equal(30);
    });
  });
});

function createIdpArray() {
  const li = document.createElement('li');

  li.setAttribute('data-title', '1234567 8 9');
  li.setAttribute('data-entityId', 'id12349');
  li.setAttribute('data-keywords', 'bogus|zever|nie echt|wel tof|3456|d12|8');

  return [li];
}
