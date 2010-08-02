<?php

/**
*
*
* @package    Corto
* @module     Library
* @author     Mads Freek Petersen, <freek@ruc.dk>
* @author     Boy Baukema, <boy@ibuildings.com>
* @licence    MIT License, see http://www.opensource.org/licenses/mit-license.php
* @copyright  2009-2010 WAYF.dk
* @version    $Id:$
*/

class Corto_Demo
{
    const SHARED_KEY = 'abracadabra';

    public static function sendAttributeQuery($aa = null)
    {
        if ($aa===null) {
            $aa = CORTO_BASE_URL . '/main/attributeService';
        }

        $attributeQuery = array(
            'samlp:AttributeQuery' => array(
                '_xmlns:samlp'  => 'urn:oasis:names:tc:SAML:2.0:protocol',
                '_xmlns:saml'   => 'urn:oasis:names:tc:SAML:2.0:assertion',
                '_ID'           => ID(),
                '_Version'      => '2.0',
                '_IssueInstant' => timeStamp(),
                'saml:Artifact' => array('__v' => $_REQUEST['SAMLArt']),
                'saml:Issuer'   => array('__v' => $GLOBALS['meta']['EntityID']),
            ),
        );

        $assertion = soapRequest($aa, $attributeQuery);

        print_r($assertion);
    }

    public static function demoApp()
    {
        $self = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if (isset($_POST['doit'])) {
            $idp = $_POST['idp'];
            if (!$idp) {
                $idp = "wayf";
            }
            $request = array(
                '_ID'                               => sha1(uniqid(mt_rand(), true)),
                '_Version'                          => '2.0',
                '_IssueInstant'                     => gmdate('Y-m-d    H:i:s\Z', time()),
                '_Destination'                      => $self . "/$idp/singleSignOnService",
                '_ForceAuthn'                       => isset($_REQUEST['ForceAuthn']) ? 'true' : 'false',
                '_IsPassive'                        => isset($_REQUEST['IsPassive']) ? 'true' : 'false',
                '_AssertionConsumerServiceURL'      => $self . "/main/" . __FUNCTION__,
                '_AttributeConsumingServiceIndex'   => 5,
                '_ProtocolBinding'                  => 'JSON-POST',
                'saml:Issuer'                       => array('__v' => $self . "/main"),
            );

            foreach((array)$_REQUEST['IDPList'] as $idp) {
                $idpList[] = array('_ProviderID' => $idp);
            }

            $relayState = 'Dummy RelayState ...';
            if ($idpList) {
                $request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'] = $idpList;
            }
            $request['samlp:Scoping']['_ProxyCount'] = 2;
            $location = $request['_Destination'];
            $requestUrlValue = urlencode(base64_encode(gzdeflate(json_encode($request))));
            $request = "jSAMLRequest=" . $requestUrlValue . ($relayState ? '&RelayState=' . urlencode($relayState) : '');
            $signature = urlencode(base64_encode(sha1(self::SHARED_KEY . sha1($request))));

            header('Location: ' . $location . "?" . $request . "&Signature=" . $signature);
            print '<a href="$location?$request&Signature=$signature">' . $location . '</a>';
            exit;
        }

        $response = "";
        $hSAMLResponse = "";
        if (isset($_POST['jSAMLResponse'])) {
            $response = base64_decode($_POST['jSAMLResponse']);
            $hSAMLResponse = json_decode($response, 1);
        }

        $relayStateParameter = "";
        if (isset($_POST['RelayState'])) {
            $relayStateParameter = '&RelayState=' . $_POST['RelayState'];
        }

        $message = "";
        if (isset($_POST['Signature']) && base64_encode(sha1(self::SHARED_KEY . sha1("jSAMLResponse=$response$relayStateParameter"))) != $_POST['Signature']) {
            $message = 'Integrity check failed (Sharedkey)';
        }

        print render(
            'demo',
            array(
                'action'        => $self . "/main/demoapp",
                'idps'          => array_keys($GLOBALS['metabase']['remote']),
                'hSAMLResponse' => $hSAMLResponse,
                'message'       => $message . " RelayState: " . (isset($_GET['RelayState'])?$_GET['RelayState']:''),
                'self'          => $self,
        ));
    }
}
 
