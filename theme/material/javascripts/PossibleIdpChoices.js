var PossibleIdpChoices = function () {
    this.setPreviousIdpChoices = function (previousIdpChoices) {
        this.previousIdpChoices = previousIdpChoices;
    };

    this.focusOnFirstIdp = function () {
        $('.result').removeClass('focussed');
        $('.result.active').first().addClass('focussed');
    };

    this.render = function () {
        this.previousIdpChoices.renderIn($('#preselection .list'));

        var $previousIdpChoices = $('#preselection');
        var $idpChoices         = $('#selection');
        var $noResultsContainer = $idpChoices.find('.noresults');

        var hasActivePreviousIdpChoices = $previousIdpChoices.find('.list a.active').length === 0;
        var hasActiveIdpChoices         = $idpChoices.find('.list a.active').length > 0;

        $previousIdpChoices.toggleClass('hidden', hasActivePreviousIdpChoices);
        $noResultsContainer.toggleClass('hidden', hasActiveIdpChoices);

        $('#noselection .noresults').toggleClass('hidden', !hasActiveIdpChoices);

        this.focusOnFirstIdp();
    };

    this.filterBy = function (filterValue) {
        var filterElements = $('.mod-results a.result');
        var spinner = $('.mod-results .spinner');
        var containsFilterValue;

        spinner.removeClass('hidden');

        // Trim BOM and NBSP for certain browsers
        filterValue = filterValue.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '').toLowerCase();
        containsFilterValue = function (keyword) {
            return keyword.toLowerCase().indexOf(filterValue) !== -1;
        };

        filterElements.each(function () {
            var $result = $(this);
            var $title = $result.find('h3').text().toLowerCase();
            var match = $title.indexOf(filterValue) !== -1;

            if (!match) {
                match = match || _.some($result.data('keywords'), containsFilterValue);
            }

            $result.toggleClass('active', match);
            $result.toggleClass('hidden', !match);
        });

        spinner.addClass('hidden');

        // Trigger the resize event to lazyload images
        $(window).trigger('resize');

        this.focusOnFirstIdp();
    };

    this.editPreviousIdpChoices = function (editLink) {
        var mode = editLink.attr('data-toggle');
        var $removables = $('#preselection .c-button');
        var $list = $('#preselection .list');

        var swapText = editLink.attr('data-toggle-text');
        editLink.attr('data-toggle-text', editLink.html());
        editLink.html(swapText);

        editLink.attr('data-toggle', function () {
            return mode === 'edit' ? 'view' : 'edit';
        });

        $list.toggleClass('show-buttons', mode === 'view');
        $removables.toggleClass('outline deleteable img', mode == 'view');
        $removables.toggleClass('white', mode !== 'view');

        $.each($removables, function () {
            var $item = $(this);
            var itemSwapText = $item.attr('data-toggle-text');
            $item.attr('data-toggle-text', $item.html());
            $item.html(itemSwapText);
        });
    };

    this.handleIdpClickedEvent = function (event, accessLink) {
        var idp = accessLink.attr('data-idp');
        var previousIdpChoices = this.previousIdpChoices.getPreviousIdpChoices();

        var saveIndex = _.findIndex(previousIdpChoices, function (previousIdp) {
            return previousIdp.idp === idp;
        });

        var selectionInsertionPoint;
        var accessLinkIdpName;

        if ($(event.target).hasClass('deleteable')) {
            event.stopPropagation();
            event.preventDefault();

            if (saveIndex !== -1) {
                // Remove the previously selected IdP from the entity ID list.
                previousIdpChoices.splice(saveIndex, 1);

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
                this.render();
            }
        } else {
            if (saveIndex === -1) {
                previousIdpChoices.push({idp: idp, count: 1});
            } else {
                ++previousIdpChoices[saveIndex].count;
            }

            $('#form-idp').val(idp);
            $('form.mod-search').submit();
        }

        Cookies.set('selectedidps', JSON.stringify(previousIdpChoices), { expires: 365, path: '/' });
    }
};
