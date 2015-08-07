$(document).ready(function() {
    var screen = new Screen();
    screen.init();
});

var Screen = function() {

    var module = {
        init : function() {
            $('table').each(function() {
               library.fixTableLayout($(this), 'no-layout-fix');
            });
        }
    };

    var library = {
        fixTableLayout : function(table, exception) {

          if (table instanceof jQuery && !table.hasClass(exception)) {
            // Add odd and even classes to odd and even rows
            table.find('tbody>tr:nth-child(even)').addClass('even');
            table.find('tbody>tr:nth-child(odd)').addClass('odd');
          }
        }
    };

    return module;
};