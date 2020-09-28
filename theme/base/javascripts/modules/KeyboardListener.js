export class KeyboardListener {
    constructor(idpPicker, searchBarElement, requestAccessModalHelper) {
        this.idpPicker        = idpPicker;
        this.searchBarElement = searchBarElement;
        this.requestAccessModalHelper = requestAccessModalHelper;
    }

    handle(keyCode) {
        if (this.requestAccessModalHelper.modalIsOpen()) {

            if (keyCode === ESCAPE) {
                this.requestAccessModalHelper.closeRequestAccessModal();
                return;
            }

            // Don't interfere with the IDP selection list when
            // the request-access modal dialog is open.
            return;
        }

        if (keyCode === ARROW_DOWN) {
            if (document.activeElement === this.searchBarElement) {
                this.searchBarElement.blur();
                return;
            }

            this.idpPicker.focusOnNextIdp();
            return;
        }

        if (keyCode === ARROW_UP) {
            if (document.activeElement === this.searchBarElement) {
                return;
            }

            if (this.idpPicker.isFocusOnFirstIdp()) {
                this.searchBarElement.focus();
                return;
            }

            this.idpPicker.focusOnPreviousIdp();
            return;
        }

        if (keyCode === ENTER) {
            if (this.idpPicker.isUnconnectedIdP()) {
                const data = this.idpPicker.getUnconnectedIdPDetails();
                this.requestAccessModalHelper.openRequestAccessModal(data.title, data.entityId);
            } else {
                this.idpPicker.selectIdpUnderFocus();
            }
        }
    }
}

const ENTER      = 13;
const ESCAPE     = 27;
const ARROW_UP   = 38;
const ARROW_DOWN = 40;
