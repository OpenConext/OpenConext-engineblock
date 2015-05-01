'use strict';

var SurfConext = {};
SurfConext.Tabs = function() {
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
        } else if (null !== typeof firstTab) {
            activeTab = firstTab.getAttribute('href');
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
    setTab(this.activeTab.replace('#', ''));
    window.addEventListener('hashchange', function(){
        setTab(getActiveTab().replace('#', ''));
    }, false);
};

SurfConext.Tabs();