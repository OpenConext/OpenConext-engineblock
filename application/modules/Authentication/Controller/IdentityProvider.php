<?php
 
class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction($idPEntityId = null)
    {
        $this->setNoRender();

        $application = EngineBlock_ApplicationSingleton::getInstance();

        $metaData = $application->getMetaData();
        $request  = $application->getHttpRequest();

        if ($idPEntityId) {
            $url = $metaData->getSingleSignOnUrlByEntityId($idPEntityId);
            if (!$url) {
                // @todo error out!
                die("Unable to retrieve SSO URL for IdP '$idPEntityId' from metadata!");
            }
            $url .= '?' . $request->getQueryString();

            $application->getHttpResponse()->setRedirectUrl($url);
            return true;
        }

        $url = $request->getProtocol() . '://';
        $url .= $request->getHostName();
        $url .= '/authentication/proxy/wayf';
        $url .= ($request->getQueryString() ? '?' . $request->getQueryString() : '');

        $application->getHttpResponse()->setRedirectUrl($url);
    }

    public function metaDataAction()
    {
        // @todo Give the metadata (with single sign on URL) for EngineBlock
    }
}