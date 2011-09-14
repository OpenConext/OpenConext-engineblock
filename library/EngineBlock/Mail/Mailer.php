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

class EngineBlock_Mail_Mailer
{

    /**
     * @throws EngineBlock_Exception in case there is no EmailConfiguration in emails table
     * @param $samlAttributes all of the SAML attributes
     * @param $emailType the pointer to the emails configuration
     * @param $replacements array where the key is a variable (e.g. {user}) and the value the string where the variable should be replaced
     * @return void
     */
    public function sendMail($samlAttributes, $emailType, $replacements)
    {
        //can't mail to nobody
        if (!isset($samlAttributes['urn:mace:dir:attribute-def:mail'])) {
            return;
        }
        $dbh = $this->_getDatabaseConnection();
        $query = "SELECT email_text, email_from, email_subject, is_html FROM emails where email_type = ?";
        $parameters = array($emailType);
        $statement = $dbh->prepare($query);
        $statement->execute($parameters);
        $rows = $statement->fetchAll();
        if (count($rows) !== 1) {
            // No configured email content found
            throw new EngineBlock_Exception("Error sending introduction email. Please configure an email with email_type " . $emailType);
        }
        $emailText = $rows[0]['email_text'];
        //we want a default replacement (can be overridden) of the username
        $emailText = str_ireplace('{user}', $this->_getUserName($samlAttributes), $emailText);
        foreach ($replacements as $key => $value) {
            $emailText = str_ireplace($key, $value, $emailText);
        }
        $emailFrom = $rows[0]['email_from'];
        $emailAddress = $samlAttributes['urn:mace:dir:attribute-def:mail'][0];
        $emailSubject = $rows[0]['email_subject'];
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($emailText, 'utf-8', 'utf-8');
        $mail->setFrom($emailFrom);
        $mail->addTo($emailAddress);
        $mail->setSubject($emailSubject);
        $mail->send();


    }

    protected function _getUserName($samlAttributes)
    {
        if (isset($samlAttributes['urn:mace:dir:attribute-def:givenName']) && isset($samlAttributes['urn:mace:dir:attribute-def:sn'])) {
            return $samlAttributes['urn:mace:dir:attribute-def:givenName'][0] . ' ' . $samlAttributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($samlAttributes['urn:mace:dir:attribute-def:cn'])) {
            return $samlAttributes['urn:mace:dir:attribute-def:cn'][0];
        }

        if (isset($samlAttributes['urn:mace:dir:attribute-def:displayName'])) {
            return $samlAttributes['urn:mace:dir:attribute-def:displayName'][0];
        }

        if (isset($samlAttributes['urn:mace:dir:attribute-def:givenName'])) {
            return $samlAttributes['urn:mace:dir:attribute-def:givenName'][0];
        }

        if (isset($samlAttributes['urn:mace:dir:attribute-def:sn'])) {
            return $samlAttributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($samlAttributes['urn:mace:dir:attribute-def:mail'])) {
            return $samlAttributes['urn:mace:dir:attribute-def:mail'][0];
        }

        if (isset($samlAttributes['urn:mace:dir:attribute-def:uid'])) {
            return $samlAttributes['urn:mace:dir:attribute-def:uid'][0];
        }

        return "";

    }

    /**
     * @return PDO
     */
    protected function _getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
    }

}