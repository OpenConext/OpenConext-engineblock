<?php

namespace OpenConext\EngineBlockBridge\Mail;

use OpenConext\EngineBlock\Assert\Assertion;

class MailMessage
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $onBehalfOf;

    /**
     * @var string
     */
    private $body;

    /**
     * @param string $subject
     * @param string $onBehalfOf
     * @param string $body
     */
    public function __construct($subject, $onBehalfOf, $body)
    {
        Assertion::nonEmptyString($subject, 'subject');
        Assertion::email($onBehalfOf, 'Expceted RFC 822 compliant email address, "%s" given', 'onBehalfOf');
        Assertion::nonEmptyString($body, 'body');

        $this->subject    = $subject;
        $this->onBehalfOf = $onBehalfOf;
        $this->body       = $body;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getOnBehalfOf()
    {
        return $this->onBehalfOf;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
