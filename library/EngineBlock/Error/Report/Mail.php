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

class EngineBlock_Error_Report_Mail implements EngineBlock_Error_Report_Interface
{
    const DEFAULT_FROM = 'SURFnet EngineBlock <coin-tech@surfnet.nl>';
    const DEFAULT_SUBJECT_TEMPLATE = '[EngineBlock Error<?php echo " " . EngineBlock_ApplicationSingleton::getInstance()->getApplicationEnvironmentId(); ?>] <?php echo $exception->getMessage(); ?>';
    const DEFAULT_MESSAGE_TEMPLATE = '
Oops! Something has gone wrong! Here is the original error:
<?php var_dump($exception); ?>

REQUEST:
<?php var_dump($_REQUEST); ?>

SESSION:
<?php var_dump($_SESSION); ?>

SERVER:
<?php var_dump($_SERVER); ?>
';


    /**
     * @var Zend_Config
     */
    protected $_config;
    protected $_originalIniSettings = array();

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function report(Exception $exception)
    {
        if (!isset($this->_config->to)) {
            throw new Exception('No to address set for mail reporter, unable to report!');
        }

        $from               = (isset($this->_config->from) ? (string)$this->_config->from : self::DEFAULT_FROM);
        $to                 = implode(',', $this->_config->to->toArray());
        $subjectTemplate    = (isset($this->_config->subjectTemplate) ? (string)$this->_config->get('subjectTemplate'):self::DEFAULT_SUBJECT_TEMPLATE);
        $messageTemplate    = (isset($this->_config->messageTemplate) ? (string)$this->_config->get('messageTemplate'):self::DEFAULT_MESSAGE_TEMPLATE);

        $subject = $this->_parseTemplate($subjectTemplate, array('exception'=>$exception));
        $message = $this->_parseTemplate($messageTemplate, array('exception'=>$exception));

        mail($to, $subject, $message, "From: $from");
    }

    protected function _parseTemplate($template, $vars = array())
    {
        $this->_overloadIniSetting('xdebug.overload_var_dump', false);

        extract($vars);

        ob_start();
        eval('?>' . $template . '<?');
        $output = ob_get_clean();
        ob_end_clean();

        $this->_restoreIniSetting('xdebug.overload_var_dump');
        return $output;
    }

    protected function _overloadIniSetting($name, $value)
    {
        $this->_originalIniSettings[$name] = ini_get($name);
        ini_set($name, $value);
    }

    protected function _restoreIniSetting($name)
    {
        ini_set($name, $this->_originalIniSettings[$name]);
    }
}
