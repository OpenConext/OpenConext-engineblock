var OpenConext = {};
OpenConext.Discover = function() {
    'use strict';
    this.searchBar = $('.mod-search-input');

    function hasDiscovery() {
        return 0 !== $('.mod-search-input').length;
    }

    function filterList(filterValue) {
        var filterElements = $('.mod-results a'),
            spinner = $('.mod-results .loading'),
            i, result, title;

        spinner.removeClass('hidden');

        filterValue = filterValue.toLowerCase();

        for (i = 0; i < filterElements.length; i++) {
            result = $(filterElements[i]);
            title = $(result).find('h3').text().toLowerCase();

            if (title.toString().indexOf(filterValue.toString()) > -1) {
                result.addClass('active');
                result.removeClass('hidden');
            } else {
                result.addClass('hidden');
                result.removeClass('active');
            }
        }

        spinner.addClass('hidden');

        // trigger the resize event to lazyload images
        $(window).trigger('resize');
    }

    function keyNavigation(e){
        var currentElement = $(document.activeElement),
            list = $('.list'),
            nextElement;

        // Press enter in searchbox
        if (
          e.which === 13 &&
          currentElement.hasClass('mod-search-input') &&
          list.find('.active:first').length > 0
        ) {
          list.find('.active:first').focus().trigger('click');
        }

        // Press enter on result
        if (e.which === 13 && currentElement.attr('href') === '#') {
          e.preventDefault();
          currentElement.trigger('click');
        }

        // Press down
        if (e.which === 40) {
            if (currentElement.closest('.active').next().attr('type') === 'hidden') {
                nextElement = list.find('.active:first');
                if (nextElement.length > 0) {
                    nextElement[0].focus();
                }
            } else if (currentElement.nextAll('.active:first')) {
                currentElement.nextAll('.active:first').focus();
            }
        }

        // Press up
        if (e.which === 38) {
            if (currentElement.prevAll('.active:first').length === 0) {
                $('input[type=search]').focus();
            } else if (currentElement.prevAll('.active:first')) {
                currentElement.prevAll('.active:first').focus();
            }
        }
    }

    if (!hasDiscovery()) {
        return this;
    }

    //var selectedIdps = JSON.parse(Cookies.get('selectedidps')) || [],
    //    hasSuggested = false,
    //    listedIdp;
    //
    //if (selectedIdps.length > 0) {
    //  for (var j = 0; j < selectedIdps.length; j++) {
    //    listedIdp = $('#selection a[data-idp=\'' + selectedIdps[j] + '\']');
    //    if (listedIdp) {
    //      $('#preselection .list').append(listedIdp);
    //      hasSuggested = true;
    //    }
    //  }
    //}

    //if (hasSuggested === true) {
    //  $('#preselection').removeClass('hidden');
    //}

    Cookies.set('selectedidps', JSON.stringify(['http://federation.hanze.nl/adfs/services/trust', 'http://federatie.graafschapcollege.nl/adfs/services/trust']));


    this.searchBar.on('input', function inputDetected(){
        filterList($(this).val());
    });

    var searchValue = $('input[type=search]').val();

    if (searchValue !== '') {
        filterList(searchValue);
    }

    $('.mod-results a').on('click', function selectIdp(){
      var selectedIdp = $(this).attr('data-idp');

      $('#form-idp').val(selectedIdp);
      $('form.mod-search').submit();
    });

    $('body').on('keydown', keyNavigation);
    $('img.logo').lazyload();
};

OpenConext.Tabs = function() {
    'use strict';
    this.activeTab = '';

    function hasTabs() {
      return 0 !== $('.mod-tabs').length;
    }

    function getActiveTab() {
        var firstTab = $('.mod-tabs .tab-target')[0],
            currentHash = window.location.hash,
            activeTab;

        if (currentHash !== '') {
            activeTab = currentHash;
        } else if (null !== firstTab) {
            activeTab = $(firstTab).attr('href');
        } else {
            activeTab = '';
        }

        return activeTab;
    }

    function setTab(activeTabId) {
        var tabPanels = $('.mod-tabpanel'),
            panel, panelId, i;

        for (i = 0; i < tabPanels.length; i++) {
            panel = $(tabPanels[i]);
            panelId = panel.attr('id');

            if (panelId === activeTabId) {
                panel.addClass('active');
            } else {
                panel.removeClass('active');
            }
        }
        $('.tab-target').removeClass('active');
        $('.tab-target[href="#'+activeTabId+'"]').addClass('active');
    }

    if (!hasTabs()) {
        return this;
    }

    this.activeTab = getActiveTab();

    if (this.activeTab !== '') {
        setTab(this.activeTab.replace('#', ''));
        window.addEventListener('hashchange', function tabActivated(){
            setTab(getActiveTab().replace('#', ''));
        }, false);
    }
};

$(function init(){
    new OpenConext.Tabs();
    new OpenConext.Discover();
});