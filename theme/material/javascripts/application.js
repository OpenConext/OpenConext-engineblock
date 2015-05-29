var OpenConext = {};
OpenConext.Discover = function () {
  'use strict';
  this.searchBar = $('.mod-search-input');

  function hasDiscovery() {
    return 0 !== $('.mod-search-input').length;
  }

  function checkSuggestedVisible() {
    var preselection = $('#preselection'),
      suggestedBox = $('#preselection .list');

    if (suggestedBox.children().length === 0) {
      preselection.addClass('hidden');
    } else {
      preselection.removeClass('hidden');
    }
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

  function keyNavigation(e) {
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

  var cookieIdps = Cookies.get('selectedidps') || '[]',
    selectedIdps = JSON.parse(cookieIdps) || [],
    listedIdp;

  if (selectedIdps.length > 0) {
    for (var j = 0; j < selectedIdps.length; j++) {
      listedIdp = $('#selection a[data-idp=\'' + selectedIdps[j] + '\']');
      if (listedIdp) {
        $('#preselection .list').append(listedIdp);
      }
    }
  }

  checkSuggestedVisible();

  this.searchBar.on('input', function inputDetected() {
    filterList($(this).val());
  });

  var searchValue = $('input[type=search]').val();

  if (searchValue !== '') {
    filterList(searchValue);
  }

  $('.mod-results a.edit').on('click', function editPreselection(e) {
    var mode = $(this).attr('data-toggle'),
      removeables = $('#preselection .c-button'),
      item, x, swapText;

    e.preventDefault();

    if (mode === 'view') {
      $('#preselection .list').addClass('show-buttons');

      swapText = $(this).attr('data-toggle-text');
      $(this).attr('data-toggle-text', $(this).text());
      $(this).text(swapText);

      for (x = 0; x < removeables.length; x++) {
        item = $(removeables[x]);
        item.removeClass('white');
        item.addClass('outline');
        item.addClass('deleteable');

        swapText = item.attr('data-toggle-text');
        item.attr('data-toggle-text', item.text());
        item.text(swapText);
      }
      $(this).attr('data-toggle', 'edit');
    } else {
      $('#preselection .list').removeClass('show-buttons');

      swapText = $(this).attr('data-toggle-text');
      $(this).attr('data-toggle-text', $(this).text());
      $(this).text(swapText);

      for (x = 0; x < removeables.length; x++) {
        item = $(removeables[x]);
        item.removeClass('outline');
        item.removeClass('deleteable');
        item.addClass('white');

        swapText = item.attr('data-toggle-text');
        item.attr('data-toggle-text', item.text());
        item.text(swapText);
      }

      $(this).attr('data-toggle', 'view');
    }
  });


  $('.mod-results a.result').on('click', function handleIdpAction(e) {
    var selectedIdp = $(this).attr('data-idp');

    if ($(e.target).hasClass('deleteable')) {
      var saveIndex = $.inArray(selectedIdp, selectedIdps);
      if (saveIndex > -1) {
        selectedIdps.splice(saveIndex, 1);
        $(this).slideUp('fast', function() {
          $(this).remove();
          checkSuggestedVisible();
        });
      }
    } else {
      $('#form-idp').val(selectedIdp);

      if ($.inArray(selectedIdp, selectedIdps) === -1) {
        selectedIdps.push(selectedIdp);
      }
      $('form.mod-search').submit();
    }

    Cookies.set('selectedidps', JSON.stringify(selectedIdps));
  });

  $('body').on('keydown', keyNavigation);
  $('img.logo').lazyload();
};

OpenConext.Tabs = function () {
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
    $('.tab-target[href="#' + activeTabId + '"]').addClass('active');
  }

  if (!hasTabs()) {
    return this;
  }

  this.activeTab = getActiveTab();

  if (this.activeTab !== '') {
    setTab(this.activeTab.replace('#', ''));
    window.addEventListener('hashchange', function tabActivated() {
      setTab(getActiveTab().replace('#', ''));
    }, false);
  }
};

$(function init() {
  new OpenConext.Tabs();
  new OpenConext.Discover();
});