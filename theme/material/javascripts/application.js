var OpenConext = {};
OpenConext.Discover = function () {
  'use strict';
  var lastMouseX = null,
    lastMouseY = null;
  this.searchBar = $('.mod-search-input');

  function hasDiscovery() {
    return 0 !== $('.mod-search-input').length;
  }

  function checkListVisible(listContainer) {
    var list = listContainer.find('.list:first');

    if (list.children().length === 0 || list.find('.active').length === 0) {
      listContainer.addClass('hidden');
    } else {
      listContainer.removeClass('hidden');
    }

  }

  function checkVisible() {
    checkListVisible($('#preselection'));
    checkListVisible($('#noselection'));
  }

  function checkNoResults() {
    var selectionContainer = $('#selection'),
      noResultsContainer = selectionContainer.find('.noresults');

    if (selectionContainer.find('.list a.active').length === 0) {
      noResultsContainer.removeClass('hidden');
    } else {
      noResultsContainer.addClass('hidden');
    }
  }

  function filterList(filterValue) {
    var filterElements = $('.mod-results a.result'),
      spinner = $('.mod-results .spinner'),
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

    checkVisible();
    checkNoResults();
    setFocusClass();
    spinner.addClass('hidden');

    // trigger the resize event to lazyload images
    $(window).trigger('resize');
  }

  function mouseNavigation(e) {
    removeFocusClass();

    if (
      (lastMouseX === null && lastMouseY === null) ||
      (lastMouseX === e.clientX && lastMouseY === e.clientY)
    ) {
      lastMouseX = e.clientX;
      lastMouseY = e.clientY;
      return;
    }

    lastMouseX = e.clientX;
    lastMouseY = e.clientY;

    $('.mod-results').addClass('mouse-nav');
  }

  function keyNavigation(e) {
    var currentElement = $(document.activeElement),
      list = $('.list'),
      key = e.which;

    $('.mod-results').removeClass('mouse-nav');
    function pressEnter() {
      e.preventDefault();
      // Press enter on searchbox
      if (currentElement.hasClass('mod-search-input') && list.find('.active:first').length > 0) {
        var firstEl = list.find('.active:first');
        if (firstEl.length > 0) {
          firstEl.focus().trigger('click');
        }
      }

      // Press enter on result
      if (currentElement.attr('href') === '#') {
        currentElement.trigger('click');
      }
    }

    function pressDownArrow() {
      // moving from the searchbox
      if (currentElement.closest('.active').next().attr('type') === 'hidden') {
        var nextElement = list.find('.active:first');

        if (nextElement.length > 0) {
          nextElement[0].focus();
          removeFocusClass();
        }
        return;
      }

      // moving in a list
      if (currentElement.nextAll('.active:first').length > 0) {
        currentElement.nextAll('.active:first').focus();
        removeFocusClass();
        return;
      }

      // moving to next list
      if (currentElement.parent().parent().next().find('.active:first').length > 0) {
        currentElement.parent().parent().next().find('.active:first').focus();
        removeFocusClass();
      }
    }

    function pressUpArrow() {
      // move up in list
      if (currentElement.prevAll('.active:first').length > 0) {
        currentElement.prevAll('.active:first').focus();
        removeFocusClass();
        return;
      }

      // move up in previous list
      if (currentElement.parent().parent().prev().find('.active:last').length > 0) {
        currentElement.parent().parent().prev().find('.active:last').focus();
        removeFocusClass();
        return;
      }

      // move up to search
      if (currentElement.prevAll('.active:first').length === 0) {
        $('input[type=search]').focus();
      }
    }

    switch (key) {
      case 13:
        pressEnter();
        break;
      case 40:
        pressDownArrow();
        break;
      case 38:
        pressUpArrow();
        break;
    }

  }

  function removeFocusClass() {
    var focussedElement = $('.result.focussed');

    if (focussedElement.length > 0) {
      focussedElement.removeClass('focussed');
    }
  }

  function setFocusClass() {
    var firstActive = $('.result.active:first');

    removeFocusClass();

    if (firstActive.length > 0) {
      firstActive.addClass('focussed');
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

  function initModal(modal) {
    $('.close-modal').on('click', function closeModal(e) {
      e.preventDefault();
      $('#request-access').trigger('closeModal');
    });
    $('#name').focus();

    $('#request_access_submit').on('click', function (e) {
      e.preventDefault();
      var formData = $('#request_access_form').serialize();
      $.post('/authentication/idp/performRequestAccess', formData)
        .done(function (data) {
          $("#request-access").html(data);
          initModal(modal);
        });
    });
  }

  checkVisible();
  checkNoResults();
  setFocusClass();
  $('#request-access').easyModal({
    onOpen: initModal,
    onClose: function() {
      $('#request-access').html('');
      $('input[type=search]').focus();
    }
  });

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
      list = $('#preselection .list'),
      item, x, swapText;

    e.preventDefault();

    if (mode === 'view') {
      list.addClass('show-buttons');

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
      list.removeClass('show-buttons');

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

  $('a.noaccess').on('click', function requestAccess(e) {
    e.preventDefault();
    var idpEntityId = $(this).attr('data-idp') || 'unknown',
      idpName = $(this).text(),
      params = {
        lang: discover.lang,
        idpEntityId: idpEntityId,
        idpName: idpName,
        spEntityId: discover.spEntityId,
        spName: discover.spName
      };

    $.get('/authentication/idp/requestAccess?' + $.param(params), function (data) {
      var requestAccess = $('#request-access');
      requestAccess.html(data);
      requestAccess.trigger('openModal');
    });
  });

  $('.mod-results a.result.access').on('click', function handleIdpAction(e) {
    var selectedIdp = $(this).attr('data-idp');

    if ($(e.target).hasClass('deleteable')) {
      var saveIndex = $.inArray(selectedIdp, selectedIdps);
      if (saveIndex > -1) {
        selectedIdps.splice(saveIndex, 1);
        $(this).slideUp('fast', function() {
          $(this).remove();
          checkVisible();
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
  $('.mod-results').on('mousemove', mouseNavigation);
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