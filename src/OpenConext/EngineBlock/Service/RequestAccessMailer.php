<?php

namespace OpenConext\EngineBlock\Service;

use Swift_Mailer;
use Swift_Message;

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
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $requestAccessEmailAddress;

    /**
     * @param Swift_Mailer $mailer
     * @param string $requestAccessEmailAddress
     */
    public function __construct(Swift_Mailer $mailer, $requestAccessEmailAddress)
    {
        $this->mailer = $mailer;
        $this->requestAccessEmailAddress = $requestAccessEmailAddress;
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

        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($email, $name)
            ->setTo($this->requestAccessEmailAddress)
            ->setBody($body, 'text/plain');

        $this->mailer->send($message);
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

        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($email, $name)
            ->setTo($this->requestAccessEmailAddress)
            ->setBody($body, 'text/plain');


        $this->mailer->send($message);
    }
}
