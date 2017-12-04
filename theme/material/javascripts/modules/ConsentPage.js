export function initConsentPage() {
    focusAcceptButton();
    initAttributesToggle();
    initSlideIns();

    function focusAcceptButton() {
        document.getElementById('accept_terms_button').focus();
    }

    function initAttributesToggle() {
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
    }

    function initSlideIns() {
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

                        slideIn.querySelector('a.close').addEventListener(
                            'click',
                            (event) => {
                                event.preventDefault();

                                slideIn.classList.remove('visible');
                            },
                            {
                                once: true
                            }
                        );
                    }
                }
            )
        );
    }
}
