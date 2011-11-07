<?php

require 'Services.php';

class Corto_Module_DemoServices extends Corto_Module_Services
{
    public function demoapp()
    {
        $sharedKey = 'abracadabra';
    
        $self = 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if ($_POST['doit']) {
            $idp = $_POST['idp'];
            if (!$idp) {
                $idp = "sp";
            }
            $request = array(
                '_ID'                               => sha1(uniqid(mt_rand(), true)),
                '_Version'                          => '2.0',
                '_IssueInstant'                     => gmdate('Y-m-d\TH:i:s\Z', time()),
                '_Destination'                      => $self . "/$idp/singleSignOnService",
                '_ForceAuthn'                       => $_REQUEST['ForceAuthn'] ? 'true' : 'false',
                '_IsPassive'                        => $_REQUEST['IsPassive']  ? 'true' : 'false',
                '_AssertionConsumerServiceURL'      => $self . "/main/" . __FUNCTION__,
                '_AttributeConsumingServiceIndex'   => 5,
                '_ProtocolBinding'                  => 'JSON-Redirect',
                'saml:Issuer' => array('__v' => $self . "/main"),
            );
            
            foreach((array)$_REQUEST['IDPList'] as $idp) {
                $idpList[] = array('_ProviderID' => $idp);
            }
            
            $relayState = 'Dummy RelayState ...';
            if ($idpList) {
                $request['samlp:Scoping']['samlp:IDPList']['samlp:IDPEntry'] = $idpList;
            }
            #$request['samlp:Scoping']['_ProxyCount'] = 2;
            $location = $request['_Destination'];
            $request = "SAMLRequest=" . urlencode(base64_encode(gzdeflate(json_encode($request)))) 
                . ($relayState ? '&RelayState=' . urlencode($relayState) : '');
            $signature = urlencode(base64_encode(sha1($sharedKey . sha1($request))));
            header('Location: ' . $location . "?" . $request . "&Signature=" . $signature);
            print "<a href=\"$location?$request&Signature=$signature\">$location</a>";
            exit;
        }

        $response = base64_decode($_REQUEST['SAMLResponse']);
        $hSAMLResponse = json_decode(gzinflate($response), 1);
        if ($rs = $_POST['RelayState']) {
            $rs = '&RelayState=' . $rs;
        }
        if ($response && base64_encode(sha1($sharedKey . sha1("jSAMLResponse=$response$rs"))) != $_POST['Signature']) {
            $message = 'Integrity check failed (Sharedkey) ' .$_POST['Signature'] . ' != ' . base64_encode(sha1($sharedKey . sha1("jSAMLResponse=$response$rs")));
        }
        
        print $this->_server->renderTemplate(
            'demo',
            array(
                'action' => $self . "/main/demoapp",
#                'idps' => array_keys($GLOBALS['metabase']['remote']),
                'hSAMLResponse' => $hSAMLResponse,
                'message' => $message . " RelayState: " . $_GET['RelayState'],
                'self' => $self)
        );
    }
    
 }
