<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Parser;

use Doctrine\Common\Annotations\PhpParser;
use OpenConext\EngineBlock\FunctionalTestingBundle\Parser\Corto\XmlToArray;
use OpenConext\EngineBlock\FunctionalTestingBundle\Saml2\AuthnRequest;
use OpenConext\EngineBlock\FunctionalTestingBundle\Saml2\Response;

/**
 * Class LogChunkParser
 * @package OpenConext\EngineBlock\FunctionalTestingBundle\Parser
 */
class LogChunkParser
{
    const MESSAGE_TYPE_RESPONSE         = 'Response';
    const MESSAGE_TYPE_AUTHN_REQUEST    = 'AuthnRequest';

    const RESPONSE_URL_KEY      = 'SAMLResponse';
    const RESPONSE_TAGNAME      = 'Response';

    const AUTHN_REQUEST_URL_KEY = 'SAMLRequest';
    const AUTHN_REQUEST_TAGNAME = 'AuthnRequest';

    protected $logFile;

    /**
     * @param $logFile
     */
    public function __construct($logFile)
    {
        $this->logFile = $logFile;

        $this->verifyLogFile();
    }

    protected function verifyLogFile()
    {
        if (is_file($this->logFile)) {
            return;
        }

        throw new \RuntimeException("Can not find log file '{$this->logFile}'.");
    }

    /**
     * @return bool
     * @throws \RuntimeException
     */
    public function detectTransparentRequest()
    {
        $contents = $this->load();

        $matches = array();
        $matched = preg_match(
            '/\[Message INFO\] Detected pre-selection of (?P<entityId>.+) as IdP, switching to transparant mode/',
            $contents,
            $matches
        );

        if ($matched === false) {
            throw new \RuntimeException(
                "Unable to look for transparent request, regex triggered an error?" . preg_last_error()
            );
        }

        if ($matched === 0) {
            return false;
        }

        return $matches['entityId'];
    }

    /**
     * @return array|bool
     */
    public function detectUnsolicitedRequest()
    {
        $lines = explode("\n", $this->load());
        foreach ($lines as $line) {
            if (!strstr($line, 'Unsolicited Request')) {
                continue;
            }

            $matches = array();
            $matched = preg_match('/\] (?P<printR>Array.+)/', $line, $matches);
            if ($matched === false) {
                throw new \RuntimeException('Unable to execute regex!');
            }
            if ($matched === 0) {
                throw new \RuntimeException('No PrintR found in line with "Unsolicited Request"');
            }

            $content = preg_replace('/\\\n/', "\n", $matches['printR']);

            $parser = new PrintRParser($content);
            $request = $parser->parse();

            if (!isset($request['saml:Issuer']['__v'])) {
                throw new \RuntimeException('Unsolicited request doesnt have an issuer?');
            }

            return $request;
        }
        return false;
    }

    public function getMessage($messageType)
    {
        if (!in_array($messageType, array(self::MESSAGE_TYPE_RESPONSE, self::MESSAGE_TYPE_AUTHN_REQUEST))) {
            throw new \RuntimeException("Unsupported messageType: " . $messageType);
        }

        $content = $this->load();

        $message = $this->getMessageFromChunk($messageType, $content);
        if ($message) {
            return $message;
        }

        $message = $this->getMessageFromUrl($messageType, $content);
        if ($message) {
            return $message;
        }

        throw new \RuntimeException('Unable to get message from log chunk!');
    }

    protected function load()
    {
        return file_get_contents($this->logFile);
    }

    protected function getMessageFromUrl($messageType, $content)
    {
        $urlKey = $this->getUrlKeyForMessageType($messageType);

        $matches = array();
        if (!preg_match("/$urlKey=([A-Za-z0-9+\\/%]+)/", $content, $matches)) {
            return false;
        }
        $request = $matches[1];

        $request = urldecode($request);

        $request = base64_decode($request);
        if (!$request) {
            throw new \RuntimeException("Unable to base64 decode found SAMLRequest: '{$matches[1]}'");
        }

        $request = gzinflate($request);
        if (!$request) {
            throw new \RuntimeException("Unable to gzip inflate found SAMLRequest: '{$matches[1]}'");
        }

        $document = new \DOMDocument();
        $document->loadXML($request);

        $messageObj = $this->createObjectForMessageType($messageType, $document->firstChild);
        $messageObj->setXml($request);

        return $messageObj;
    }

    protected function getUrlKeyForMessageType($messageType)
    {
        if ($messageType === static::MESSAGE_TYPE_AUTHN_REQUEST) {
            return static::AUTHN_REQUEST_URL_KEY;
        }
        return static::RESPONSE_URL_KEY;
    }

    protected function getMessageFromChunk($messageType, $content)
    {
        $content = $this->getChunkContent($messageType, $content);
        if ($content === false) {
            return false;
        }

        return $this->createObjectFromPrintR($messageType, $content);
    }

    protected function getChunkContent($messageType, $content)
    {
        $tagName = $this->getTagNameForMessageType($messageType);

        $chunkStartMatches = array();
        $chunkEndMatches = array();

        $matchedChunkStartLines = preg_match("/!CHUNKSTART>.+samlp:$tagName/", $content, $chunkStartMatches);
        $matchedChunkEndLines   = preg_match('/!CHUNKEND>/', $content, $chunkEndMatches);

        if ($matchedChunkStartLines === false || $matchedChunkEndLines === false) {
            throw new \RuntimeException('Matching for CHUNKSTART and CHUNKEND gave an error');
        }

        if (!$matchedChunkStartLines xor !$matchedChunkEndLines) {
            throw new \RuntimeException('CHUNKSTART found without CHUNKEND or vice versa');
        }

        if ($matchedChunkStartLines === 0) {
            return false;
        }

        // Chop off everything before the CHUNKSTART
        $content = substr($content, strpos($content, $chunkStartMatches[0]));
        // ... and after the first newline after CHUNKEND
        $content = substr($content, 0, strpos($content, "\n", strpos($content, $chunkEndMatches[0])));

        // Remove everything before CHUNK>|CHUNKSTART>|CHUNKEND>
        $content = preg_replace('/!CHUNKSTART>\s*/sU', '', $content);
        $content = preg_replace('/\n.+CHUNK>/sU', '', $content);
        $content = preg_replace('/\n.+CHUNKEND>/sU', '', $content);
        // And turn all \n literals into actual newlines
        $content = preg_replace('/\\\n/', "\n", $content);

        return $content;
    }

    /**
     * @param $messageType
     * @return string
     */
    protected function getTagNameForMessageType($messageType)
    {
        if ($messageType === static::MESSAGE_TYPE_AUTHN_REQUEST) {
            return static::AUTHN_REQUEST_TAGNAME;
        }
        return static::RESPONSE_TAGNAME;
    }

    /**
     * @param $messageType
     * @param $content
     * @return AuthnRequest|Response
     */
    protected function createObjectFromPrintR($messageType, $content)
    {
        $parser = new PrintRParser($content);
        $messageArray = $parser->parse();

        if (isset($messageArray['__']['Raw'])) {
            $xml = $messageArray['__']['Raw'];
        } else {
            $xml = XmlToArray::array2xml($messageArray, 'samlp:' . $this->getTagNameForMessageType($messageType));
        }

        $document = new \DOMDocument();
        $document->loadXML($xml);

        $messageObj = $this->createObjectForMessageType($messageType, $document->firstChild);
        $messageObj->xml = $xml;
        return $messageObj;
    }

    protected function createObjectForMessageType($messageType, \DOMElement $root)
    {
        if ($messageType === static::MESSAGE_TYPE_AUTHN_REQUEST) {
            return new AuthnRequest($root);
        }

        return new Response($root);
    }
}
