var OpenConext = {};
OpenConext.Discover = function () {
  'use strict';
  var $results = $('.mod-results');
  var $searchBar = $('.mod-search-input');
  var $previousIdpChoices = $('#preselection');
  var $idpChoices = $('#selection');

  function hasDiscovery() {
    return $searchBar.length !== 0;
  }

  function checkListVisible(listContainer) {
    listContainer.toggleClass('hidden', listContainer.find('.list .active').length === 0);
  }

  function checkVisible() {
    checkListVisible($previousIdpChoices);
  }

  function checkNoResults() {
    var selectionContainer = $idpChoices,
      noResultsContainer = selectionContainer.find('.noresults'),
      hasActiveResults = selectionContainer.find('.list a.active').length > 0;

    noResultsContainer.toggleClass('hidden', hasActiveResults);
    $('#noselection .noresults').toggleClass('hidden', !hasActiveResults);
  }

  function filterList(filterValue) {
    fakeFocusFirstIdP();

    var filterElements = $('.mod-results a.result'),
        spinner = $('.mod-results .spinner'),
        containsFilterValue;

    spinner.removeClass('hidden');

    filterValue = filterValue.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '').toLowerCase();
    containsFilterValue = function (keyword) {
      return keyword.toLowerCase().indexOf(filterValue) !== -1;
    };

    filterElements.each(function () {
      var result = $(this),
          title = result.find('h3').text().toLowerCase(),
          match = title.indexOf(filterValue) !== -1;

      if (!match) {
        match = match || _.some(result.data('keywords'), containsFilterValue);
      }

      result
          .toggleClass('active', match)
          .toggleClass('hidden', !match);
    });

    checkVisible();
    checkNoResults();
    spinner.addClass('hidden');

    // trigger the resize event to lazyload images
    $(window).trigger('resize');
  }

  function keyNavigation(e) {
    var currentElement = $(document.activeElement),
      list = $('.list'),
      key = e.which;

    $results.removeClass('mouse-nav');

    function pressEnter() {
      e.preventDefault();
      // Press enter on searchbox
      if (currentElement.hasClass('mod-search-input')) {
        list.find('.active:first').first().focus().trigger('click');
      }

      // Press enter on result
      if (currentElement.attr('href') === '#') {
        currentElement.trigger('click');
      }
    }

    function pressDownArrow() {
      removeFakeFocusFirstIdP();

      // moving from the searchbox
      if (currentElement.closest('.active').next().attr('type') === 'hidden') {
        var nextElement = list.find('.active:first');

        if (nextElement.length > 0) {
          nextElement[0].focus();
        }
        return;
      }

      // moving in a list
      if (currentElement.nextAll('.active:first').length > 0) {
        currentElement.nextAll('.active:first').focus();
        return;
      }

      // moving to next list
      if (currentElement.parent().parent().next().find('.active:first').length > 0) {
        currentElement.parent().parent().next().find('.active:first').focus();
      }
    }

    function pressUpArrow() {
      // move up in list
      if (currentElement.prevAll('.active:first').length > 0) {
        currentElement.prevAll('.active:first').focus();
        return;
      }

      // move up in previous list
      if (currentElement.parent().parent().prev().find('.active:last').length > 0) {
        currentElement.parent().parent().prev().find('.active:last').focus();
        return;
      }

      // move up to search
      if (currentElement.prevAll('.active:first').length === 0) {
        $('input.mod-search-input').focus();
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

  function fakeFocusFirstIdP() {
    removeFakeFocusFirstIdP();
    $('.result.active').first().addClass('focussed');
  }
  function removeFakeFocusFirstIdP() {
    $('.result').removeClass('focussed');
  }

  if (!hasDiscovery()) {
    return this;
  }

  var selectedIdps = JSON.parse(Cookies.get('selectedidps') || '[]') || [];

  if (selectedIdps.length > 0 && selectedIdps[0].idp === void 0) {
    // Clear the array if it does not yet use the new format which includes the count.
    selectedIdps = [];
  }

  // Transforms [100, 2, 40] => [2, 40, 100] => { 2: 0, 40: 1, 100: 2 }
  var normalisedIdpCounts = _.chain(selectedIdps).pluck('count').uniq().sort().invert().value();

  selectedIdps = _.map(selectedIdps, function (idp) {
    // Enables IdPs to more easily take over the top position (most-used), ie. they can't get too far apart.
    return { idp: idp.idp, count: parseInt(normalisedIdpCounts[idp.count]) + 1 };
  });

  // Populate list of previously chosen IdPs based on "selectedidps" cookie.
  _.chain(selectedIdps)
      .map(function (selectedIdp) {
        return {
          idp: $('#selection a[data-idp]').filter(function () {
            return $(this).data('idp') === selectedIdp.idp;
          }),
          count: selectedIdp.count
        };
      })
      .sortBy('count')
      .reverse()
      .pluck('idp')
      .each(function (idpElement) {
        $('#preselection .list').append(idpElement);
      });

  function initRequestAccessModal(html) {
    $('#request-access').html(html);
    $(document.body).css({ overflowY: 'hidden'});
    $('#request-access-scroller').show().data('opened', true);

    $('.close-modal').on('click', function closeModal(e) {
      e.preventDefault();
      deinitRequestAccessModal();
    });
    $('#name').focus();

    $('#request_access_submit').on('click', function (e) {
      e.preventDefault();
      var formData = $('#request_access_form').serialize();
      $.post('/authentication/idp/performRequestAccess', formData)
        .done(function (data) {
          initRequestAccessModal(data);
        });
    });
  }

  function deinitRequestAccessModal() {
    $('#request-access-scroller').hide();
    $(document.body).css({ overflowY: 'auto'});
    $('#request-access').html('');
    $('input.mod-search-input').focus();
  }

  $(document.body).on('click', '#request-access-scroller, #request-access-container', function (e) {
    if (e.target !== this) return;

    deinitRequestAccessModal();
  });
  $(document.body).on('keydown', function (e) {
    if (e.which !== 27 || e.altKey || e.ctrlKey || e.metaKey) return;
    if ($('#request-access-scroller').data('opened') === false) return;

    deinitRequestAccessModal();
  });

  checkVisible();
  checkNoResults();

  // Listening to multiple events: 'input' for changes in input text, except for IE, which does not register character
  // deletions; 'keyup' for character deletions in IE; 'mouseup' for clicks on IE's native input clear button. The
  // 0-millisecond timeout is there to retrieve the input value after 'mouseup' events.
  $searchBar.on('input keyup mouseup', function inputDetected(event) {
    setTimeout(function () {
      filterList($(event.target).val());
    }, 0);
  });

  var searchValue = $('input.mod-search-input').val();

  if (searchValue) {
    filterList(searchValue);
  }

  $('.mod-results a.edit').on('click', function editPreselection(e) {
    var editLink = $(this),
      mode = editLink.attr('data-toggle'),
      removeables = $('#preselection .c-button'),
      list = $('#preselection .list'),
      item, x, swapText;

    e.preventDefault();

    if (mode === 'view') {
      list.addClass('show-buttons');

      swapText = editLink.attr('data-toggle-text');
      editLink.attr('data-toggle-text', editLink.html());
      editLink.html(swapText);

      for (x = 0; x < removeables.length; x++) {
        item = $(removeables[x])
          .removeClass('white')
          .addClass('outline deleteable img');

        swapText = item.attr('data-toggle-text');
        item.attr('data-toggle-text', item.html());
        item.html(swapText);
      }
      editLink.attr('data-toggle', 'edit');
    } else {
      list.removeClass('show-buttons');

      swapText = editLink.attr('data-toggle-text');
      editLink.attr('data-toggle-text', editLink.html());
      editLink.html(swapText);

      for (x = 0; x < removeables.length; x++) {
        item = $(removeables[x])
          .removeClass('outline deleteable img')
          .addClass('white');

        swapText = item.attr('data-toggle-text');
        item.attr('data-toggle-text', item.html());
        item.html(swapText);
      }

      editLink.attr('data-toggle', 'view');
    }
  });

  $('a.noaccess').on('click', function requestAccess(e) {
    e.preventDefault();
    var noAccessLink = $(this),
      idpEntityId = noAccessLink.attr('data-idp') || 'unknown',
      idpName = noAccessLink.text(),
      params = {
        lang: discover.lang,
        idpEntityId: idpEntityId,
        idpName: idpName,
        spEntityId: discover.spEntityId,
        spName: discover.spName
      };

    $.get('/authentication/idp/requestAccess?' + $.param(params), function (data) {
      initRequestAccessModal(data);
    });
  });

  $('.mod-results a.result.access').on('click', function handleIdpAction(e) {
    var accessLink = $(this),
      idp = accessLink.attr('data-idp'),
      saveIndex = _.findIndex(selectedIdps, function (selectedIdp) {
        return selectedIdp.idp === idp;
      }),
      selectionInsertionPoint,
      accessLinkIdpName;

    if ($(e.target).hasClass('deleteable')) {
      e.stopPropagation();
      e.preventDefault();

      if (saveIndex !== -1) {
        // Remove the previously selected IdP from the entity ID list.
        selectedIdps.splice(saveIndex, 1);
        // We find out where to move the IdP in the "normal" IdP list by comparing names...
        accessLinkIdpName = accessLink.find('h3').text();
        selectionInsertionPoint = $('#selection .list .result h3')
          .filter(function () {
            return $(this).text() < accessLinkIdpName;
          })
          .last()
          .parents('.result').first();
        if (selectionInsertionPoint.length === 0) {
          selectionInsertionPoint = $('#selection .list .spinner');
        }
        // ... and move the IdP there, swapping the delete button for its "Press enter to select" button.
        accessLink
          .insertAfter(selectionInsertionPoint)
          .each(function () {
            var action = $(this).find('.action');
            var swapText = action.attr('data-toggle-text');
            action.attr('data-toggle-text', action.html());
            action.html(swapText).removeClass('outline deleteable img').addClass('white');
          });
        checkVisible();
      }
    } else {
      if (saveIndex === -1) {
        selectedIdps.push({idp: idp, count: 1});
      } else {
        ++selectedIdps[saveIndex].count;
      }

      $('#form-idp').val(idp);
      $('form.mod-search').submit();
    }

    Cookies.set('selectedidps', JSON.stringify(selectedIdps), { expires: 365, path: '/' });
  });

  $('body').on('keydown', keyNavigation);
  $results.on('mouseenter mouseleave', '.result', function () {
    $(document.activeElement).filter('.result').blur();
    removeFakeFocusFirstIdP();
  });
  $('img.logo').lazyload();
  $searchBar.on('focus', function() {
    var val = this.value;
    var $this = $(this);
    $this.val("");
    setTimeout(function () {
      $this.val(val);
    }, 1);

    fakeFocusFirstIdP();
  });

  // on desktop devices set the focus
  if (window.innerWidth > 800) {
    $searchBar.focus();
  }
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
    $('.comp-language a').each(function(){
      var val = $(this).attr('href'),
        newVal = val.split('#')[0];
      $(this).attr('href', newVal + '#' + activeTabId);
    });
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

OpenConext.Profile = function () {
  $(document.body).on('click', '#MyAppsTable .sp-row .sp-display-name', function (event) {
    event.preventDefault();

    $(this).parents('.sp-row')
        .next('.attribute-table-row')
        .toggleClass('attribute-table-row-expanded');
  });
};

$('html').removeClass('no-js');
$(function init() {

  new OpenConext.Tabs();
  new OpenConext.Discover();
  new OpenConext.Profile();
  FastClick.attach(document.body);

  if ($('#engine-main-page').length > 0) {
      // set absolute URLs of anchors as display text
      $('dl.metadata-certificates-list a').not('a[data-external-link=true]').each(function(){
        $(this).text(
          window.location.protocol + '//' + window.location.hostname + $(this).attr('href')
        );
      });
  }

  $('#delete-confirmation-form').submit(function() {
    return confirm($('#delete-confirmation-text').attr('data-confirmation-text'));
  });
});

