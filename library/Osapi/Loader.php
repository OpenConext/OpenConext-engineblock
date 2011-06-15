<?php
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

/**
 * Shindig and the OpenSocial Client both use the same OAuth library.
 *
 * So when Shindig calls EngineBlock stuff and EngineBlock calls the OS client,
 * it breaks because it already knows an OAuthException class.
 *
 * So what we do is we use include_path trickery to make the OS client look in /Osapi/Hack
 * for it's stuff FIRST.
 * There is an external/Oauth.php which just requires the Shindig OAuth library
 * which it already has included, so everything chugs along. Sweet!
 */

$osHackPath = realpath(ENGINEBLOCK_FOLDER_LIBRARY . '/Osapi/Hack') . '/';
$osApiPath  = realpath(ENGINEBLOCK_FOLDER_LIBRARY . '/opensocial-php-client/src/osapi') . '/';
set_include_path($osHackPath . PATH_SEPARATOR . $osApiPath . PATH_SEPARATOR . get_include_path());

require_once 'osapi.php';

osapiLogger::setAppender(new osapiConsoleAppender());