<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Service;

use Swift_Mailer;
use Swift_Message;

class RequestAccessMailer
{
    const REQUEST_IDP_ACCESS_SUBJECT = 'Request for IdP access';
    const REQUEST_IDP_ACCESS_TEMPLATE = <<<TPL
There has been a request to allow access for IdP %s ('%s') to SP %s ('%s'). The request was made by:

%s <%s>

The comment was:

%s

TPL;
    const REQUEST_INSTITUTION_ACCESS_SUBJECT = 'Request for institution access';
    const REQUEST_INSTITUTION_ACCESS_TEMPLATE = <<<TPL
There has been a request to allow access for institution '%s' to SP %s ('%s'). The request was made by:

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
     * Request access for a specific (known to OpenConext) IDP.
     *
     * @param $spName
     * @param $spEntityId
     * @param $institution
     * @param $idpEntityId
     * @param $name
     * @param $email
     * @param $comment
     */
    public function sendRequestAccessEmailForIdp($spName, $spEntityId, $institution, $idpEntityId, $name, $email, $comment)
    {
        $subject = self::REQUEST_IDP_ACCESS_SUBJECT;
        $body = sprintf(
            self::REQUEST_IDP_ACCESS_TEMPLATE,
            $institution,
            $idpEntityId,
            $spName,
            $spEntityId,
            $name,
            $email,
            $comment
        );

        // We use the destination email address also as a From since we do
        // not have a better generic sender address available currently.
        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($this->requestAccessEmailAddress)
            ->setTo($this->requestAccessEmailAddress)
            ->setBody($body, 'text/plain');

        $this->mailer->send($message);
    }

    /**
     * Request access for a institution perhaps not yet available to OpenConext.
     *
     * @param $spName
     * @param $spEntityId
     * @param $institution
     * @param $name
     * @param $email
     * @param $comment
     */
    public function sendRequestAccessEmailForInstitution($spName, $spEntityId, $institution, $name, $email, $comment)
    {
        $subject = self::REQUEST_INSTITUTION_ACCESS_SUBJECT;
        $body = sprintf(
            self::REQUEST_INSTITUTION_ACCESS_TEMPLATE,
            $institution,
            $spName,
            $spEntityId,
            $name,
            $email,
            $comment
        );

        $message = new Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($this->requestAccessEmailAddress)
            ->setTo($this->requestAccessEmailAddress)
            ->setBody($body, 'text/plain');


        $this->mailer->send($message);
    }
}
