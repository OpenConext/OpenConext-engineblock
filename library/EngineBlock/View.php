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

class EngineBlock_View
{
    protected $_data = array();

    public function __construct()
    {
    }

    public function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    public function render($viewScriptPath, $useLayout = true)
    {
        if (!file_exists($viewScriptPath)) {
            throw new EngineBlock_Exception("View $viewScriptPath does not exist");
        }

        extract($this->_data);

        ob_start();
        require $viewScriptPath;
        $renderedView = ob_get_contents();
        ob_end_clean();

        if (!$useLayout) {
            return $renderedView;
        }

        $layout = $this->layout();
        $layout->content = $renderedView;
        $renderedPage = $layout->render();

        return $renderedPage;
    }

    public function layout()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getLayout();
    }

    /**
     * Translate a string.
     *
     * Alias for 'translate'
     *
     * @example <?php echo $this->t('logged_in_as', $this->user->getDisplayName()); ?>
     *
     * @param string $from Identifier for string
     * @param string $arg1 Argument to parse in with sprintf
     * @return string
     */
    public function t($from, $arg1 = null)
    {
        return call_user_func_array(array($this, 'translate'), func_get_args());
    }

    /**
     * Translate a string.
     *
     * Has an alias called 't'.
     *
     * @example <?php echo $this->translate('logged_in_as', $this->user->getDisplayName()); ?>
     *
     * @param string $from Identifier for string
     * @param string $arg1 Argument to parse in with sprintf
     * @return string
     */
    public function translate($from, $arg1 = null)
    {
        $translator = EngineBlock_ApplicationSingleton::getInstance()->getTranslator()->getAdapter();

        $arguments = func_get_args();
        $arguments[0] = $translator->translate($from);
        return call_user_func_array('sprintf', $arguments);
    }

    /**
     * Get the displayName for the entity
     *
     * @param array $entity the rmeote entity metadata
     * @return the name to display
     */
    public function getDisplayName(array $entity) {
        $lang = $this->language();
        $lang != null ? $lang : 'en';

        $displayAttributes = array('name', 'description', 'displayName');
        foreach ($displayAttributes as $key) {
            $ret = $this->_getLanguageFallback($entity, $key, $lang);
            if ($ret) {
                return $ret;
            }
        }
        return "Unknown (meta-data incomplete)";
    }

    /**
     * Return the language.
     *
     * @example <?php echo $this->language(); ?>
     *
     * @return string
     */
    public function language()
    {
        $translator = EngineBlock_ApplicationSingleton::getInstance()->getTranslator()->getAdapter();
        return $translator->getLocale();
    }

    /**
     * Return the url of the Profile vhost
     *
     * @example <?php echo $this->profileUrl(); ?>
     *
     * @return string
     */
    public static function profileUrl($path = "")
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $settings = $application->getConfiguration();
        return $settings->profile->protocol . '://'. $settings->profile->host . $path;
    }

    /**
     * Return the module name
     *
     * @return string
     */
    public static function moduleName()
    {
        $serverName = $_SERVER['SERVER_NAME'];
        return $serverName ? strtolower(trim(substr($serverName, 0, strpos($serverName, '.')))) : 'engine';
    }

    /**
     * Set the language on the query string and return the new query string
     *
     * @example <?php echo $this->setLanguage('en'); ?>
     *
     * @param $lang the language to set
     * @return string the new query string
     */
    public static function setLanguage($lang)
    {
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $params = array(
            'lang' => $lang
        );

        if ($request->getMethod() === 'POST') {
            // re-create URL from POST parameters
            $params = array_merge(
                self::_getQueryParametersFromPost($request), $params
            );
        } else {
            // re-create URL from GET parameters
            $params = array_merge(
                $request->getQueryParameters(), $params
            );
        }

        $query = '';
        foreach ($params as $key => $value) {
            $query .= (strlen($query) == 0) ? '?' : '&' ;
            $query .= $key. '=' .urlencode($value);
        }

        return $query;
    }

    /**
     * This method takes the POST parameters of a request and returns
     * the GET parameters that can be used to reload the page. The
     * following transformations are done on the SAMLRequest value:
     *
     *  - base64 decode
     *  - gzip message
     *  - base64 encode
     *
     * This allows the SSO service to use 'receiveMessageFromHttpRedirect' to
     * parse the message, while initially 'receiveMessageFromHttpPost' was used.
     *
     * @param EngineBlock_Http_Request $request
     * @return array $params
     */
    protected static function _getQueryParametersFromPost(EngineBlock_Http_Request $request)
    {
        $params = $request->getPostParameters();
        if (!empty($params['SAMLRequest'])) {
            $message = base64_decode($params['SAMLRequest']);
            $params['SAMLRequest'] = base64_encode(gzdeflate($message));
        }

        return $params;
    }

    protected function _getLanguageFallback(array $entity, $key, $lang) {
        if (isset($entity["$key:$lang"])) {
            return $entity["$key:$lang"];
        }
        $lang = ($lang == 'en' ? 'nl' : 'en');
        if (isset($entity["$key:$lang"])) {
            return $entity["$key:$lang"];
        }
        return null;
    }

}