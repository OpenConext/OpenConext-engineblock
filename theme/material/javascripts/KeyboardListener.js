var KeyboardListener = function () {
    this.onKeyDown = function (event) {
        var activeElement  = $(document.activeElement);
        var listElement    = $('.list');
        var resultElements = $('.result');
        var key            = event.which;

        switch (key) {
            case 13:
                selectIdP(activeElement, listElement);
                break;
            case 40:
                resultElements.removeClass('focussed');
                moveDown(activeElement, listElement);
                break;
            case 38:
                resultElements.removeClass('focussed');
                moveUp(activeElement);
                break;
        }
    };

    function selectIdP (activeElement, listElement) {
        // Enter when in the search input
        if (activeElement.hasClass('mod-search-input')) {
            listElement.find('.active:first').first().focus().trigger('click');
        }

        // Enter when in a list
        if (activeElement.attr('href') === '#') {
            activeElement.trigger('click');
        }
    }

    function moveDown (activeElement, listElement) {

        // Move down from the searchbox
        // if (activeElement.closest('.active').next().attr('type') === 'hidden') {
        if (activeElement.hasClass('mod-search-input')) {
            var nextElement = listElement.find('.active');

            if (nextElement.length > 0) {
                nextElement[0].focus();

                return;
            }
        }

        // Move down in list
        if (activeElement.nextAll('.active:first').length > 0) {
            activeElement.nextAll('.active:first').focus();
            return;
        }

        // Move down to next list
        if (activeElement.parent().parent().next().find('.active:first').length > 0) {
            activeElement.parent().parent().next().find('.active:first').focus();
        }
    }

    function moveUp (activeElement) {
        // Move up in list
        if (activeElement.prevAll('.active:first').length > 0) {
            activeElement.prevAll('.active:first').focus();
            return;
        }

        // Move up in previous list
        if (activeElement.parent().parent().prev().find('.active:last').length > 0) {
            activeElement.parent().parent().prev().find('.active:last').focus();
            return;
        }

        // Move up to searchbox
        if (activeElement.prevAll('.active:first').length === 0) {
            $('input.mod-search-input').focus();
        }
    }
};
