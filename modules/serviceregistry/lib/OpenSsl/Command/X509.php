<?php
/**
 *
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