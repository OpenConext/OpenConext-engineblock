<?php
/**
 * Temporary workaround
 *
 * @todo move methods message and entity objects *
 */
class EngineBlock_SamlHelper
{
    /**
     * Extracts sp or idp from SAML message
     *
     * @param array $samlMessage
     * @return string
     */
    public static function extractIssuerFromMessage(array $samlMessage)
    {
        return $samlMessage['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
    }

    /**
     * Extracts sp or idp from SAML message
     *
     * @param array $samlMessage
     * @return string
     */
    public static function extractOriginalIssuerFromMessage(array $samlMessage)
    {
        return $samlMessage[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['OriginalIssuer'];
    }

    /**
     * @param array $sp
     * @param array $idp
     * @return bool
     */
    public static function doRemoteEntitiesRequireAdditionalLogging(array $sp, array $idp = null) {
        return (!empty($sp['AdditionalLogging']) || !empty($idp['AdditionalLogging']));
    }
}
