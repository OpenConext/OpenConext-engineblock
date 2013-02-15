<?php
/**
 * Copyright (c) 2007-2011, Rob Allen (rob@akrabat.com)
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *
 *     * The name of Rob Allen may not be used to endorse or promote products derived 
 *       from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class EngineBlock_Config_Ini extends Zend_Config_Ini
{
    /**
     * Load the INI file from disk using parse_ini_file(). Use a private error
     * handler to convert any loading errors into a Zend_Config_Exception
     *
     * @param string $filename
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _parseIniFile($filename)
    {
        set_error_handler(array($this, '_loadFileErrorHandler'));
        if (substr($filename, -4) == '.ini') {
            $iniArray = parse_ini_file($filename, true);
        } else {
            $iniArray = parse_ini_string($filename, true);
        }
        restore_error_handler();

        // Check if there was a error while loading file
        if ($this->_loadFileErrorStr !== null) {
            throw new EngineBlock_Exception('Parsing config file failed: ' . $this->_loadFileErrorStr, EngineBlock_Exception::CODE_ALERT);
        }

        return $iniArray;
    }
}