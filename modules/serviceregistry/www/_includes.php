<?php
/**
 * SURFconext Service Registry
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
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * @todo This should really be autoloaded
 */

define('SERVICE_REGISTRY_LIBRARY', __DIR__ . '/../lib');

require SERVICE_REGISTRY_LIBRARY . '/Shell/Command/Interface.php';
require SERVICE_REGISTRY_LIBRARY . '/Shell/Command/Abstract.php';

require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate/Chain.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate/Utility.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate/Validator.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate/Chain/Factory.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate/Chain/Validator.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate/Chain/Exception/BuildingFailedIssuerUrlNotFound.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate/Exception/NotAValidPem.php';

require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Command/SClient.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Command/Verify.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Command/X509.php';


require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Url/UnparsableUrlException.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Certificate.php';
require SERVICE_REGISTRY_LIBRARY . '/OpenSsl/Url.php';

require SERVICE_REGISTRY_LIBRARY . '/Janus/Exception/NoCertData.php';
require SERVICE_REGISTRY_LIBRARY . '/Janus/CertificateFactory.php';

require SERVICE_REGISTRY_LIBRARY . '/Metadata/Validator.php';