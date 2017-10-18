// This file is generated. Please edit the files of the appropriate theme in the 'theme/' directory.
(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

var _EngineBlockMainPage = require("./modules/EngineBlockMainPage");

var _IdpList = require("./modules/IdpList");

var _ConnectedIdpPicker = require("./modules/ConnectedIdpPicker");

var _UnconnectedIdpPicker = require("./modules/UnconnectedIdpPicker");

var _PreviousSelectionList = require("./modules/PreviousSelectionList");

var _KeyboardListener = require("./modules/KeyboardListener");

var _PreviousSelectionStorage = require("./modules/PreviousSelectionStorage");

var _IdpListElementFactory = require("./modules/IdpListElementFactory");

var _RequestAccessModalHelper = require("./modules/RequestAccessModalHelper");

function initialize() {
    document.body.className = document.body.className.replace('no-js', '');

    if (document.getElementById('engine-main-page') !== null) {
        (0, _EngineBlockMainPage.replaceMetadataCertificateLinkTexts)();
        return;
    }

    if (document.getElementById('wayf-configuration') === null) {
        return;
    }

    var $searchBar = document.querySelector('.mod-search-input');
    var $connectedIdpPickerTarget = document.getElementById('connected-idp-picker');
    var $connectedIdpListTarget = $connectedIdpPickerTarget.querySelector('.selection');
    var $previousSelectionTarget = $connectedIdpPickerTarget.querySelector('.preselection');
    var $searchForm = document.querySelector('form.mod-search');
    var $requestAccessModal = document.getElementById('request-access');
    var $requestAccessScroller = document.getElementById('request-access-scroller');

    var configuration = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
    var throttleAmountInMs = 250;

    var idpListElementFactory = new _IdpListElementFactory.IdpListElementFactory(configuration.messages);
    var connectedIdpList = new _IdpList.IdpList($connectedIdpListTarget, configuration.connectedIdps, idpListElementFactory, configuration.cutoffPointForShowingUnfilteredIdps);
    var previousSelectionList = new _PreviousSelectionList.PreviousSelectionList($previousSelectionTarget, configuration.previousSelectionList, idpListElementFactory);
    var previousSelectionStorage = new _PreviousSelectionStorage.PreviousSelectionStorage(configuration.previousSelectionCookieName);
    var connectedIdpPicker = new _ConnectedIdpPicker.ConnectedIdpPicker($searchForm, $connectedIdpPickerTarget, previousSelectionList, connectedIdpList, previousSelectionStorage);
    var requestAccessModalHelper = new _RequestAccessModalHelper.RequestAccessModalHelper($requestAccessModal, $requestAccessScroller, $searchBar, configuration.requestAccessUrl);
    var keyboardListener = new _KeyboardListener.KeyboardListener(connectedIdpPicker, $searchBar, requestAccessModalHelper);

    // Keyup, click and input are registered events for cross-browser compatibility with HTML5 'search' input
    $searchBar.addEventListener('keyup', throttle(function (event) {
        return connectedIdpPicker.searchBy(event.target.value);
    }, throttleAmountInMs));
    $searchBar.addEventListener('click', function (event) {
        return connectedIdpPicker.searchBy(event.target.value);
    });
    $searchBar.addEventListener('input', function (event) {
        return connectedIdpPicker.searchBy(event.target.value);
    });

    // Only show the search form when javascript is enabled.
    showElement($searchForm);

    document.addEventListener('keyup', function (event) {
        return keyboardListener.handle(event.keyCode);
    });

    $searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        connectedIdpPicker.selectIdpUnderFocus();
    });

    var $unconnectedIdpPickerTarget = document.getElementById('unconnected-idp-picker');
    if ($unconnectedIdpPickerTarget) {
        // Only show the unconnected IdP box when javascript is enabled.
        showElement($unconnectedIdpPickerTarget);

        var $unconnectedIdpListTarget = $unconnectedIdpPickerTarget.querySelector('.selection');
        var unconnectedIdpList = new _IdpList.IdpList($unconnectedIdpListTarget, configuration.unconnectedIdps, idpListElementFactory, configuration.cutoffPointForShowingUnfilteredIdps);
        var unconnectedIdpPicker = new _UnconnectedIdpPicker.UnconnectedIdpPicker($searchForm, $unconnectedIdpPickerTarget, unconnectedIdpList);

        $searchBar.addEventListener('keyup', throttle(function (event) {
            return unconnectedIdpPicker.searchBy(event.target.value);
        }, throttleAmountInMs));
        $searchBar.addEventListener('click', function (event) {
            return unconnectedIdpPicker.searchBy(event.target.value);
        });
        $searchBar.addEventListener('input', function (event) {
            return unconnectedIdpPicker.searchBy(event.target.value);
        });

        $unconnectedIdpPickerTarget.addEventListener('click', requestAccessModalHelper.requestAccessClickHandler());
    }

    if (window.innerWidth > 800) {
        $searchBar.focus();
    }
}

function throttle(action, delay) {
    var timer = null;

    return function () {
        var _this = this,
            _arguments = arguments;

        clearTimeout(timer);
        timer = setTimeout(function () {
            return action.apply(_this, _arguments);
        }, delay);
    };
}

function showElement(element) {
    var pattern = 'hidden';
    if (element.className.match(/ hidden/)) {
        pattern = ' hidden';
    }

    element.className = element.className.replace(pattern, '');
}

initialize();

},{"./modules/ConnectedIdpPicker":3,"./modules/EngineBlockMainPage":4,"./modules/IdpList":6,"./modules/IdpListElementFactory":7,"./modules/KeyboardListener":8,"./modules/PreviousSelectionList":9,"./modules/PreviousSelectionStorage":10,"./modules/RequestAccessModalHelper":11,"./modules/UnconnectedIdpPicker":12}],2:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var AbstractIdpPicker = exports.AbstractIdpPicker = function () {
    function AbstractIdpPicker(searchForm, targetElement, idpList) {
        _classCallCheck(this, AbstractIdpPicker);

        this.searchForm = searchForm;
        this.targetElement = targetElement;
        this.idpList = idpList;

        this.previousSearchTerm = '';
    }

    AbstractIdpPicker.prototype.searchBy = function searchBy(searchTerm) {
        searchTerm = searchTerm.trim().toLowerCase();

        if (searchTerm === this.previousSearchTerm) {
            return;
        }

        if (searchTerm.length > this.previousSearchTerm.length) {
            this.narrowFilterBy(searchTerm);
        } else {
            this.filterBy(searchTerm);
        }

        this.updateFocus();

        this.previousSearchTerm = searchTerm;
    };

    AbstractIdpPicker.prototype.narrowFilterBy = function narrowFilterBy(searchTerm) {
        this.idpList.narrowFilterBy(searchTerm);
    };

    AbstractIdpPicker.prototype.filterBy = function filterBy(searchTerm) {
        this.idpList.filterBy(searchTerm);
    };

    AbstractIdpPicker.prototype.updateFocus = function updateFocus() {};

    return AbstractIdpPicker;
}();

},{}],3:[function(require,module,exports){
'use strict';

exports.__esModule = true;
exports.ConnectedIdpPicker = undefined;

var _AbstractIdpPicker2 = require('./AbstractIdpPicker');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var ConnectedIdpPicker = exports.ConnectedIdpPicker = function (_AbstractIdpPicker) {
    _inherits(ConnectedIdpPicker, _AbstractIdpPicker);

    function ConnectedIdpPicker(searchForm, targetElement, previousSelectionList, idpList, previousSelectionStorage) {
        _classCallCheck(this, ConnectedIdpPicker);

        var _this = _possibleConstructorReturn(this, _AbstractIdpPicker.call(this, searchForm, targetElement, idpList));

        _this.previousSelectionList = previousSelectionList;
        _this.previousSelectionStorage = previousSelectionStorage;

        _this.indexOfIdpUnderFocus = null;

        _this.updateFocus();
        return _this;
    }

    ConnectedIdpPicker.prototype.narrowFilterBy = function narrowFilterBy(searchTerm) {
        _AbstractIdpPicker.prototype.narrowFilterBy.call(this, searchTerm);

        this.previousSelectionList.narrowFilterBy(searchTerm);
    };

    ConnectedIdpPicker.prototype.filterBy = function filterBy(searchTerm) {
        _AbstractIdpPicker.prototype.filterBy.call(this, searchTerm);

        this.previousSelectionList.filterBy(searchTerm);
    };

    ConnectedIdpPicker.prototype.updateFocus = function updateFocus() {
        _AbstractIdpPicker.prototype.updateFocus.call(this);

        this.focusOnFirstIdp();
        this.indexElements();
        this.updateResultSelectedClickListeners();
        this.updateEditPreviousSelectionClickListener();
    };

    ConnectedIdpPicker.prototype.indexElements = function indexElements() {
        var $results = this.targetElement.querySelectorAll('.result');

        for (var i = 0; i < $results.length; i++) {
            $results[i].setAttribute('data-idp-list-index', i);
        }
    };

    ConnectedIdpPicker.prototype.focusOnFirstIdp = function focusOnFirstIdp() {
        this.focusOnIdpByIndex(0);
    };

    ConnectedIdpPicker.prototype.focusOnIdpByIndex = function focusOnIdpByIndex(index) {
        var $results = this.targetElement.querySelectorAll('.result');

        if ($results.length === 0) {
            return;
        }

        if (index >= $results.length) {
            return;
        }

        if ($results[index].className.indexOf('focussed') > -1) {
            return;
        }

        var $underFocus = this.targetElement.querySelectorAll('.result.focussed');
        for (var i = 0; i < $underFocus.length; i++) {
            $underFocus[i].className = $underFocus[i].className.replace(' focussed', '');
        }
        $results[index].className += ' focussed';

        this.indexOfIdpUnderFocus = index;
    };

    ConnectedIdpPicker.prototype.focusOnPreviousIdp = function focusOnPreviousIdp() {
        if (this.indexOfIdpUnderFocus === 0) {
            return;
        }

        this.focusOnIdpByIndex(this.indexOfIdpUnderFocus - 1);
    };

    ConnectedIdpPicker.prototype.focusOnNextIdp = function focusOnNextIdp() {
        this.focusOnIdpByIndex(this.indexOfIdpUnderFocus + 1);
    };

    ConnectedIdpPicker.prototype.isFocusOnFirstIdp = function isFocusOnFirstIdp() {
        return this.indexOfIdpUnderFocus === 0;
    };

    ConnectedIdpPicker.prototype.updateResultSelectedClickListeners = function updateResultSelectedClickListeners() {
        var $results = this.targetElement.querySelectorAll('.result');

        for (var i = 0; i < $results.length; i++) {
            $results[i].removeEventListener('click', this.resultSelectedClickHandler());
            $results[i].addEventListener('click', this.resultSelectedClickHandler());
        }
    };

    ConnectedIdpPicker.prototype.resultSelectedClickHandler = function resultSelectedClickHandler() {
        var _this2 = this;

        return function (event) {
            // If the delete button is clicked instead of the container, we do not want to select an IdP
            if (event.target.className.indexOf('deleteable') > -1) {
                return;
            }

            event.preventDefault();

            _this2.focusOnIdpByIndex(event.currentTarget.getAttribute('data-idp-list-index'));
            _this2.selectIdpUnderFocus();
        };
    };

    ConnectedIdpPicker.prototype.updateEditPreviousSelectionClickListener = function updateEditPreviousSelectionClickListener() {
        var $editButton = this.targetElement.querySelector('.edit');
        if ($editButton) {
            $editButton.removeEventListener('click', this.editPreviousSelectionClickHandler());
            $editButton.addEventListener('click', this.editPreviousSelectionClickHandler());
        }
    };

    ConnectedIdpPicker.prototype.editPreviousSelectionClickHandler = function editPreviousSelectionClickHandler() {
        var _this3 = this;

        return function (event) {
            event.preventDefault();

            var $previousSelectionList = _this3.targetElement.querySelector('.preselection .idp-list');

            if ($previousSelectionList.className.indexOf('show-buttons') > -1) {
                $previousSelectionList.className = $previousSelectionList.className.replace('show-buttons', '');
            } else {
                $previousSelectionList.className += ' show-buttons';
            }

            var toggleText = event.currentTarget.getAttribute('data-toggle-text');
            event.currentTarget.setAttribute('data-toggle-text', event.currentTarget.textContent);
            event.currentTarget.textContent = toggleText;

            _this3.previousSelectionList.editing = !_this3.previousSelectionList.editing;
            _this3.previousSelectionList.render();

            _this3.updateDeleteIdpFromPreviousSelectionClickListeners();
            _this3.indexElements();
            _this3.focusOnIdpByIndex(_this3.indexOfIdpUnderFocus);
        };
    };

    ConnectedIdpPicker.prototype.updateDeleteIdpFromPreviousSelectionClickListeners = function updateDeleteIdpFromPreviousSelectionClickListeners() {
        var $deletionButtons = this.targetElement.querySelectorAll('.result .deleteable.action');
        for (var i = 0; i < $deletionButtons.length; i++) {
            $deletionButtons[i].removeEventListener('click', this.deleteIdpFromPreviousSelectionClickHandler());
            $deletionButtons[i].addEventListener('click', this.deleteIdpFromPreviousSelectionClickHandler());
        }
    };

    ConnectedIdpPicker.prototype.deleteIdpFromPreviousSelectionClickHandler = function deleteIdpFromPreviousSelectionClickHandler() {
        var _this4 = this;

        return function (event) {
            event.preventDefault();
            var index = event.currentTarget.parentNode.getAttribute('data-idp-list-index');
            _this4.deleteIdpFromPreviousSelection(index);
        };
    };

    ConnectedIdpPicker.prototype.deleteIdpFromPreviousSelection = function deleteIdpFromPreviousSelection(index) {
        var idp = this.previousSelectionList.getFilteredIdpByIndex(index);
        this.previousSelectionList.removeIdpByIndex(index);
        this.idpList.addIdp(idp);

        this.updateDeleteIdpFromPreviousSelectionClickListeners();
        this.indexElements();
        this.focusOnFirstIdp();

        this.previousSelectionStorage.save(this.previousSelectionList.getPreviousSelections());
    };

    ConnectedIdpPicker.prototype.selectIdpUnderFocus = function selectIdpUnderFocus() {
        var $focused = this.targetElement.querySelector('.result.focussed');

        if ($focused === null) {
            return;
        }

        var index = $focused.getAttribute('data-idp-list-index');

        var idp = void 0;

        if (this.previousSelectionList) {
            if (this.previousSelectionList.hasElement($focused)) {
                idp = this.previousSelectionList.getFilteredIdpByIndex(index);
            } else {
                idp = this.idpList.getFilteredIdpByIndex(index - this.previousSelectionList.getLengthOfFilteredList());
            }

            this.previousSelectionStorage.save(this.previousSelectionList.getListUpdatedWith(idp));
        }

        this.searchForm.elements['form-idp'].value = idp.entityId;
        this.searchForm.submit();
    };

    return ConnectedIdpPicker;
}(_AbstractIdpPicker2.AbstractIdpPicker);

},{"./AbstractIdpPicker":2}],4:[function(require,module,exports){
'use strict';

exports.__esModule = true;
exports.replaceMetadataCertificateLinkTexts = replaceMetadataCertificateLinkTexts;
function replaceMetadataCertificateLinkTexts() {
    var $metadataCertificateLinks = document.querySelectorAll('dl.metadata-certificates-list a');

    for (var i = 0; i < $metadataCertificateLinks.length; i++) {
        var link = $metadataCertificateLinks[i];

        if (link.getAttribute('data-external-link') === "true") {
            continue;
        }

        // Set absolute URLs of anchors as display text
        link.textContent = window.location.protocol + '//' + window.location.hostname + link.getAttribute('href');
    }
}

},{}],5:[function(require,module,exports){
"use strict";

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var IdpFilter = exports.IdpFilter = function () {
    function IdpFilter() {
        _classCallCheck(this, IdpFilter);
    }

    IdpFilter.filterIdpsByValue = function filterIdpsByValue(filterSet, filterValue) {
        return filterSet.filter(function (idp) {
            return idp.title.indexOf(filterValue) > -1 || idp.entityId.indexOf(filterValue) > -1 || idp.keywords.indexOf(filterValue) > -1;
        });
    };

    return IdpFilter;
}();

},{}],6:[function(require,module,exports){
'use strict';

exports.__esModule = true;
exports.IdpList = undefined;

var _IdpFilter = require('./IdpFilter');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var IdpList = exports.IdpList = function () {
    function IdpList(targetElement, idpList, idpListElementFactory, cutoffPointForShowingUnfilteredIdps) {
        _classCallCheck(this, IdpList);

        this.targetElement = targetElement;
        this.idpList = idpList;
        this.idpListElementFactory = idpListElementFactory;

        this.filterValue = '';
        this.shouldHideUnfilteredIdpList = this.idpList.length > cutoffPointForShowingUnfilteredIdps;
        this.filteredIdpList = this.idpList;

        this.render();
    }

    IdpList.prototype.filterBy = function filterBy(filterValue) {
        this.filterValue = filterValue;
        this.filteredIdpList = _IdpFilter.IdpFilter.filterIdpsByValue(this.idpList, filterValue);
        this.render();
    };

    IdpList.prototype.narrowFilterBy = function narrowFilterBy(filterValue) {
        this.filterValue = filterValue;
        this.filteredIdpList = _IdpFilter.IdpFilter.filterIdpsByValue(this.filteredIdpList, filterValue);
        this.render();
    };

    IdpList.prototype.getFilteredIdpByIndex = function getFilteredIdpByIndex(index) {
        return this.filteredIdpList[index];
    };

    IdpList.prototype.addIdp = function addIdp(idp) {
        this.idpList.push(idp);
        this.sortIdpListAlphabetically();
        this.filteredIdpList = this.idpList;
        this.render();
    };

    IdpList.prototype.render = function render() {
        var $noResultsElement = this.targetElement.querySelector('.noresults');

        if (this.filteredIdpList.length === 0) {
            $noResultsElement.className = $noResultsElement.className.replace(' hidden', '');
        } else if ($noResultsElement.className.indexOf('hidden') === -1) {
            $noResultsElement.className += ' hidden';
        }

        if (this.filterValue.trim() === '' && this.shouldHideUnfilteredIdpList) {
            this.targetElement.className += ' hidden';

            // In order to maintain correct state of the list, render no elements in the page
            var _idpListElement = this.idpListElementFactory.createIdpListElementWithSelectionButtons([]);
            this.targetElement.replaceChild(_idpListElement, this.targetElement.querySelector('.idp-list'));

            return;
        }

        this.targetElement.className = this.targetElement.className.replace(' hidden', '');

        var idpListElement = this.idpListElementFactory.createIdpListElementWithSelectionButtons(this.filteredIdpList);

        this.targetElement.replaceChild(idpListElement, this.targetElement.querySelector('.idp-list'));
    };

    IdpList.prototype.sortIdpListAlphabetically = function sortIdpListAlphabetically() {
        this.idpList = this.idpList.sort(function (idpA, idpB) {
            if (idpA.title < idpB.title) {
                return -1;
            } else if (idpA.title > idpB.title) {
                return 1;
            }

            return 0;
        });
    };

    return IdpList;
}();

},{"./IdpFilter":5}],7:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var placeholderImage = '/images/placeholder.png';

var IdpListElementFactory = exports.IdpListElementFactory = function () {
    function IdpListElementFactory(messages) {
        _classCallCheck(this, IdpListElementFactory);

        this.messages = messages;
    }

    IdpListElementFactory.prototype.createIdpListElementWithSelectionButtons = function createIdpListElementWithSelectionButtons(idpList) {
        var idpListElement = document.createElement('div');
        idpListElement.className = 'idp-list';

        for (var i = 0; i < idpList.length; i++) {
            var idpElement = document.createElement('a');
            idpElement.className = 'result active';

            if (idpList[i].connected) {
                idpElement.className += ' access';
            } else {
                idpElement.className += ' noaccess';
            }

            idpElement.setAttribute('href', '#');
            idpElement.setAttribute('tabindex', '-1');

            var logoElement = document.createElement('img');
            logoElement.className = 'logo';
            setLogoAndFallbackUrlOnElement(idpList[i].logo, placeholderImage, logoElement);

            var titleElement = document.createElement('h3');
            titleElement.textContent = idpList[i].displayTitle;

            var actionElement = document.createElement('span');
            actionElement.className = 'c-button white action';

            if (idpList[i].connected) {
                actionElement.textContent = this.messages['pressEnterToSelect'];
            } else {
                actionElement.textContent = this.messages['requestAccess'];
            }

            idpElement.appendChild(logoElement);
            idpElement.appendChild(titleElement);
            idpElement.appendChild(actionElement);

            idpListElement.appendChild(idpElement);
        }

        return idpListElement;
    };

    IdpListElementFactory.prototype.createIdpListElementWithDeleteButtons = function createIdpListElementWithDeleteButtons(idpList) {
        var idpListElement = document.createElement('div');
        idpListElement.className = 'idp-list show-buttons';

        for (var i = 0; i < idpList.length; i++) {
            var idpElement = document.createElement('a');
            idpElement.className = 'result active access';
            idpElement.setAttribute('href', '#');
            idpElement.setAttribute('tabindex', '-1');

            var logoElement = document.createElement('img');
            logoElement.className = 'logo';
            logoElement.setAttribute('alt', '');
            setLogoAndFallbackUrlOnElement(idpList[i].logo, placeholderImage, logoElement);

            var titleElement = document.createElement('h3');
            titleElement.textContent = idpList[i].displayTitle;

            var actionElement = document.createElement('span');
            actionElement.className = 'c-button action outline deleteable img';

            var imageElement = document.createElement('img');
            imageElement.className = 'deleteable';
            imageElement.setAttribute('src', '/images/cross.svg');
            imageElement.setAttribute('alt', 'delete');
            actionElement.appendChild(imageElement);

            idpElement.appendChild(logoElement);
            idpElement.appendChild(titleElement);
            idpElement.appendChild(actionElement);

            idpListElement.appendChild(idpElement);
        }

        return idpListElement;
    };

    return IdpListElementFactory;
}();

var failedLogos = {};
function setLogoAndFallbackUrlOnElement(logoUrl, fallbackUrl, logoElement) {
    if (failedLogos[logoUrl] === true) {
        logoElement.setAttribute('src', fallbackUrl);
        return;
    }

    var logoErrorListener = function logoErrorListener() {
        logoElement.removeEventListener('error', logoErrorListener);
        logoElement.setAttribute('src', fallbackUrl);
        failedLogos[logoUrl] = true;
    };
    logoElement.addEventListener('error', logoErrorListener);
    logoElement.setAttribute('src', logoUrl);
}

},{}],8:[function(require,module,exports){
"use strict";

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var KeyboardListener = exports.KeyboardListener = function () {
    function KeyboardListener(idpPicker, searchBarElement, requestAccessModalHelper) {
        _classCallCheck(this, KeyboardListener);

        this.idpPicker = idpPicker;
        this.searchBarElement = searchBarElement;
        this.requestAccessModalHelper = requestAccessModalHelper;
    }

    KeyboardListener.prototype.handle = function handle(keyCode) {
        if (keyCode === ESCAPE) {
            this.requestAccessModalHelper.closeRequestAccessModal();
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
            this.idpPicker.selectIdpUnderFocus();
        }
    };

    return KeyboardListener;
}();

var ENTER = 13;
var ESCAPE = 27;
var ARROW_UP = 38;
var ARROW_DOWN = 40;

},{}],9:[function(require,module,exports){
'use strict';

exports.__esModule = true;
exports.PreviousSelectionList = undefined;

var _IdpFilter = require('./IdpFilter');

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var PreviousSelectionList = exports.PreviousSelectionList = function () {
    function PreviousSelectionList(targetElement, previousSelectionList, idpListElementFactory) {
        _classCallCheck(this, PreviousSelectionList);

        this.targetElement = targetElement;
        this.previousSelectionList = previousSelectionList;
        this.idpListElementFactory = idpListElementFactory;

        this.filteredPreviousSelectionList = this.previousSelectionList;
        this.editing = false;

        this.render();
    }

    PreviousSelectionList.prototype.getLengthOfFilteredList = function getLengthOfFilteredList() {
        return this.filteredPreviousSelectionList.length;
    };

    PreviousSelectionList.prototype.hasElement = function hasElement(element) {
        return this.targetElement.querySelector('.idp-list') === element.parentNode;
    };

    PreviousSelectionList.prototype.filterBy = function filterBy(filterValue) {
        this.filteredPreviousSelectionList = _IdpFilter.IdpFilter.filterIdpsByValue(this.previousSelectionList, filterValue);
        this.render();
    };

    PreviousSelectionList.prototype.narrowFilterBy = function narrowFilterBy(filterValue) {
        this.filteredPreviousSelectionList = _IdpFilter.IdpFilter.filterIdpsByValue(this.filteredPreviousSelectionList, filterValue);
        this.render();
    };

    PreviousSelectionList.prototype.getFilteredIdpByIndex = function getFilteredIdpByIndex(index) {
        return this.filteredPreviousSelectionList[index];
    };

    PreviousSelectionList.prototype.getPreviousSelections = function getPreviousSelections() {
        return this.previousSelectionList;
    };

    PreviousSelectionList.prototype.getListUpdatedWith = function getListUpdatedWith(idp) {
        var previousSelectionToUpdate = this.previousSelectionList.slice(0);
        var indexInPreviousSelection = previousSelectionToUpdate.indexOf(idp);

        if (indexInPreviousSelection === -1) {
            idp.count = 1;
            previousSelectionToUpdate.push(idp);
        } else {
            var idpToUpdate = previousSelectionToUpdate[indexInPreviousSelection];
            idpToUpdate.count++;
            previousSelectionToUpdate[indexInPreviousSelection] = idpToUpdate;
        }

        return previousSelectionToUpdate.sort(function (idpA, idpB) {
            return idpB.count - idpA.count;
        });
    };

    PreviousSelectionList.prototype.removeIdpByIndex = function removeIdpByIndex(index) {
        this.previousSelectionList.splice(index, 1);
        this.filteredPreviousSelectionList = this.previousSelectionList;
        this.render();
    };

    PreviousSelectionList.prototype.render = function render() {
        if (this.filteredPreviousSelectionList.length > 0) {
            this.targetElement.className = this.targetElement.className.replace(' hidden', '');
        } else if (this.targetElement.className.indexOf('hidden') === -1) {
            this.targetElement.className += ' hidden';
        }

        var idpListElement = void 0;
        if (this.editing === true) {
            idpListElement = this.idpListElementFactory.createIdpListElementWithDeleteButtons(this.filteredPreviousSelectionList);
        } else {
            idpListElement = this.idpListElementFactory.createIdpListElementWithSelectionButtons(this.filteredPreviousSelectionList);
        }

        this.targetElement.replaceChild(idpListElement, this.targetElement.querySelector('.idp-list'));
    };

    return PreviousSelectionList;
}();

},{"./IdpFilter":5}],10:[function(require,module,exports){
'use strict';

exports.__esModule = true;
exports.PreviousSelectionStorage = undefined;

var _jsCookie = require('js-cookie');

var Cookies = _interopRequireWildcard(_jsCookie);

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj['default'] = obj; return newObj; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var PreviousSelectionStorage = exports.PreviousSelectionStorage = function () {
    function PreviousSelectionStorage(cookieName) {
        _classCallCheck(this, PreviousSelectionStorage);

        this.cookieName = cookieName;
    }

    PreviousSelectionStorage.prototype.save = function save(previousSelection) {
        var simplifiedPreviousSelection = previousSelection.map(function (idp) {
            return {
                'idp': idp.entityId,
                'count': idp.count
            };
        });

        Cookies.set(this.cookieName, simplifiedPreviousSelection, { expires: 365, path: '/' });
    };

    return PreviousSelectionStorage;
}();

},{"js-cookie":13}],11:[function(require,module,exports){
'use strict';

exports.__esModule = true;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var RequestAccessModalHelper = exports.RequestAccessModalHelper = function () {
    function RequestAccessModalHelper(requestAccessElement, scrollerElement, searchBarElement, requestAccessUrl) {
        _classCallCheck(this, RequestAccessModalHelper);

        this.requestAccessElement = requestAccessElement;
        this.scrollerElement = scrollerElement;
        this.searchBarElement = searchBarElement;
        this.requestAccessUrl = requestAccessUrl;
    }

    RequestAccessModalHelper.prototype.openRequestAccessModal = function openRequestAccessModal(institutionName) {
        var _this = this;

        sendGetRequest(this.requestAccessUrl, function (responseText) {
            return _this.renderRequestAccessModal(responseText, institutionName);
        });
    };

    RequestAccessModalHelper.prototype.renderRequestAccessModal = function renderRequestAccessModal(responseText, institutionName) {
        document.body.style.overflowY = 'auto';
        this.requestAccessElement.innerHTML = responseText;

        this.scrollerElement.style.display = 'block';

        var $container = this.scrollerElement.querySelector('#request-access-container');
        var $closeModalButton = this.requestAccessElement.querySelector('.close-modal');
        var $submitButton = this.requestAccessElement.querySelector('#request_access_submit');
        var $nameField = this.requestAccessElement.querySelector('#name');
        var $institutionField = this.requestAccessElement.querySelector('#institution');

        $container.removeEventListener('click', this.containerClickHandler());
        $container.addEventListener('click', this.containerClickHandler($container));

        if ($submitButton !== null) {
            $submitButton.removeEventListener('click', this.submitRequestAccessClickHandler());
            $submitButton.addEventListener('click', this.submitRequestAccessClickHandler());
        }

        if ($closeModalButton !== null) {
            $closeModalButton.removeEventListener('click', this.closeModalClickHandler());
            $closeModalButton.addEventListener('click', this.closeModalClickHandler());
        }

        if (institutionName) {
            $institutionField.value = institutionName;
        }

        if ($nameField) {
            $nameField.focus();
        }
    };

    RequestAccessModalHelper.prototype.requestAccessClickHandler = function requestAccessClickHandler() {
        var _this2 = this;

        function isUnconnectedIdpRow(element) {
            return element.className.indexOf('noaccess') > -1;
        }

        return function (event) {
            if (isUnconnectedIdpRow(event.target) || isUnconnectedIdpRow(event.target.parentElement)) {
                var $institutionTitle = event.target.parentElement.querySelector('h3');
                var institutionName = '';

                if ($institutionTitle) {
                    institutionName = $institutionTitle.innerText;
                }

                _this2.openRequestAccessModal(institutionName);
            }
        };
    };

    RequestAccessModalHelper.prototype.containerClickHandler = function containerClickHandler(containerElement) {
        var _this3 = this;

        return function (event) {
            if (event.target === containerElement) {
                _this3.closeRequestAccessModal();
            }
        };
    };

    RequestAccessModalHelper.prototype.submitRequestAccessClickHandler = function submitRequestAccessClickHandler() {
        var _this4 = this;

        return function (event) {
            event.preventDefault();
            var $requestAccessForm = _this4.requestAccessElement.querySelector('#request_access_form');

            var formData = {
                name: $requestAccessForm.name.value,
                email: $requestAccessForm.email.value,
                institution: $requestAccessForm.institution.value,
                comment: $requestAccessForm.comment.value
            };

            sendPostRequest('/authentication/idp/performRequestAccess', formData, function (responseText) {
                _this4.renderRequestAccessModal(responseText);
            });
        };
    };

    RequestAccessModalHelper.prototype.closeModalClickHandler = function closeModalClickHandler() {
        var _this5 = this;

        return function (event) {
            event.preventDefault();
            _this5.closeRequestAccessModal();
        };
    };

    RequestAccessModalHelper.prototype.closeRequestAccessModal = function closeRequestAccessModal() {
        document.body.style.overflowY = null;
        this.scrollerElement.style.display = 'none';
        this.requestAccessElement.innerHTML = '';
        this.searchBarElement.focus();

        var $container = this.scrollerElement.querySelector('#request-access-container');
        $container.removeEventListener('click', this.containerClickHandler());
    };

    return RequestAccessModalHelper;
}();

function sendGetRequest(url, callback) {
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status == 200) {
            callback(request.responseText);
        }
    };

    request.open('GET', url, true);
    request.send(null);
}

function sendPostRequest(url, formData, callback) {
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200) {
            callback(request.responseText);
        }
    };

    var parts = [];

    for (name in formData) {
        parts.push(encodeURIComponent(name) + '=' + encodeURIComponent(formData[name]));
    }

    request.open('POST', url, true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.send(parts.join('&'));
}

},{}],12:[function(require,module,exports){
"use strict";

exports.__esModule = true;
exports.UnconnectedIdpPicker = undefined;

var _AbstractIdpPicker2 = require("./AbstractIdpPicker");

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var UnconnectedIdpPicker = exports.UnconnectedIdpPicker = function (_AbstractIdpPicker) {
  _inherits(UnconnectedIdpPicker, _AbstractIdpPicker);

  function UnconnectedIdpPicker() {
    _classCallCheck(this, UnconnectedIdpPicker);

    return _possibleConstructorReturn(this, _AbstractIdpPicker.apply(this, arguments));
  }

  return UnconnectedIdpPicker;
}(_AbstractIdpPicker2.AbstractIdpPicker);

},{"./AbstractIdpPicker":2}],13:[function(require,module,exports){
/*!
 * JavaScript Cookie v2.1.4
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
;(function (factory) {
	var registeredInModuleLoader = false;
	if (typeof define === 'function' && define.amd) {
		define(factory);
		registeredInModuleLoader = true;
	}
	if (typeof exports === 'object') {
		module.exports = factory();
		registeredInModuleLoader = true;
	}
	if (!registeredInModuleLoader) {
		var OldCookies = window.Cookies;
		var api = window.Cookies = factory();
		api.noConflict = function () {
			window.Cookies = OldCookies;
			return api;
		};
	}
}(function () {
	function extend () {
		var i = 0;
		var result = {};
		for (; i < arguments.length; i++) {
			var attributes = arguments[ i ];
			for (var key in attributes) {
				result[key] = attributes[key];
			}
		}
		return result;
	}

	function init (converter) {
		function api (key, value, attributes) {
			var result;
			if (typeof document === 'undefined') {
				return;
			}

			// Write

			if (arguments.length > 1) {
				attributes = extend({
					path: '/'
				}, api.defaults, attributes);

				if (typeof attributes.expires === 'number') {
					var expires = new Date();
					expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
					attributes.expires = expires;
				}

				// We're using "expires" because "max-age" is not supported by IE
				attributes.expires = attributes.expires ? attributes.expires.toUTCString() : '';

				try {
					result = JSON.stringify(value);
					if (/^[\{\[]/.test(result)) {
						value = result;
					}
				} catch (e) {}

				if (!converter.write) {
					value = encodeURIComponent(String(value))
						.replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
				} else {
					value = converter.write(value, key);
				}

				key = encodeURIComponent(String(key));
				key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
				key = key.replace(/[\(\)]/g, escape);

				var stringifiedAttributes = '';

				for (var attributeName in attributes) {
					if (!attributes[attributeName]) {
						continue;
					}
					stringifiedAttributes += '; ' + attributeName;
					if (attributes[attributeName] === true) {
						continue;
					}
					stringifiedAttributes += '=' + attributes[attributeName];
				}
				return (document.cookie = key + '=' + value + stringifiedAttributes);
			}

			// Read

			if (!key) {
				result = {};
			}

			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling "get()"
			var cookies = document.cookie ? document.cookie.split('; ') : [];
			var rdecode = /(%[0-9A-Z]{2})+/g;
			var i = 0;

			for (; i < cookies.length; i++) {
				var parts = cookies[i].split('=');
				var cookie = parts.slice(1).join('=');

				if (cookie.charAt(0) === '"') {
					cookie = cookie.slice(1, -1);
				}

				try {
					var name = parts[0].replace(rdecode, decodeURIComponent);
					cookie = converter.read ?
						converter.read(cookie, name) : converter(cookie, name) ||
						cookie.replace(rdecode, decodeURIComponent);

					if (this.json) {
						try {
							cookie = JSON.parse(cookie);
						} catch (e) {}
					}

					if (key === name) {
						result = cookie;
						break;
					}

					if (!key) {
						result[name] = cookie;
					}
				} catch (e) {}
			}

			return result;
		}

		api.set = api;
		api.get = function (key) {
			return api.call(api, key);
		};
		api.getJSON = function () {
			return api.apply({
				json: true
			}, [].slice.call(arguments));
		};
		api.defaults = {};

		api.remove = function (key, attributes) {
			api(key, '', extend(attributes, {
				expires: -1
			}));
		};

		api.withConverter = init;

		return api;
	}

	return init(function () {});
}));

},{}]},{},[1]);
