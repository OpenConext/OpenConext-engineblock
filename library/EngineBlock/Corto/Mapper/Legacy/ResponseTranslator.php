<?php

use SAML2\DOMDocumentFactory;
use SAML2\Response;
use SAML2\XML\saml\NameID;

/**
 * Class EngineBlock_Corto_Mapper_Legacy_ResponseTranslator
 */
class EngineBlock_Corto_Mapper_Legacy_ResponseTranslator
{
    /**
     * @var array
     */
    protected $privateVars = array(
        'Return',
        'OriginalIssuer',
        'OriginalNameId',
        'OriginalBinding',
        'OriginalResponse',
        'CollabPersonId',
        'CustomNameId',
        'IntendedNameId',
        'DeliverByBinding',
        'RelayState',
    );

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return array
     */
    public function fromNewFormat(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        $legacyResponse = EngineBlock_Corto_XmlToArray::xml2array(
            $response->getSspMessage()->toUnsignedXML()->ownerDocument->saveXML()
        );

        return $this->addPrivateVarsToLegacy($legacyResponse, $response);
    }

    /**
     * @param array $legacyResponse
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    public function fromOldFormat(array $legacyResponse)
    {
        $legacyResponse = EngineBlock_Corto_XmlToArray::registerNamespaces($legacyResponse);
        $xml = EngineBlock_Corto_XmlToArray::array2xml($legacyResponse);

        $document = DOMDocumentFactory::fromString($xml);

        $response = new Response($document->firstChild);
        $annotatedResponse = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);

        return $this->addPrivateVars($annotatedResponse, $legacyResponse);
    }

    /**
     * @param array $to
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $from
     * @return array
     */
    protected function addPrivateVarsToLegacy(array $to, EngineBlock_Saml2_ResponseAnnotationDecorator $from)
    {
        if (!isset($to[EngineBlock_Corto_XmlToArray::PRIVATE_PFX])) {
            $to[EngineBlock_Corto_XmlToArray::PRIVATE_PFX] = array();
        }

        foreach ($this->privateVars as $privateVar) {
            $method = 'get' . $privateVar;
            $value = $from->$method();
            if ($value) {
                if ($value instanceof EngineBlock_Saml2_ResponseAnnotationDecorator) {
                    $value = $this->fromNewFormat($value);
                }
                $to[EngineBlock_Corto_XmlToArray::PRIVATE_PFX][$privateVar] = $value;
            }
        }
        return $to;
    }

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $to
     * @param array $from
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    protected function addPrivateVars(EngineBlock_Saml2_ResponseAnnotationDecorator $to, array $from)
    {
        foreach ($this->privateVars as $privateVar) {
            if (!isset($from[EngineBlock_Corto_XmlToArray::PRIVATE_PFX][$privateVar])) {
                continue;
            }

            $value = $from[EngineBlock_Corto_XmlToArray::PRIVATE_PFX][$privateVar];

            // Does this value have a __t tag? If so, make sure it's namespaces are registered.
            if (is_array($value) && isset($value[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX])) {
                $value = $this->fromOldFormat($value);
            }

            // 'setCustomNameId' on the ResponseAnnotationDecorator requires an array representation of the NameID. So
            // convert it if it's a NameID object.
            if ($privateVar === 'CustomNameId' && $value instanceof NameID) {
                $value = ['Value' => $value->value, 'Format' => $value->Format];
            }

            $method = 'set' . $privateVar;
            $to->$method($value);
        }
        return $to;
    }
}
