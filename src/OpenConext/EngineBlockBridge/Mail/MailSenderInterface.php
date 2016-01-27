<?php

namespace OpenConext\EngineBlockBridge\Mail;

interface MailSenderInterface
{
    /**
     * @param MailMessage $message
     * @return void
     */
    public function send(MailMessage $message);
}
