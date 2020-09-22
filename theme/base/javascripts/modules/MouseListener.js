export class MouseListener {
    constructor(idpPicker, requestAccessModalHelper) {
        this.idpPicker        = idpPicker;
        this.requestAccessModalHelper = requestAccessModalHelper;
    }

    handle(target) {
        if (this.requestAccessModalHelper.modalIsOpen()) {

            // Don't interfere with the IDP selection list when
            // the request-access modal dialog is open.
            return;
        }

        var index = target.getAttribute('data-idp-list-index');
        if (index !== null) {
            this.idpPicker.focusOnIdpByIndex(index);
        }
    }
}
