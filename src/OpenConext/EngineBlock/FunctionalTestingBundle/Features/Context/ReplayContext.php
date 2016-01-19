<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Features\Context;

use OpenConext\EngineBlock\FunctionalTestingBundle\Parser\LogChunkParser;

/**
 * Class ReplayContext
 * @package OpenConext\EngineBlock\FunctionalTestingBundle\Features\Context
 */
class ReplayContext extends AbstractSubContext
{
    /**
     * @Then /^the request should be compared with the one at "([^"]*)"$/
     */
    public function theRequestShouldBeComparedWithTheOneAt($requestLogFile)
    {
        $node = $this->getMainContext()->getMinkContext()->getSession()->getPage()->findById('authnRequestXml');
        if (!$node) {
            throw new \RuntimeException('authnRequestXml id not found on page?');
        }
        $authnRequestXml = trim(html_entity_decode($node->getHtml()));
        if (empty($authnRequestXml)) {
            throw new \RuntimeException('authnRequestXml is on page, but no content found?');
        }

        // Parse a Response out of the log file
        $logReader = new LogChunkParser($requestLogFile);
        $request = $logReader->getMessage(LogChunkParser::MESSAGE_TYPE_AUTHN_REQUEST);

        $this->printDebug(print_r($request, true));

        $originalRequestXml = $this->formatXml($request->toXml());
        $replayedRequestXml = $this->formatXml($authnRequestXml);

        $this->printDebug($originalRequestXml);
        $this->printDebug($replayedRequestXml);

        $diff = new \Diff(
            explode("\n", $originalRequestXml),
            explode("\n", $replayedRequestXml)
        );
        $renderer = new \Diff_Renderer_Text_Unified;
        echo $diff->render($renderer);
    }

    /**
     * @Then /^the response should be compared with the one at "([^"]*)"$/
     */
    public function theResponseShouldBeComparedWithTheOneAt($responseLogFile)
    {
        // Parse a Response out of the log file
        $logReader = new LogChunkParser($responseLogFile);
        $response = $logReader->getMessage(LogChunkParser::MESSAGE_TYPE_RESPONSE);
        $originalResponseXml = $this->formatXml($response->toXml());
        $replayedResponseXml = $this->formatXml($this->getMainContext()->getPageContent());

        $this->printDebug($originalResponseXml);
        $this->printDebug($replayedResponseXml);

        $diff = new \Diff(
            explode("\n", $originalResponseXml),
            explode("\n", $replayedResponseXml)
        );
        $renderer = new \Diff_Renderer_Text_Unified;
        echo $diff->render($renderer);
    }

    /**
     * @param $xml
     * @return string
     */
    protected function formatXml($xml)
    {
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);
        $dom->formatOutput = true;
        return $dom->saveXml();
    }
}
