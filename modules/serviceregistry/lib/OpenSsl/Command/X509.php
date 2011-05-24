<?php
/**
 * SURFconext Service Registry
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
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * 
 */
class OpenSsl_Command_X509 extends Shell_Command_Abstract
{
    const COMMAND = 'openssl x509';
    
    const FORM_PEM = 'PEM';
    const FORM_DER = 'DER';

    protected $_inFile;
    protected $_outFile;
    protected $_inForm;
    protected $_outForm;
    protected $_displayText;

    public function setDisplayText()
    {
        $this->_displayText = true;
        return $this;
    }

    public function setInFile($filepath)
    {
        $this->_inFile = $filepath;
        return $this;
    }

    public function setInForm($form)
    {
        if (!in_array($form, array(self::FORM_PEM, self::FORM_DER))) {
            throw new OpenSsl_Command_Exception_UnsupportedForm("");
        }

        $this->_inForm = $form;
        return $this;
    }

    public function setOutForm($form)
    {
        if (!in_array($form, array(self::FORM_PEM, self::FORM_DER))) {
            throw new OpenSsl_Command_Exception_UnsupportedForm("");
        }

        $this->_outForm = $form;
        return $this;
    }

    public function setOutFile($file)
    {
        $this->_outFile = $file;
        return $this;
    }

    public function _buildCommand()
    {
        $command = self::COMMAND;
        if ($this->_displayText) {
            $command .= ' -text';
        }
        if ($this->_inForm) {
            $command .= ' -inform ' . $this->_inForm;
        }
        if ($this->_outForm) {
            $command .= ' -outform ' . $this->_outForm;
        }
        if ($this->_inFile) {
            $command .= ' -in ' . $this->_inFile;
        }
        if ($this->_outFile) {
            $command .= ' -out ' . $this->_outFile;
        }
        return $command;
    }
}