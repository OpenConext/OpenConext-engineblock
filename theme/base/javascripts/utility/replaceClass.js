/**
 * Given that IE11 does not support classList.replace, we need a helper function to do it the old way.
 * @param element
 * @param oldClass
 * @param newClass
 */
export const replaceClass = (element, oldClass, newClass) => {
  element.classList.remove(oldClass);
  element.classList.add(newClass);
};
