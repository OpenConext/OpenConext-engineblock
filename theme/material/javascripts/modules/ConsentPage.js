import tippy from 'tippy.js';

// The lines below are polyfills for Tippy
import 'core-js/stable/promise';
import 'core-js/stable/object/assign';
import 'core-js/stable/array/find';
//

export function initConsentPage() {

    focusAcceptButton();
    initAttributesToggle();
    initTooltips();
    initSlideIns();

    function focusAcceptButton() {
        // Only focus on the accept terms button if it is visible on the page (to prevent scrolldown)
        if (isVisibleOnPage('accept_terms_button')){
            document.getElementById('accept_terms_button').focus();
        }
    }

    /**
     * Tests if the element (specified by id) is visible on the page. Once the element is partly in view the method
     * will start returning true.
     *
     * @param elementId
     * @returns {boolean}
     */
    function isVisibleOnPage(elementId) {
        const windowHeight = window.innerHeight;
        const elementTopPosition = document.getElementById(elementId).offsetTop;
        return windowHeight > elementTopPosition;
    }

    function initTooltips() {
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
    }
}
