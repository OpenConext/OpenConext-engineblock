<?php
namespace EngineBlock\Behat\Context;

use \Behat\Behat\Context\BehatContext;

/**
 * Description of TrackingAndProvisioning
 *
 * @author lucasvanlierop
 */
class Provisioning extends BehatContext
{
    // @todo [improve] Get url/domain values from config?
    const TEST_SP_URL = 'https://engine-internal.test.surfconext.nl/social/people/urn:collab:person:';
    const TEST_IDP_DOMAIN = 'test.surfguest.nl';

    /**
     * Tries to retrieve user data via open social api
     *
     * @Then /^I should be able to retrieve user "([^"]*)" via open social api$/
     * @param string $userName
     * @return void
     */
    public function iShouldBeAbleToRetrieveUserViaOpenSocialApi($userName)
    {
        $this->_loadUserDataViaOpenSocialApi($userName);
    }

    /**
     * Loads user data via open social api
     *
     * @param string $userName
     * @return array $userData
     * @throws Exception in case user cannot be retrieved
     * @todo [move] make this a generic and public available method so it can be used
     * for testing deprovisioning also
     */
    protected function _loadUserDataViaOpenSocialApi($userName)
    {
        $url = self::TEST_SP_URL . self::TEST_IDP_DOMAIN . ':' . $userName . '?fields=all';
        $this->getMainContext()->getSession()->visit($url);

        $userDataJson = $this->getMainContext()->getSession()->getPage()->getContent();
        $resultData = json_decode($userDataJson, true);
        $userData = current($resultData['entry']);

        if(!array_key_exists('id', $userData)) {
            throw new \Exception('User ' . $userName . ' Could not be retrieved from LDAP');
        }

        return $userData;
    }
}