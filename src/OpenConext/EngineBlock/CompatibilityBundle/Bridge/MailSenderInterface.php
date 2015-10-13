<?php

namespace OpenConext\EngineBlock\CompatibilityBundle\Bridge;

interface MailSenderInterface
{
    /**
     * @param MailMessage $message
     * @return void
     */
    public function send(MailMessage $message);
}
