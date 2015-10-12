<?php

namespace OpenConext\EngineBlock\CompatibilityBundle\Bridge;

interface MailSenderInterface
{
    /**
     * @param MailMessage $message
     * @return void
     * @throws \Zend_Mail_Exception
     */
    public function send(MailMessage $message);
}
