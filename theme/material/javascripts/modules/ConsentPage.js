export function initConsentPage() {
    const tippy = require('./../../../node_modules/tippy.js/dist/tippy.all.min.js');

    focusAcceptButton();
    initAttributesToggle();
    initTooltips();

    function focusAcceptButton() {
        document.getElementById('accept_terms_button').focus();
    }

    function initTooltips() {
        const anchors = document.querySelectorAll('a.tooltip');

        tippy(
            anchors,
            {
                animation: 'scale',
                arrow: true,
                duration: 200,
                placement: 'top',
                theme: 'light bordered'
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
                () => anchor.closest('tbody').classList.toggle('expanded')
            )
        );
    }
}
