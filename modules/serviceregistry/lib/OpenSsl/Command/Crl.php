<?php
/**
 *
 */

/**
 *
 */
class OpenSsl_Command_Crl extends Shell_Command_Abstract
{
    const COMMAND = "openssl crl";

    const FORM_PEM = 'PEM';
    const FORM_DER = 'DER';

    protected $_inFile;
    protected $_outFile;
    protected $_inForm;
    protected $_outForm;


    public function __construct()
    {
    }

    public function setInFile($filepath)
    {
        $this->_inFile = $filepath;
        return $this;
    }

    public function setInForm($form)
    {
        if (!in_array($form, array(FORM_PEM, FORM_DER))) {
            throw new OpenSsl_Exception_UnsupportedForm("");
        }

        $this->_inForm = $form;
        return $this;
    }

    public function setOutForm($form)
    {
        if (!in_array($form, array(FORM_PEM, FORM_DER))) {
            throw new OpenSsl_Exception_UnsupportedForm("");
        }

        $this->_outForm = $form;
        return $this;
    }

    public function setOutFile($file)
    {
        $this->_outFile = $file;
        return $this;
    }

    public function setPrintAsText()
    {
    }

    public function setNoPrintEncoded()
    {
    }

    public function setPrintIssuerNameHash()
    {
    }

    public function setPrintIssuer()
    {
    }

    public function setPrintLastUpdate()
    {
    }

    public function setPrintNextUpdate()
    {
    }

    public function setCertificateAuthorityFile()
    {
    }

    public function setCertificateAuthorityPath()
    {
    }

    protected function _buildCommand()
    {
        return self::COMMAND;
    }
}