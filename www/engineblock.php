<?php

ini_set('display_errors', true);

define('CORTO_APPLICATION_OVERRIDES_DIRECTORY', realpath(dirname(__FILE__).'/../').'/');
require './../corto/www/corto.php';

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