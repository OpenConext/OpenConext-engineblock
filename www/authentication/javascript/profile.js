/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

var Profile = function() {

    var module = {

        init : function() {

            library.fixTableLayout($('#MyAppsTable'));

            $('#GroupProviders').accordion({
                autoHeight: false
            });

            $('.delete').click(function() {
                return confirm('Are you sure you want to delete your profile ?');
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
                })

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
}
