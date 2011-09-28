<?

	function ID() { return sha1(uniqid(mt_rand(), true)); };
	function timeStamp($delta = 0) {return gmdate('Y-m-d\TH:i:s\Z', time() + $delta);}
	
	$entityID =  'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST']	. $_SERVER['SCRIPT_NAME'];

	$request = json_decode(gzinflate(base64_decode($_GET['SAMLRequest'])), 1);
	$now = timeStamp();
	$soon = timeStamp(300);
	$sessionEnd = timeStamp(60*60*12);
	$response = array (
		'__t' => 'samlp:Response',
		'__' => array(
			'paramname' => 'SAMLResponse',
			'RelayState' => $request['__']['RelayState'],
		),
		'_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
		'_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
		'_ID' => ID(),
		'_Version' => '2.0',
		'_IssueInstant' => $now,
		'_InResponseTo' => $request['_ID'],
		'saml:Issuer' => array('__v' => $entityID),
		'samlp:Status' => array (
			'samlp:StatusCode' => array (
			'_Value' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
			),
		),
	);
	
	$destinationid = $request['saml:Issuer']['__v'];
	$response['__']['destinationid'] = $destinationid;

	if ($acsurl = $request['_AssertionConsumerServiceURL']) {
		$response['_Destination'] 		= $acsurl;
	  	$response['__']['ProtocolBinding']	= $request['_ProtocolBinding'];
	} else {
		die("No Destination in request or metadata for: $destinationid");
	}
  
	$response['saml:Assertion'] = array (
		'_xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
		'_xmlns:xs' => 'http://www.w3.org/2001/XMLSchema',
		'_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
		'_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
		'_ID' => ID(),
		'_Version' => '2.0',
		'_IssueInstant' => $now,
		'saml:Issuer' => array('__v' => $entityID),
		'ds:Signature' => '__placeholder__',
		'saml:Subject' => array (
			'saml:NameID' =>  array (
				'_SPNameQualifier' => $entityID,
				'_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
				'__v' => ID(),
			),
			'saml:SubjectConfirmation' => array (
				'_Method' => 'urn:oasis:names:tc:SAML:2.0:cm:bearer',
				'saml:SubjectConfirmationData' => array (
					'_NotOnOrAfter' => $soon,
					'_Recipient' => $request['_AssertionConsumerServiceURL'], # req issuer
					'_InResponseTo' => $request['_ID'],
				),
			),
		),
		'saml:Conditions' => array (
			'_NotBefore' => $now,
			'_NotOnOrAfter' => $soon,
			'saml:AudienceRestriction' => array (
				'saml:Audience' => array('__v' => $request['saml:Issuer']['__v']),
			),
		),
		'saml:AuthnStatement' => array (
			'_AuthnInstant' => $now,
			'_SessionNotOnOrAfter' => $sessionEnd,
#			'_SessionIndex' => ID(),
			'saml:SubjectLocality' => array(
				'_Address' => $_SERVER['REMOTE_ADDR'],
				'_DNSName' => $_SERVER['REMOTE_HOST'],
			),
			'saml:AuthnContext' => array (
				'saml:AuthnContextClassRef' => array('__v' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password'),
			),
		),
	);

	$attributes['uid'][] = 'abc@null';
	foreach((array)$attributes as $name => $attr) {
		$newattr = array(
			'_Name' => $name,
			'_NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic',
		);
		foreach ((array)$attr as $val) {
			$newattr['saml:AttributeValue'][] = array (
			   '_xsi:type' => 'xs:string',
			   '__v' => $val,
			);
		}
		$res[] = $newattr;
	}
	
	
	$response['saml:Assertion']['saml:AttributeStatement']['saml:Attribute'] = $res;
	
	$location = $response['_Destination'] . "?SAMLResponse=" . urlencode(base64_encode(gzdeflate(json_encode($response))));
?>
<html>
<body>
<a href="<?= $location ?>">GO</a>
<pre>
<?= print_r($response, 1) ?>
</pre>
</body>
</html>