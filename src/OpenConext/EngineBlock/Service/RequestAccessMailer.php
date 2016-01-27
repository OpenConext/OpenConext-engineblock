<?php

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlockBridge\Mail\MailMessage;
use OpenConext\EngineBlockBridge\Mail\MailSenderInterface;

class RequestAccessMailer
{
    const REQUEST_ACCESS_SUBJECT = 'Request for IdP access (%s)';
    const REQUEST_ACCESS_TEMPLATE = <<<TPL
There has been a request to allow access for IdP '%s' to SP '%s'. The request was made by:

%s <%s>

The comment was:

%s

TPL;
    const REQUEST_INSTITUTION_ACCESS_SUBJECT = 'Request for institution access (%s)';
    const REQUEST_INSTITUTION_ACCESS_TEMPLATE = <<<TPL
There has been a request to allow access for institution '%s' to SP '%s'. The request was made by:

%s <%s>

The comment was:

%s

TPL;

    /**
     * @var MailSenderInterface
     */
    private $mailSender;

    /**
     * @param MailSenderInterface $mailSender
     */
    public function __construct(MailSenderInterface $mailSender)
    {
        $this->mailSender = $mailSender;
    }

    /**
     * @param string $identityProvider
     * @param string $serviceProvider
     * @param string $name
     * @param string $email
     * @param string $comment
     */
    public function sendRequestAccessEmail($identityProvider, $serviceProvider, $name, $email, $comment)
    {
        $subject = sprintf(self::REQUEST_ACCESS_SUBJECT, gethostname());
        $body = sprintf(self::REQUEST_ACCESS_TEMPLATE, $identityProvider, $serviceProvider, $name, $email, $comment);

        $message = new MailMessage($subject, $email, $body);

        $this->mailSender->send($message);
    }

    /**
     * @param string $serviceProvider
     * @param string $name
     * @param string $email
     * @param string $institution
     * @param string $comment
     */
    public function sendRequestAccessToInstitutionEmail($serviceProvider, $name, $email, $institution, $comment)
    {
        $subject = sprintf(self::REQUEST_INSTITUTION_ACCESS_SUBJECT, gethostname());
        $body = sprintf(
            self::REQUEST_INSTITUTION_ACCESS_TEMPLATE,
            $institution,
            $serviceProvider,
            $name,
            $email,
            $comment
        );

        $message = new MailMessage($subject, $email, $body);

        $this->mailSender->send($message);
    }
}
