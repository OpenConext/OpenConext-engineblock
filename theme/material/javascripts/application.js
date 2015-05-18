var OpenConext = {};
OpenConext.Discover = function() {
    'use strict';
    this.searchBar = $('.mod-search-input');

    function hasDiscovery() {
        return null !== typeof $('.mod-search-input');
    }

    function filterList(filterValue) {
        var filterElements = $('.mod-results a'),
            searchHeader = $('.mod-results header'),
            i, result, title;

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
    console.log(searchValue);
    if (searchValue !== '') {
        filterList(searchValue);
    }

    $('.mod-results a').on('click', function selectIdp(){
       var selectedIdp = $(this).attr('data-idp');
        $('#form-idp').val(selectedIdp);
        $('form.mod-search').submit();
    });

    $('body').on('keydown', keyNavigation);
};

OpenConext.Tabs = function() {
    'use strict';
    this.activeTab = '';

    function hasTabs() {
        return null !== typeof document.querySelector('.mod-tabs');
    }

    function getActiveTab() {
        var firstTab = document.querySelector('.mod-tabs .tab-target'),
            currentHash = window.location.hash,
            activeTab;

        if (currentHash !== '') {
            activeTab = currentHash;
        } else if (null !== firstTab) {
            activeTab = firstTab.getAttribute('href');
        } else {
            activeTab = '';
        }

        return activeTab;
    }

    function setTab(activeTabId) {
        var tabPanels = document.querySelectorAll('.mod-tabpanel'),
            panel, panelId, panelClass, i;

        for (i = 0; i < tabPanels.length; i++) {
            panel = tabPanels[i];
            panelId = panel.getAttribute('id');
            panelClass = panel.getAttribute('class');

            if (panelId === activeTabId) {
                panelClass = panelClass.replace('mod-tabpanel', 'mod-tabpanel active');
            } else {
                panelClass = panelClass.replace('active', '');
            }

            panel.setAttribute('class', panelClass);
        }
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