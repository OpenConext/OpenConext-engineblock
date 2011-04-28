<?php

ini_set('display_errors', true);



/**
 * @var array Top level JANUS metadata fields that have endpoint data
 */


define('ENTITY_METADATA_MINIMUM_VALIDITY_DAYS', 5);

define('DAY_IN_SECONDS', 86400);

$config                 = SimpleSAML_Configuration::getInstance();
$janusConfig            = SimpleSAML_Configuration::getConfig('module_janus.php');
$serviceRegistryConfig  = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');

$template = new SimpleSAML_XHTML_Template($config, 'serviceregistry:checkcerts.php', 'serviceregistry:checkcerts');

$janusWorkflowStates = $janusConfig->getValue('workflowstates');
$template->data['workflow_states'] = $janusWorkflowStates;

$entityMetadataMinimumValidityDays = $serviceRegistryConfig->getInteger(
    'entity_metadata_minimum_validity_days',
    ENTITY_METADATA_MINIMUM_VALIDITY_DAYS
);

$metaEntries = array(
    'saml20-idp' => array(),
    'saml20-sp' => array(),
    'shib13-idp' => array(),
    'shib13-sp' => array(),
);

$util = new sspmod_janus_AdminUtil();
foreach ($util->getEntities() as $entity) {
    $entityId = $entity['eid'];

    $entityController = new sspmod_serviceregistry_EntityController($janusConfig);
    $entityController->setEntity($entityId);
    $entityController->loadEntity();

    $metadata           = $entityController->getMetadata();
    $metaArray          = $entityController->getMetaArray();
    $entityId           = $entityController->getEntity()->getEntityid();
    $entityType         = $entityController->getEntity()->getType();
    $prettyName         = $entityController->getEntity()->getPrettyname();
    $entityWorkflow     = $entityController->getEntity()->getWorkflow();
//if ($prettyName!=="test displayname en") continue;
    $entry = array();
    $entry['entity_id']      = $entityId;
    $entry['entity_type']    = $entityType;
    $entry['pretty_name']    = $prettyName;
    $entry['workflow']       = $entityWorkflow;

    $entry['name'] = (array_key_exists('name', $metaArray)) ? $metaArray['name'] : null;
    $entry['url']  = (array_key_exists('url', $metaArray))  ? $metaArray['url']  : null;

    $entry['validation'] = array(
        'errors'=>array(),
        'warnings'=>array(),
    );

    $entry['validation']['endpoints'] = array();
    foreach ($ENTITY_ENDPOINT_METAKEYS as $endPointMetaKey) {
        if (!isset($metaArray[$endPointMetaKey])) {
            continue;
        }

        foreach ($metaArray[$endPointMetaKey] as $index => $binding) {
            if (!isset($binding['Location']) || trim($binding['Location'])==="") {
                $entry['validation']['warnings'][] = array(
                    'entity_binding_no_location',
                    array(
                        'KEY' => "$endPointMetaKey:$index"
                    )
                );
                continue;
            }

            $endpointKey = $endPointMetaKey . ':' . $index . ':Location';
            $entry['validation']['endpoints'][$endpointKey] = array(
                'warnings'=>array(),
                'errors' => array(),
            );
            $validationResults = &$entry['validation']['endpoints'][$endpointKey];

            $sslUrl = new OpenSsl_Url($binding['Location']);
            $connectSuccess = $sslUrl->connect();
            if (!$connectSuccess) {
                $validationResults['errors'][] = array(
                    'endpoint_unreachable',
                    array(
                         'LOCATION' => $binding['Location']
                    )
                );
                continue;
            }

            $urlCertificate = $sslUrl->getCertificate();

            $urlCertificateSubject = $urlCertificate->getSubject();
            $urlCertificateCn = $urlCertificateSubject['CN'];
            $urlCertificateAltNames = $urlCertificate->getSubjectAltNames();
            $urlHost = $sslUrl->getHost();

            $matches = false;;
            if (doesHostnameMatchPattern($urlHost, $urlCertificateCn)) {
                $matches = true;
            }
            foreach ($urlCertificateAltNames as $altName) {
                if (doesHostnameMatchPattern($urlHost, $altName)) {
                    $matches = true;
                }
            }

            if (!$matches) {
                $validationResults['errors'][] = array(
                    'endpoint_domain_mismatch',
                    array(
                        'CN'   => $urlCertificateCn,
                        'HOST' => $urlHost,
                        'SUBJECTALT' => implode(', ', $urlCertificateAltNames),
                    ),
                );
            }

            $urlChain = OpenSsl_Certificate_Chain_Factory::create($urlCertificate);

            $urlChainValidator = new OpenSsl_Certificate_Chain_Validator($urlChain);
            $urlChainValidator->validate();

            $validationResults['warnings'] += $urlChainValidator->getWarnings();
            $validationResults['errors']   += $urlChainValidator->getErrors();
        }
    }

    try {
        $certificate = $entityController->getCertificate();
    } catch(Janus_Exception_NoCertData $e) {
        $entry['validation']['warnings'][] = array(
            'entity_cert_missing'
        );
        // Store the data in the result array
        if (array_key_exists($entityType, $metaEntries)) {
            array_push($metaEntries[$entityType], $entry);
        }
        continue;
    }
    $entry['certificate'] = $certificate;

    if ($certificate->getValidFromUnixTime() > time()) {
        $entry['validation']['errors'][] = array(
            'entity_cert_not_yet_valid'
        );
    }
    if ($certificate->getValidUntilUnixTime() < time()) {
        $entry['validation']['errors'][] = array(
            'entity_cert_expired'
        );
    }
        
    $chain = OpenSsl_Certificate_Chain_Factory::create($certificate);
    $entry['chain'] = $chain;

    $validator = new OpenSsl_Certificate_Chain_Validator($chain);
    $validator->setIgnoreSelfSigned(true);
    $validator->validate();

    $entry['validation']['warnings'] = array_merge($entry['validation']['warnings'], $validator->getWarnings());
    $entry['validation']['errors']   = array_merge($entry['validation']['errors'],   $validator->getErrors());

    // Check if the certificate is still valid in x days, add a warning if it is not
    $entityMetadataMinimumValidityUnixTime = time() + ($entityMetadataMinimumValidityDays * DAY_IN_SECONDS);
    if (!$certificate->getValidUntilUnixTime() > $entityMetadataMinimumValidityUnixTime) {
        $entry['validation']['warnings'][] = array(
            'entity_cert_expires_soon',
            array(
                 $certificate->getValidUntilUnixTime(),
                 $entityMetadataMinimumValidityDays,
            )
        );
    }

    // Store the data in the result array
    if (array_key_exists($entityType, $metaEntries)) {
        array_push($metaEntries[$entityType], $entry);
    }
}

$template->data['header'] = $template->t('federation_entities_header');
$template->data['meta_entries'] = $metaEntries;

$template->show();

