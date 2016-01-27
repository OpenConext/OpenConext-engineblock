<?php

namespace OpenConext\EngineBlockBridge\Mail;

use OpenConext\EngineBlockBundle\Exception\InvalidArgumentException;

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
        if (!is_string($subject) || trim($subject) === '') {
            throw InvalidArgumentException::invalidType('non-empty string', 'subject', $subject);
        }

        if (filter_var($onBehalfOf, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidArgumentException::invalidType('RFC 822 compliant email address', 'onBehalfOf', $onBehalfOf);
        }

        if (!is_string($body) || trim($body) === '') {
            throw InvalidArgumentException::invalidType('non-empty string', 'body', $body);
        }

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
