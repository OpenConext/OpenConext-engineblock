<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Service;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\IdFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\IdFrame;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\SuperGlobalsFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\TimeFixture;

/**
 * Class EngineBlock
 * @SuppressWarnings("PMD")
 */
class EngineBlock
{
    const IDP_METADATA_PATH         = '/authentication/idp/metadata';
    const SP_METADATA_PATH          = '/authentication/sp/metadata';
    const SINGLE_SIGN_ON_PATH       = '/authentication/idp/single-sign-on';
    const ASSERTION_CONSUMER_PATH   = '/authentication/sp/consume-assertion';
    const UNSOLICITED_SSO_START_PATH = '/authentication/idp/unsolicited-single-sign-on/%s';
    const LOGOUT                    = '/logout';

    protected $baseUrl;
    protected $timeFixture;
    protected $superGlobalFixture;
    protected $idFixture;

    /**
     * @param $baseUrl
     * @param TimeFixture $timeFixture
     * @param SuperGlobalsFixture $superGlobalFixture
     * @param IdFixture $idFixture
     */
    public function __construct(
        $baseUrl,
        TimeFixture $timeFixture,
        SuperGlobalsFixture $superGlobalFixture,
        IdFixture $idFixture
    ) {
        $this->baseUrl              = $baseUrl;
        $this->timeFixture          = $timeFixture;
        $this->superGlobalFixture   = $superGlobalFixture;
        $this->idFixture            = $idFixture;
    }

    public function idpEntityId()
    {
        return $this->baseUrl . self::IDP_METADATA_PATH;
    }

    public function transparentSsoLocation($idpEntityId)
    {
        return $this->singleSignOnLocation() . '/' . md5($idpEntityId);
    }

    public function voSsoLocation($voId)
    {
        return $this->singleSignOnLocation() . '/vo:' . $voId;
    }

    public function singleSignOnLocation()
    {
        return $this->baseUrl . self::SINGLE_SIGN_ON_PATH;
    }

    public function unsolicitedLocation($identityProviderEntityId, $serviceProviderEntityId)
    {
        return $this->baseUrl
               . sprintf(self::UNSOLICITED_SSO_START_PATH, md5($identityProviderEntityId))
               . '?sp-entity-id=' . urlencode($serviceProviderEntityId);
    }

    public function spEntityId()
    {
        return $this->baseUrl . self::SP_METADATA_PATH;
    }

    public function assertionConsumerLocation()
    {
        return $this->baseUrl . self::ASSERTION_CONSUMER_PATH;
    }

    public function logoutLocation()
    {
        return $this->baseUrl . self::LOGOUT;
    }

    public function overrideHostname($hostname)
    {
        $this->superGlobalFixture->set(SuperGlobalsFixture::SERVER, 'HTTP_HOST', $hostname);
        return $this;
    }

    public function overrideTime($time)
    {
        $this->timeFixture->set($time);
        return $this;
    }

    public function getIdsToUse($frameName)
    {
        if (!$this->idFixture->hasFrame($frameName)) {
            $frame = new IdFrame();
            $this->idFixture->addFrame($frameName, $frame);
        }

        return $this->idFixture->getFrame($frameName);
    }

    public function clearNewIds()
    {
        $this->idFixture->clear();
        return $this;
    }

    public function getIdFixture()
    {
        return $this->idFixture;
    }
}
