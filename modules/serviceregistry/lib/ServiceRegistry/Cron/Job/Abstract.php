<?php

abstract class ServiceRegistry_Cron_Job_Abstract implements ServiceRegistry_Cron_Job_Interface
{
    protected function _mailTechnicalContact($tag, ServiceRegistry_Cron_Logger $logger)
    {
        $errorHtml   = $this->_getHtmlForMessages($logger->getNamespacedErrors(), 'errors');
        $warningHtml = $this->_getHtmlForMessages($logger->getNamespacedWarnings(), 'warnings');
        $noticeHtml  = $this->_getHtmlForMessages($logger->getNamespacedNotices(), 'notices');

        $config = SimpleSAML_Configuration::getInstance();
        $time = date(DATE_RFC822);
        $url = SimpleSAML_Utilities::selfURL();

        $message = <<<MESSAGE
<h1>Cron report</h1>
<p>Cron ran at $time</p>
<p>URL: <tt>$url</tt></p>
<p>Tag: $tag</p>
<h2>Errors</h2>
$errorHtml
<h2>Warnings</h2>
$warningHtml
<h2>Notices</h2>
$noticeHtml
MESSAGE;

        $toAddress = $config->getString('technicalcontact_email', 'na@example.org');
        if ($toAddress == 'na@example.org') {
            SimpleSAML_Logger::error('Cron - Could not send email. [technicalcontact_email] not set in config.');
        } else {
            $email = new SimpleSAML_XHTML_EMail($toAddress, 'ServiceRegistry cron report', 'coin-beheer@surfnet.nl');
            $email->setBody($message);
            $email->send();
        }
    }

    protected function _getHtmlForMessages($messages, $type)
    {
        if (count($messages) > 0) {
            $messageHtml = '<ul>';
            foreach ($messages as $label => $message) {
                $messageHtml .= '<li>';
                if (is_array($message)) {
                    $messageHtml .= $this->_getListForMessages($message, $label);
                }
                else {
                    $messageHtml .= $message;
                }
                $messageHtml .= "</li>";
            }
            $messageHtml .= '</ul>';
        }
        else {
            $messageHtml = "<p>No $type</p>";
        }
        return $messageHtml;
    }

    protected function _getListForMessages($messages, $label)
    {
        $html = "<dl><dt>$label</dt>";
        foreach ($messages as $label => $message) {
            if (is_array($message)) {
                $html .= "<dd>" . $this->_getListForMessages($message, $label) . "</dd>";
            }
            else {
                $html .= "<dd>$message</dd>";
            }
        }
        $html .= "</dl>";
        return $html;
    }
}