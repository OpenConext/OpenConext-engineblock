export class IdpPicker {
    constructor(searchForm, targetElement, previousSelectionList, idpList, previousSelectionStorage) {
        this.searchForm = searchForm;
        this.targetElement = targetElement;
        this.previousSelectionList = previousSelectionList;
        this.idpList = idpList;
        this.previousSelectionStorage = previousSelectionStorage;

        this.previousSearchTerm = '';
        this.indexOfIdpUnderFocus = null;

        this.focusOnFirstIdp();
        this.indexElements();
        this.updateResultSelectedClickListeners();
        this.updateEditPreviousSelectionClickListener();
    }

    searchBy(searchTerm) {
        searchTerm = searchTerm.trim().toLowerCase();

        if (searchTerm === this.previousSearchTerm) {
            return;
        }

        if (searchTerm.length > this.previousSearchTerm.length) {
            this.previousSelectionList.narrowFilterBy(searchTerm);
            this.idpList.narrowFilterBy(searchTerm);
        } else {
            this.previousSelectionList.filterBy(searchTerm);
            this.idpList.filterBy(searchTerm);
        }

        this.focusOnFirstIdp();
        this.indexElements();
        this.updateResultSelectedClickListeners();
        this.updateEditPreviousSelectionClickListener();

        this.previousSearchTerm = searchTerm;
    }

    indexElements() {
        const $results = this.targetElement.querySelectorAll('.result');

        for (let i = 0; i < $results.length; i++) {
            $results[i].setAttribute('data-idp-list-index', i);
        }
    }

    focusOnFirstIdp() {
        this.focusOnIdpByIndex(0);
    }

    focusOnIdpByIndex(index) {
        const $results = this.targetElement.querySelectorAll('.result');

        if ($results.length === 0) {
            return;
        }

        if (index >= $results.length) {
            return;
        }

        if ($results[index].className.indexOf('focussed') > -1) {
            return;
        }

        const $underFocus = this.targetElement.querySelectorAll('.result.focussed');
        for (let i = 0; i < $underFocus.length; i++) {
            $underFocus[i].className = $underFocus[i].className.replace(' focussed', '');
        }
        $results[index].className += ' focussed';

        this.indexOfIdpUnderFocus = index;
    }

    focusOnPreviousIdp() {
        if (this.indexOfIdpUnderFocus === 0) {
            return;
        }

        this.focusOnIdpByIndex(this.indexOfIdpUnderFocus - 1);
    }

    focusOnNextIdp() {
        this.focusOnIdpByIndex(this.indexOfIdpUnderFocus + 1);
    }

    isFocusOnFirstIdp() {
        return this.indexOfIdpUnderFocus === 0;
    }

    updateResultSelectedClickListeners() {
        const $results = this.targetElement.querySelectorAll('.result');

        for (let i = 0; i < $results.length; i++) {
            $results[i].removeEventListener('click', this.resultSelectedClickHandler());
            $results[i].addEventListener('click', this.resultSelectedClickHandler());
        }
    }

    resultSelectedClickHandler() {
        return (event) => {
            // If the delete button is clicked instead of the container, we do not want to select an IdP
            if (event.target.className.indexOf('deleteable') > -1) {
                return;
            }

            event.preventDefault();

            this.focusOnIdpByIndex(event.currentTarget.getAttribute('data-idp-list-index'));
            this.selectIdpUnderFocus();
        }
    }

    updateEditPreviousSelectionClickListener() {
        const $editButton = this.targetElement.querySelector('.edit');
        $editButton.removeEventListener('click', this.editPreviousSelectionClickHandler());
        $editButton.addEventListener('click', this.editPreviousSelectionClickHandler());
    }

    editPreviousSelectionClickHandler() {
        return (event) => {
            event.preventDefault();

            const $previousSelectionList = this.targetElement.querySelector('#preselection .idp-list');

            if ($previousSelectionList.className.indexOf('show-buttons') > -1) {
                $previousSelectionList.className = $previousSelectionList.className.replace('show-buttons', '');
            } else {
                $previousSelectionList.className += ' show-buttons';
            }

            const toggleText = event.currentTarget.getAttribute('data-toggle-text');
            event.currentTarget.setAttribute('data-toggle-text', event.currentTarget.textContent);
            event.currentTarget.textContent = toggleText;

            this.previousSelectionList.editing = !this.previousSelectionList.editing;
            this.previousSelectionList.render();

            this.updateDeleteIdpFromPreviousSelectionClickListeners();
            this.indexElements();
            this.focusOnIdpByIndex(this.indexOfIdpUnderFocus);
        }
    }

    updateDeleteIdpFromPreviousSelectionClickListeners() {
        const $deletionButtons = this.targetElement.querySelectorAll('.result .deleteable.action');
        for (let i = 0; i < $deletionButtons.length; i++) {
            $deletionButtons[i].removeEventListener('click', this.deleteIdpFromPreviousSelectionClickHandler());
            $deletionButtons[i].addEventListener('click', this.deleteIdpFromPreviousSelectionClickHandler());
        }
    }

    deleteIdpFromPreviousSelectionClickHandler() {
        return (event) => {
            event.preventDefault();
            const index = event.currentTarget.parentNode.getAttribute('data-idp-list-index');
            this.deleteIdpFromPreviousSelection(index);
        }
    }

    deleteIdpFromPreviousSelection(index) {
        const idp = this.previousSelectionList.getFilteredIdpByIndex(index);
        this.previousSelectionList.removeIdpByIndex(index);
        this.idpList.addIdp(idp);

        this.updateDeleteIdpFromPreviousSelectionClickListeners();
        this.indexElements();
        this.focusOnFirstIdp();

        this.previousSelectionStorage.save(this.previousSelectionList.getPreviousSelections());
    }

    selectIdpUnderFocus() {
        const $focused = this.targetElement.querySelector('.result.focussed');

        if ($focused === null) {
            return;
        }

        const index = $focused.getAttribute('data-idp-list-index');

        let idp;
        if (this.previousSelectionList.hasElement($focused)) {
            idp = this.previousSelectionList.getFilteredIdpByIndex(index);
        } else {
            idp = this.idpList.getFilteredIdpByIndex(index - this.previousSelectionList.getLengthOfFilteredList());
        }

        this.previousSelectionStorage.save(this.previousSelectionList.getListUpdatedWith(idp));

        this.searchForm.elements['form-idp'].value = idp.entityId;
        this.searchForm.submit();
    }
}
