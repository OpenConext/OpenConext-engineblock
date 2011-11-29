<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Log_Writer_Mail extends Zend_Log_Writer_Abstract
{
    /**
     * @var EngineBlock_Log_Formatter_Mail
     */
    protected $_engineBlockFormatter;
    protected $_mailConfig;
    protected $_eventsToMail;
    protected $_subjectPrependText;

    public function __construct($options, $formatter)
    {
        $this->_mailConfig = $options;
        $this->_engineBlockFormatter = $formatter;
    }

    public function setSubjectPrependText($text)
    {
        $this->_subjectPrependText = $text;
    }

    public static function factory($config)
    {
        $options = self::_parseConfig($config);

        $formatter = new EngineBlock_Log_Formatter_Mail($options['filterValues']);

        $writer = new self($options, $formatter);

        $writer->setSubjectPrependText('[SURFconext][EngineBlock][' . gethostname() . ']');

        if (isset($options['filterName'])) {
            // has underscores
            if (strpos($options['filterName'], '_') !== false) {
                $className = 'Zend_Log_Filter_' . $options['filterName'];
            }
            else {
                $className = $options['filterName'];
            }

            $filter = new $className($options['filterParams']);
            $writer->addFilter($filter);
        }

        return $writer;
    }

    /**
     * Places Zend_Views into an array in order to send a formatted mail for every log message
     *
     * @param  array $event Event data
     * @return void
     */
    protected function _write($event)
    {
        $view = $this->_engineBlockFormatter->format($event);
        $this->_eventsToMail[] = $view;
    }

    /**
     * Sends mail to recipient(s) if log entries are present.  Note that both
     * plaintext and HTML portions of email are handled here.
     *
     * @return void
     */
    public function shutdown()
    {
        // If there are events to mail, send them separately as html mails in the given template.  Otherwise,
        // there is no mail to be sent.
        if (empty($this->_eventsToMail)) {
            return;
        }

        foreach ($this->_eventsToMail as $view) {
            $mail = $this->_getMailClient($this->_mailConfig);
            $layout = $this->_getMailLayout();

            if ($this->_subjectPrependText !== null) {
                // line and set it on the Zend_Mail object.
                $mail->setSubject(
                    "{$this->_subjectPrependText} {$this->_getShortenedMessage($view)}");
            }   
            // Always provide events to mail as plaintext.
            $mail->setBodyText($view->message);

            if ($layout) {
                $layout->setView($view);
            }

            // If an exception occurs during rendering, convert it to a notice
            // so we can avoid an exception thrown without a stack frame.
            try {
                $mail->setBodyHtml($layout->render());
            } catch (Exception $e) {
                trigger_error(
                    "exception occurred when rendering layout; " .
                    "unable to set html body for message; " .
                    "message = {$e->getMessage()}; " .
                    "code = {$e->getCode()}; " .
                    "exception class = " . get_class($e),
                    E_USER_NOTICE);
            }

            // Finally, send the mail.  If an exception occurs, convert it into a
            // warning-level message so we can avoid an exception thrown without a
            // stack frame.
            try {
                $mail->send();
            } catch (Exception $e) {
                trigger_error(
                    "unable to send log entries via email; " .
                    "message = {$e->getMessage()}; " .
                    "code = {$e->getCode()}; " .
                    "exception class = " . get_class($e),
                    E_USER_WARNING);
            }
        }

    }

    protected function _getMailClient($options)
    {
        $mail = new Zend_Mail('UTF-8');

        $mail->setFrom($options['from']['email'], $options['from']['name']);

        foreach ($options['to'] as $to) {
            if (is_array($to)) {
                $mail->addTo($to['email'], $to['name']);
            }
            else {
                $mail->addTo($to);
            }
        }

        if (isset($options['cc'])) {
            foreach ($options['cc'] as $bcc) {
                if (is_array($bcc)) {
                    $mail->addCc($bcc['email'], $bcc['name']);
                }
                else {
                    $mail->addCc($bcc);
                }
            }
        }

        if (isset($options['bcc'])) {
            foreach ($options['bcc'] as $bcc) {
                if (is_array($bcc)) {
                    $mail->addBcc($bcc['email'], $bcc['name']);
                }
                else {
                    $mail->addBcc($bcc);
                }
            }
        }
        return $mail;
    }

    protected function _getMailLayout()
    {
        $layout = new Zend_Layout();
        $layout->setLayoutPath(ENGINEBLOCK_FOLDER_APPLICATION . 'layouts/scripts');
        $layout->setLayout('error-mail');

        return $layout;
    }

    protected function _getShortenedMessage(Zend_View $view)
    {
        $message = $view->message;
        if (strlen($message) > 100) {
            $message = substr($message, 0, 100) . '...';
        }
        return $message;
    }
}