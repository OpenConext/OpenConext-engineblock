export const initAttributesToggle = () => {
  Array.prototype.forEach.call(
    document.querySelectorAll('tr.toggle-attributes a'),
    (anchor) => anchor.addEventListener(
      'click',
      (event) => {
        event.preventDefault();

        anchor.closest('tbody').classList.toggle('expanded');
      }
    )
  );
};
