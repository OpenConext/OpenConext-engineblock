<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi_PublicationInfo
{

    public function mapTo(array $rootElement)
    {
        $publication = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->edugain->publication;

        if (!isset($rootElement['mdrpi:Extensions']['mdrpi:PublicationInfo'])) {
            $publicationInfo = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "creationInstant" => date(DateTime::W3C ),
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . "publisher" => $publication->publisher
            );
            $publicationInfo['mdrpi:UsagePolicy'] = array(
                array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => "en",
                    EngineBlock_Corto_XmlToArray::VALUE_PFX => $publication->policy
                )
            );
            $rootElement['mdrpi:Extensions']['mdrpi:PublicationInfo'] = array($publicationInfo);

        }
        return $rootElement;
    }
}