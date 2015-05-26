var OpenConext = {};
OpenConext.Discover = function() {
    'use strict';
    this.searchBar = $('.mod-search-input');

    function hasDiscovery() {
        return 0 !== $('.mod-search-input').length;
    }

    function filterList(filterValue) {
        var filterElements = $('.mod-results a'),
            searchHeader = $('.mod-results header'),
            spinner = $('.mod-results .loading'),
            i, result, title;

        spinner.removeClass('hidden');

        filterValue = filterValue.toLowerCase();

        if (filterValue !== '') {
            searchHeader.hide();
        } else {
            searchHeader.show();
        }

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
            nextElement;

        // Press down
        if (e.which === 40) {
            if (currentElement.closest('.active').next().attr('type') === 'hidden') {
                nextElement = $('.list').find('.active:first');
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