<?php

namespace OpenConext\EngineBlockBridge\Mail;

use Exception;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Zend_Mail;

class ZendMailSender implements MailSenderInterface
{
    /**
     * @var string
     */
    private $toAddress;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string          $toAddress
     * @param LoggerInterface $logger
     */
    public function __construct($toAddress, LoggerInterface $logger)
    {
        Assertion::nonEmptyString($toAddress, $toAddress);

        $this->toAddress = $toAddress;
        $this->logger = $logger;
    }

    /**
     * @param MailMessage $message
     * @return void
     * @throws \Zend_Mail_Exception
     */
    public function send(MailMessage $message)
    {
        $mail = new Zend_Mail('UTF-8');
        $mail->addTo($this->toAddress);

        $mail->setFrom($message->getOnBehalfOf());
        $mail->setSubject($message->getSubject());
        $mail->setBodyText($message->getBody());

        $this->logger->debug('Attempting to send email');

        try {
            $mail->send();
        } catch (Exception $e) {
            $message = sprintf(
                'Sending an email caused an exception to be thrown: "%s[%d]", message: "%s"',
                get_class($e),
                $e->getCode(),
                $e->getMessage()
            );

            $this->logger->error($message);

            throw new RuntimeException($message, $e->getCode(), $e);
        }

        $this->logger->debug('Successfully sent email');
    }
}
