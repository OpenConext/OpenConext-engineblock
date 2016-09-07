$(function init() {
    $('html').removeClass('no-js');
    FastClick.attach(document.body);

    var $searchBar = $('.mod-search-input');
    var $idpChoices = $('.mod-results');
    var $editPreselectionButton = $('.mod-results a.edit');
    var $accessibleIdps = $('.mod-results a.result.access');
    var $documentBody = $(document.body);
    var $requestAccessButton = $('a.noaccess');
    var $idpLogos = $('img.logo');

    var possibleIdpChoices = new PossibleIdpChoices();
    possibleIdpChoices.setPreviousIdpChoices(PreviousIdpChoices.fromJson(Cookies.get('selectedidps') || '[]'));
    possibleIdpChoices.render();

    var keyboardListener = new KeyboardListener();
    $documentBody.on('keydown', keyboardListener.onKeyDown);

    $idpLogos.lazyload();

    $searchBar.on('focus', function () {
        possibleIdpChoices.focusOnFirstIdp();
    });

    // on desktop devices set the focus
    if (window.innerWidth > 800) {
        $searchBar.focus();
    }

    // Listening to multiple events for crossbrowser capabilities
    // 0-millisecond timeout is there to retrieve the input value after 'mouseup' events.
    $searchBar.on('input keyup mouseup', function inputDetected(event) {
        setTimeout(function () {
            possibleIdpChoices.filterBy($(event.target).val());
        }, 0);
    });

    $searchBar.on('blur', function () {
        if ($('.result.focussed').length > 0) {
            $(document.activeElement).filter('.result').blur();
            $('.result').removeClass('focussed');
        }
    });

    $editPreselectionButton.on('click', function editPreselection(event) {
        event.preventDefault();
        possibleIdpChoices.editPreviousIdpChoices($(this));
    });

    $accessibleIdps.on('click', function handleIdpAction(event) {
        possibleIdpChoices.handleIdpClickedEvent(event, $(this));
    });

    $idpChoices.on('mouseenter mouseleave', '.result', function () {
        $(document.activeElement).filter('.result').blur();
        $('.result').removeClass('focussed');
    });

    var requestAccessModalHelper = new RequestAccessModalHelper;
    $documentBody.on('click', '#request-access-scroller, #request-access-container', function (e) {
        if (e.target !== this) {
            return;
        }

        requestAccessModalHelper.closeRequestAccessModal();
    });

    $documentBody.on('keydown', function (e) {
        if (e.which !== 27 || e.altKey || e.ctrlKey || e.metaKey) {
            return;
        }

        if ($('#request-access-scroller').data('opened') === false) {
            return;
        }

        requestAccessModalHelper.closeRequestAccessModal();
    });

    $requestAccessButton.on('click', function requestAccess(event) {
        event.preventDefault();

        var params = $.param({
            lang: discover.lang,
            idpEntityId: $(this).attr('data-idp') || 'unknown',
            idpName: $(this).text(),
            spEntityId: discover.spEntityId,
            spName: discover.spName
        });

        $.get('/authentication/idp/requestAccess?' + params, function (data) {
            requestAccessModalHelper.openRequestAccessModal(data);
        });
    });

    if ($('#engine-main-page').length > 0) {
        // set absolute URLs of anchors as display text
        $('dl.metadata-certificates-list a').not('a[data-external-link=true]').each(function () {
            $(this).text(
                window.location.protocol + '//' + window.location.hostname + $(this).attr('href')
            );
        });
    }

    $('#delete-confirmation-form').submit(function () {
        return confirm($('#delete-confirmation-text').attr('data-confirmation-text'));
    });
});
