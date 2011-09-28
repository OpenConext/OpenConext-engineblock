<?php
/**
 *
 */

/**
 *
 */
class ServiceRegistry_Cron_Job_ValidateEntityCertificate
{
    const CONFIG_WITH_TAGS_TO_RUN_ON = 'validate_entity_certificate_cron_tags';

    public function __construct()
    {
    }

    public function runForCronTag($cronTag)
    {
        if (!$this->_isExecuteRequired($cronTag)) {
            return array();
        }

        $cronLogger = new ServiceRegistry_Cron_Logger();
        try {
            $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
            $srConfig = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');
            $rootCertificatesFile = $srConfig->getString('ca_bundle_file');

            $util = new sspmod_janus_AdminUtil();
            $entities = $util->getEntities();

            foreach ($entities as $partialEntity) {
                try {
                    $entityController = new sspmod_serviceregistry_EntityController($janusConfig);

                    $eid = $partialEntity['eid'];
                    if (!$entityController->setEntity($eid)) {
                        $cronLogger->with($eid)->error("Failed import of entity. Wrong eid '$eid'.");
                        continue;
                    }

                    $entityController->loadEntity();
                    $entityId = $entityController->getEntity()->getEntityid();
                    $entityType = $entityController->getEntity()->getType();
                    try {
                        try {
                            $certificate = $entityController->getCertificate();
                        }
                        catch (Exception $e) {
                            if ($entityType === 'saml20-sp') {
                                $cronLogger->with($entityId)->notice("SP does not have a certificate");
                            }
                            else if ($entityType=== 'saml20-idp') {
                                $cronLogger->with($entityId)->warn("Unable to create certificate object, certData missing?");
                            }
                            continue;
                        }
                        $validator = new OpenSsl_Certificate_Validator($certificate);
                        $validator->setIgnoreSelfSigned(true);
                        $validator->validate();

                        $validatorWarnings = $validator->getWarnings();
                        $validatorErrors = $validator->getErrors();
                        foreach ($validatorWarnings as $warning) {
                            $cronLogger->with($entityId)->warn($warning);
                        }
                        foreach ($validatorErrors as $error) {
                            $cronLogger->with($entityId)->error($error);
                        }

                        OpenSsl_Certificate_Chain_Factory::loadRootCertificatesFromFile($rootCertificatesFile);

                        $chain = OpenSsl_Certificate_Chain_Factory::createFromCertificateIssuerUrl($certificate);
                        $validator = new OpenSsl_Certificate_Chain_Validator($chain);
                        $validator->setIgnoreSelfSigned(true);
                        $validator->setTrustedRootCertificateAuthorityFile($rootCertificatesFile);
                        $validator->validate();

                        $validatorWarnings = $validator->getWarnings();
                        $validatorErrors = $validator->getErrors();
                        foreach ($validatorWarnings as $warning) {
                            $cronLogger->with($entityId)->warn($warning);
                        }
                        foreach ($validatorErrors as $error) {
                            $cronLogger->with($entityId)->error($error);
                        }
                    } catch (Exception $e) {
                        $cronLogger->with($entityId)->error($e->getMessage());
                    }
                } catch (Exception $e) {
                    $cronLogger->error($e->getMessage() . $e->getTraceAsString());
                }
            }
        } catch (Exception $e) {
            $cronLogger->error($e->getMessage() . $e->getTraceAsString());
        }
        $summaryLines = $cronLogger->getSummaryLines();
        if ($cronLogger->hasErrors()) {
            $this->_mailTechnicalContact($cronTag, $cronLogger);
        }
        return $summaryLines;
    }

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
    
    protected function _isExecuteRequired($cronTag)
    {
        $serviceRegistryConfig = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');

        $cronTags = $serviceRegistryConfig->getArray(self::CONFIG_WITH_TAGS_TO_RUN_ON, array());

        if (!in_array($cronTag, $cronTags)) {
            return false; // Nothing to do: it's not our time
        }
        return true;
    }
}
