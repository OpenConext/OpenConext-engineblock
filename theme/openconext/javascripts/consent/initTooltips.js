import tippy from 'tippy.js';

export const initTooltips = () => {
  const anchors = document.querySelectorAll('a.tooltip');

  tippy(
    anchors,
    {
      animation: 'scale',
      arrow: true,
      arrowTransform: 'scale(2)',
      distance: 30,
      duration: 200,
      followCursor: true,
      placement: 'top',
      theme: 'light',
    }
  );

  Array.prototype.forEach.call(
    anchors,
    (anchor) => anchor.addEventListener(
      'click',
      (event) => event.preventDefault()
    )
  );
};
