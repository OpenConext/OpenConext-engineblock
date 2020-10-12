import {assignWeight} from '../../../../../base/javascripts/wayf/search/assignWeight';

context('unit test the assignWeight function', () => {
  describe('Test multiple hits', () => {
    // should show 100 + 30 + 25 + 18 + 17 = 187
    it('Test full match on title & keywords & partial on entityId & keyword', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '1234567 8 9');

      expect(Number(idpArray[0].children[0].getAttribute('data-weight'))).to.equal(187);
    });

    // should show 60 + 60
    it('Test full match on keywords & partial on title', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '8');

      expect(Number(idpArray[0].children[0].getAttribute('data-weight'))).to.equal(60);
    });

    // should show 18
    it('Test partial on keywords & partial on title', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '6');

      expect(Number(idpArray[0].children[0].getAttribute('data-weight'))).to.equal(18);
    });

    // should show 17
    it('Test partial match on title & entityId', () => {
      const idpArray = createIdpArray();
      assignWeight(idpArray, '9');

      expect(Number(idpArray[0].children[0].getAttribute('data-weight'))).to.equal(17);
    });
  });
});

function createIdpArray() {
  const li = document.createElement('li');
  const idp = document.createElement('article');

  idp.setAttribute('data-title', '1234567 8 9');
  idp.setAttribute('data-entityId', 'id12349');
  idp.setAttribute('data-keywords', 'bogus|zever|nie echt|wel tof|3456|d12|8');

  li.innerHTML = idp.outerHTML;

  return [li];
}
