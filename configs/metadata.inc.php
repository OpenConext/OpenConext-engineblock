<?php

$GLOBALS['metabase'] = array();

$GLOBALS['metabase']['hosted']= array(
    CORTO_BASE_URL . 'main' => array(
        'WantResponsesSigned' => true,
        'WantAssertionsSigned' => true,
        'outfilter'=>'engineBlockEnrichAttributes',
    ),

    CORTO_BASE_URL . 'ssidp' => array(
        'idp' => 'https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
    ),
);
//
//$GLOBALS['metabase']['remote'] = array(
//    'https://ss.idp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp' => array(
//        'SingleSignOnService'   => array(
//            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
//            'Location' => 'https://ss.idp.ebdev.net/simplesaml/saml2/idp/SSOService.php',
//        ),
//        'name:nl'=> 'EngineBlock Test IdP',
//        'hostedname' => 'ssidp',
//    ),
//
//    'https://ivo-idp.coin.surfnetlabs.nl/simplesaml/saml2/idp/metadata.php' => array(
//        'SingleSignOnService'   => array(
//            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
//            'Location' => 'https://ivo-idp.coin.surfnetlabs.nl/simplesaml/saml2/idp/SSOService.php',
//        ),
//    ),
//
//    'https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/metadata.php/default-sp' => array(
//        'AssertionConsumerService'=>array(
//            'Location'=>"https://ss.sp.ebdev.net/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp",
//            'Binding'=>'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
//        ),
//        'WantResponsesSigned' => true,
//        'WantAssertionsSigned' => true,
//
//        'OrganizationDisplayName'=> 'EngineBlock Test SP',
//    ),
//);

engineBlockSetupMetadata();

function engineBlockSetupMetadata()
{
    $GLOBALS['metabase']['remote'] = array();
    
    $metaDataXml = file_get_contents(ENGINEBLOCK_SERVICEREGISTRY_METADATA_URL);
    $hash = Corto_XmlToHash::xml2hash($metaDataXml);

    foreach ($hash['md:EntityDescriptor'] as $entity) {
        $entityID = $entity['_entityID'];
        $metaData = array();
        if (isset($entity['md:IDPSSODescriptor']['md:SingleSignOnService'])) {
            $metaData['SingleSignOnService'] = array(
                'Binding' =>$entity['md:IDPSSODescriptor']['md:SingleSignOnService']['_Binding'],
                'Location'=>$entity['md:IDPSSODescriptor']['md:SingleSignOnService']['_Location'],
            );
        }
        if (isset($entity['md:SPSSODescriptor']['md:AssertionConsumerService'])) {
            $metaData['AssertionConsumerService'] = array(
                'Binding' =>$entity['md:SPSSODescriptor']['md:AssertionConsumerService']['_Binding'],
                'Location'=>$entity['md:SPSSODescriptor']['md:AssertionConsumerService']['_Location'],
            );

            $metaData['WantResponsesSigned']  = true;
            $metaData['WantAssertionsSigned'] = true;
        }

        if (isset($entity['md:Organization'])) {
            if (isset($entity['md:Organization']['md:OrganizationDisplayName'])) {
                $metaData['OrganizationDisplayName'] = $entity['md:Organization']['md:OrganizationDisplayName']['__v'];
            }
            else {
                $metaData['OrganizationDisplayName'] = $entity['md:Organization']['md:OrganizationName']['__v'];
            }
        }


        $GLOBALS['metabase']['remote'][$entityID] = $metaData;
    }
}


function engineBlockEnrichAttributes(array $metaData, array $response, array &$attributes)
{
    $attributes['over-18'] = array('yup');
}

function engineBlockTranslateAttributeName($name)
{
    $attributeName = 'attribute_' . strtolower($name);
    $translations = json_decode(file_get_contents(CORTO_APPLICATION_OVERRIDES_DIRECTORY . 'configs/attributes.definition.json'));
    if (!isset($translations->$attributeName)) {
        return $name;
    }

    return $translations->$attributeName->en;
}