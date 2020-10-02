export const initSlideIns = () => {
  Array.prototype.forEach.call(
    document.querySelectorAll('a[data-slidein]'),
    (anchor) => anchor.addEventListener(
      'click',
      (event) => {
        event.preventDefault();

        const slideIn = document.querySelector(
          '.slidein.' + anchor.getAttribute('data-slidein')
        );

        if (slideIn !== null) {
          slideIn.classList.add('visible');
          document.body.classList.add('slidein-open');

          slideIn.querySelector('a.close').addEventListener(
            'click',
            (event) => {
              event.preventDefault();

              slideIn.classList.remove('visible');
              document.body.classList.remove('slidein-open');
            },
            {
              once: true
            }
          );
        }
      }
    )
  );
};
