import {AbstractIdpPicker} from "./AbstractIdpPicker";
import {IdpList} from './IdpList';
import {PreviousSelectionList} from './PreviousSelectionList';
import {PreviousSelectionStorage} from './PreviousSelectionStorage';
import {RememberChoiceStorage} from './RememberChoiceStorage';
import {IdpListElementFactory} from './IdpListElementFactory';
import {UnconnectedIdpPicker} from './UnconnectedIdpPicker';
import {toggleVisibility} from '../../../base/javascripts/utility/toggleVisibility';
import {searchBarEventListeners} from '../../../base/javascripts/wayf/searchBarEventListeners';

export class IdpPicker extends AbstractIdpPicker {
    constructor(searchForm, targetElement, previousSelectionList, idpList, previousSelectionStorage, rememberChoiceFeature, rememberChoiceStorage) {
        super(searchForm, targetElement, idpList);

        this.previousSelectionList = previousSelectionList;
        this.previousSelectionStorage = previousSelectionStorage;

        this.rememberChoiceFeature = rememberChoiceFeature;
        this.rememberChoiceStorage = rememberChoiceStorage;
        this.indexOfIdpUnderFocus = null;

        this.updateFocus();
    }

    narrowFilterBy(searchTerm) {
        super.narrowFilterBy(searchTerm);

        this.previousSelectionList.narrowFilterBy(searchTerm);
    }

    filterBy(searchTerm) {
        super.filterBy(searchTerm);

        this.previousSelectionList.filterBy(searchTerm);
    }

    updateFocus() {
        super.updateFocus();

        this.focusOnFirstIdp();
        this.indexElements();
        this.updateResultSelectedClickListeners();
        this.updateEditPreviousSelectionClickListener();
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
            $underFocus[i].classList.remove('focussed');
        }
        $results[index].classList.add('focussed');

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
        };
    }

    updateEditPreviousSelectionClickListener() {
        const $editButton = this.targetElement.querySelector('.edit');
        if ($editButton) {
            $editButton.removeEventListener('click', this.editPreviousSelectionClickHandler());
            $editButton.addEventListener('click', this.editPreviousSelectionClickHandler());
        }
    }

    editPreviousSelectionClickHandler() {
        return (event) => {
            event.preventDefault();

            const $previousSelectionList = this.targetElement.querySelector('.preselection .idp-list');

            if ($previousSelectionList.className.indexOf('show-buttons') > -1) {
                $previousSelectionList.classList.remove('show-buttons');
            } else {
                $previousSelectionList.classList.add('show-buttons');
            }

            const toggleText = event.currentTarget.getAttribute('data-toggle-text');
            event.currentTarget.setAttribute('data-toggle-text', event.currentTarget.textContent);
            event.currentTarget.textContent = toggleText;

            this.previousSelectionList.editing = !this.previousSelectionList.editing;
            this.previousSelectionList.render();

            this.indexElements();
            this.focusOnIdpByIndex(this.indexOfIdpUnderFocus);

            if (this.previousSelectionList.editing) {
                this.updateDeleteIdpFromPreviousSelectionClickListeners();
            } else {
                this.updateResultSelectedClickListeners();
            }
        };
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
        };
    }

    deleteIdpFromPreviousSelection(index) {
        const idp = this.previousSelectionList.getFilteredIdpByIndex(index);
        this.previousSelectionList.removeIdpByIndex(index);
        this.idpList.addIdp(idp);
        // When the last previous selection is removed, the previous chosen block is hidden, but we need an
        // explicit call to updateFocus, in order to re-register the mouse listeners on the connected IdPs.
        if (this.previousSelectionList.getLengthOfFilteredList() === 0) {
            this.updateFocus();
        } else {
            this.updateDeleteIdpFromPreviousSelectionClickListeners();
            this.indexElements();
            this.focusOnFirstIdp();
        }

        this.previousSelectionStorage.save(this.previousSelectionList.getPreviousSelections());
    }

    /**
     * Test if the current node is an unconnected/noaccess node
     * @returns {boolean}
     */
    isUnconnectedIdP() {
        return this.targetElement.querySelector('.result.focussed.noaccess') !==null;
    }

    /**
     * Retrieve the title and entity id from the currently focussed unconnected/noaccess node
     *
     * The data is returned as an anonymous object with a title and entityId property
     *
     * @returns {object}
     */
    getUnconnectedIdPDetails() {
        const title = this.targetElement.querySelector('.result.focussed.noaccess h3');
        const entityId = this.targetElement.querySelector('.result.focussed.noaccess span.action');

        return {title: title.textContent, entityId: entityId.getAttribute('data-entity-id')};
    }

    selectIdpUnderFocus() {
        const $focused = this.targetElement.querySelector('.result.focussed');

        if ($focused === null) {
            return;
        }

        const index = $focused.getAttribute('data-idp-list-index');

        let idp;

        if (this.previousSelectionList) {
            if (this.previousSelectionList.hasElement($focused)) {
                idp = this.previousSelectionList.getFilteredIdpByIndex(index);
            } else {
                idp = this.idpList.getFilteredIdpByIndex(index - this.previousSelectionList.getLengthOfFilteredList());
            }

            this.previousSelectionStorage.save(this.previousSelectionList.getListUpdatedWith(idp));
        }
        if (this.rememberChoiceFeature) {
            const $checkbox = this.targetElement.querySelector('#rememberChoice');
            if ($checkbox.checked) {
                this.rememberChoiceStorage.save(idp.entityId);
            }
        }

        this.searchForm.elements['form-idp'].value = idp.entityId;
        this.searchForm.submit();
    }
}

export const returnNewIdpPicker = (configuration, searchForm) => {
  const idpListElementFactory = new IdpListElementFactory(configuration.messages);
  const connectedIdpList = new IdpList(
    document.querySelector('#idp-picker .selection'),
    configuration.connectedIdps,
    idpListElementFactory,
    configuration.cutoffPointForShowingUnfilteredIdps
  );
  const previousSelectionList    = new PreviousSelectionList(
    document.querySelector('#idp-picker .preselection'),
    configuration.previousSelectionList,
    idpListElementFactory
  );

  return new IdpPicker(
    searchForm,
    document.getElementById('idp-picker'),
    previousSelectionList,
    connectedIdpList,
    new PreviousSelectionStorage(configuration.previousSelectionCookieName),
    configuration.rememberChoiceFeature,
    new RememberChoiceStorage(configuration.rememberChoiceCookieName)
  );
};

const returnNewUnconnectedIdpPicker = (configuration, searchForm, unconnectedIdpPickerTarget) => {
  const unconnectedIdpList = new IdpList(
    document.querySelector(' #unconnected-idp-picker .selection'),
    configuration.unconnectedIdps,
    new IdpListElementFactory(configuration.messages),
    configuration.cutoffPointForShowingUnfilteredIdps
  );

  return new UnconnectedIdpPicker(
    searchForm,
    unconnectedIdpPickerTarget,
    unconnectedIdpList
  );
};

const registerUnconnectedIdPickerEvents = (unconnectedIdpPickerTarget, requestAccessModalHelper, keyboardListener) => {
  unconnectedIdpPickerTarget.addEventListener('click', requestAccessModalHelper.requestAccessClickHandler());

  // Use the keyboardListener to open the modal on ENTER presses
  unconnectedIdpPickerTarget.addEventListener('keyup', event => keyboardListener.handle(event.keyCode));
};

export const handleUnconnectedKeypicker = (requestAccessModalHelper, configuration, searchForm, searchBar, idpPicker, keyboardListener) => {
  const unconnectedIdpPickerTarget = document.getElementById('unconnected-idp-picker');
  if (unconnectedIdpPickerTarget) {
    // Only show the unconnected IdP box when javascript is enabled.
    toggleVisibility(unconnectedIdpPickerTarget);

    const unconnectedIdpPicker = returnNewUnconnectedIdpPicker(configuration, searchForm, unconnectedIdpPickerTarget);

    /**
     * Call indexElements to also include the disconnected IdPs in
     * the IdP list index, ensuring mouse and keyboard behavior
     * matches that of the connected IdP list.
     */
    idpPicker.indexElements();
    searchBarEventListeners(searchBar, unconnectedIdpPicker);
    registerUnconnectedIdPickerEvents(unconnectedIdpPickerTarget, requestAccessModalHelper, keyboardListener);
  }
};
