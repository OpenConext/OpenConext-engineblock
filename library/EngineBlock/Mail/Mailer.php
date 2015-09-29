<?php

class EngineBlock_Mail_Mailer
{
    /**
     * Send a mail based on the configuration in the emails table
     *
     * @throws EngineBlock_Exception in case there is no EmailConfiguration in emails table
     * @param string $emailAddress the email address of the recipient
     * @param string $emailType the pointer to the emails configuration
     * @param array $replacements array where the key is a variable (e.g. {user}) and the value the string where the variable should be replaced
     * @return void
     */
    public function sendMail($emailAddress, $emailType, $replacements)
    {
        $dbh = $this->_getDatabaseConnection();
        $query = "SELECT email_text, email_from, email_subject, is_html FROM emails where email_type = ?";
        $parameters = array($emailType);
        $statement = $dbh->prepare($query);
        $statement->execute($parameters);
        $rows = $statement->fetchAll();
        if (count($rows) !== 1) {
            EngineBlock_ApplicationSingleton::getLog()->error(
                "Unable to send mail because of missing email configuration: " . $emailType
            );
            return;
        }

        $emailText = $rows[0]['email_text'];
        foreach ($replacements as $key => $value) {
            // Single value replacement
            if (!is_array($value)) {
                $emailText = str_ireplace($key, $value, $emailText);
            }
            // Multi value replacement
            else {
                $replacement = '<ul>';
                foreach ($value as $valElem) {
                    $replacement .= '<li>' . $valElem . '</li>';
                }
                $replacement .= '</ul>';
                $emailText = str_ireplace($key, $replacement, $emailText);
            }
        }
        $emailFrom = $rows[0]['email_from'];
        $emailSubject = $rows[0]['email_subject'];
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($emailText, 'utf-8', 'utf-8');
        $mail->setFrom($emailFrom, "SURFconext Support");
        $mail->addTo($emailAddress);
        $mail->setSubject($emailSubject);
        $mail->send();
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
