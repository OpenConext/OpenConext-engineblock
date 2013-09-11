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

$(document).ready(function() {
    var screen = new Screen();
    screen.init();
})

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
}