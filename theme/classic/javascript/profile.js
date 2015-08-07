var Profile = function() {

    var module = {

        init : function() {

            library.fixTableLayout($('#MyAppsTable'));

            $('#GroupProviders').accordion({
                autoHeight: false
            });

            $('#delete-confirmation-form').submit(function() {
                return confirm($('#delete-confirmation-text').attr('data-confirmation-text'));
            });

            $('.show-details').live('click', function(e){
                e.preventDefault();
                var table = $(this).parents('table');
                var nextRow = $(this).parents('tr').next();

                var isOpen = !nextRow.hasClass('hide');

                // Collapse all boxes
                table.find('tr.detail-row').each(function() {
                    if (!$(this).hasClass('hide')) {
                        var elm = $(this);
                        $(this).find('div.attribute-table-wrapper').slideUp(500, function() {
                            elm.addClass('hide');
                        });
                    }
                });

                // reset all icons
                table.find('span.ui-icon').each(function() {
                    $(this).removeClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-e');
                });

                if (isOpen) {
                    nextRow.find('div.attribute-table-wrapper').slideUp(500, function() {
                        nextRow.addClass('hide');
                    });
                    $(this).prev().removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
                } else {
                    nextRow.removeClass('hide');
                    nextRow.find('div.attribute-table-wrapper').slideDown(500, function() {
                    });
                    $(this).prev().removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
                }
            })
        }
    };

    var library = {
        fixTableLayout : function(table) {
            if (table instanceof jQuery) {

                var style = 'odd';

                table.find('tbody.apps>tr').removeClass('odd').removeClass('even');
                table.find('tbody.apps>tr').each(function() {
                    if (!$(this).hasClass('detail-row')) {
                        $(this).addClass(style);
                        style = style === 'odd' ? 'even' : 'odd';
                    }
                });
            }
        }
    };

    return module;
};
