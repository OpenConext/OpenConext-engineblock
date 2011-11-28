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

class EngineBlock_Log_Writer_Mail extends Zend_Log_Writer_Mail
{
    public static function factory($config)
    {
        $options = self::_parseConfig($config);

        $mailClient = self::_getMailClient($options);
        $writer = new self($mailClient);

        if (isset($options['filterValues'])) {
            $writer->setFormatter(new EngineBlock_Log_Formatter_Mail($options['filterValues']));
        }

        $envName = ENGINEBLOCK_ENV;
        $writer->setSubjectPrependText("[EngineBlock][$envName]");

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

    protected static function _getMailClient($options)
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
}