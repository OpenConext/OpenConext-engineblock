<?php

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