/**
 * Add polyfills for methods IE11 does not support.
 * All polyfills taken from https://vanillajstoolkit.com/polyfills
 */
export const addPolyfills = () => {
  polyfillElementDotClosest();
  polyfillElementDotRemove();
};

function polyfillElementDotRemove () {
  /**
   * ChildNode.remove() polyfill
   * https://gomakethings.com/removing-an-element-from-the-dom-the-es6-way/
   * @author Chris Ferdinandi
   * @license MIT
   */
  (function (elem) {
    for (var i = 0; i < elem.length; i++) {
      if (!window[elem[i]] || 'remove' in window[elem[i]].prototype) continue;
      window[elem[i]].prototype.remove = function () {
        this.parentNode.removeChild(this);
      };
    }
  })(['Element', 'CharacterData', 'DocumentType']);
}

function polyfillElementDotClosest () {
  /**
   * Element.closest() polyfill
   * https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#Polyfill
   */
  if (!Element.prototype.closest) {
    if (!Element.prototype.matches) {
      Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
    }
    Element.prototype.closest = function (s) {
      var el = this;
      var ancestor = this;
      if (!document.documentElement.contains(el)) return null;
      do {
        if (ancestor.matches(s)) return ancestor;
        ancestor = ancestor.parentElement;
      } while (ancestor !== null);
      return null;
    };
  }
}
