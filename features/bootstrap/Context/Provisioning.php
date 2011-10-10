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
     * Checks a given attribute against the value loaded via open social api
     *
     * @Then /^the open social attribute "([^"]*)" of user "([^"]*)" should be "([^"]*)"$/
     * @param   string $attribute
     * @param   string $userName
     * @param   string $value
     * @return  void
     */
    public function theOpenSocialAttributeOfUserShouldBe($attribute, $userName, $value)
    {
        $userData = $this->_loadUserDataViaOpenSocialApi($userName);

        $loadedValue = self::getNestedValue($userData, $attribute);

        if(null === $loadedValue) {
            throw new \Exception('Attribute ' . $attribute . ' Does not exist');
        }

        if($value != $loadedValue) {
            throw new \Exception('Attribute ' . $attribute . ' is not: "' . $value . '" but: "' . $loadedValue . '"');
        }
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
        $isUserFound = is_array($userData) && !array_key_exists('id', $userData);
        if(!$isUserFound) {
            throw new \Exception('User ' . $userName . ' Could not be retrieved from LDAP');
        }

        return $userData;
    }

    /**
     * Finds value in nested array specified by path
     *
     * @param   array    $haystack
     * @param   string   $path       location split by separator
     * @param   string   $separator  separator used (defaults to dot)
     * @return  mixed    $haystack   (reduced)
     * @todo [move] this to util class
     */
    public static function getNestedValue(array $haystack, $path, $separator = '.')
    {
        $pathParts = explode($separator, $path);
        foreach($pathParts as $partName) {
            // Reduce result
            if(is_array($haystack) && array_key_exists($partName, $haystack)) {
                $haystack = $haystack[$partName];
            } else {
                return null;
            }
        }

        return $haystack;
    }

    /**
     * Checks if form errors occured
     *
     * @Given /^I check for form errors$/
     * @todo make name more specific?
     * @return void
     * @throws Exception if error occurs
     */
    public function iCheckForFormErrors()
    {
        $page = $this->getMainContext()->getSession()->getPage();
        $errorElement = $page->find('css', 'ul.error li');
        if(!empty($errorElement)) {
            throw new \Exception(
                $errorElement->getText()
                . "\n(Note: This error message can also mean that invalid characters are used!)"
            );
        }
    }

}