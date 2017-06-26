<?php

class EngineBlock_View
{
    protected $_data = array();

    /**
     * @var Zend_Layout
     */
    private $layout;

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
            $symfonyPath = $this->layout()->getViewBasePath() . $viewScriptPath;
            if (!file_exists($symfonyPath)) {
                throw new EngineBlock_Exception(sprintf(
                    'View script "%s" does not exist as a file itself and also not as a file at "%s"',
                    $viewScriptPath,
                    $symfonyPath
                ));
            }

            $viewScriptPath = $symfonyPath;
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

    /**
     * Allows for injection as used in the CompatibilityBundle services.xml, without having to change the constructor
     * @param Zend_Layout $layout
     */
    public function setLayout(Zend_Layout $layout)
    {
        $this->layout = $layout;
    }

    public function layout()
    {
        if ($this->layout) {
            return $this->layout;
        }

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

        if (count($arguments) === 1) {
            return $arguments[0];
        }

        return call_user_func_array('sprintf', $arguments);
    }

    public function getAttributeName($attributeId, $ietfLanguageTag = 'en')
    {
        return EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getAttributeMetadata()
            ->getName($attributeId, $ietfLanguageTag);
    }

    /**
     * Get user-friendly attribute source name.
     *
     * @param $source Source identifier (e.g. "voot")
     * @return string
     */
    public function getAttributeSourceDisplayName($source)
    {
        return $this->translate('consent_attribute_source_' . strtolower($source));
    }

    /**
     * Return the language.
     *
     * @example <?php echo $this->getLocale(); ?>
     *
     * @return string
     */
    public function getLocale()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getLocaleProvider()->getLocale();
    }

    /**
     * Set the language on the query string and return the new query string
     *
     * @example <?php echo $this->setLanguage('en'); ?>
     *
     * @param string $lang the language to set
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

    public static function htmlSpecialCharsText($content)
    {
        return htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
    }

    public static function htmlSpecialCharsAttributeValue($content)
    {
        return htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Sort, group and normalize attributes for display on the consent page.
     *
     * To preserve backwards compatibility in consent.phtml, the attributes are
     * not grouped by attribute source when $attributeSources is omitted.
     *
     * @param $attributes
     * @param array|null $attributeSources
     * @return array
     */
    public function sortByDisplayOrder($attributes, array $attributeSources = null)
    {
        $attributeMetadata = EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getAttributeMetadata();

        $sortedAttributes = $attributeMetadata->sortByDisplayOrder($attributes);
        $normalizedAttributes = $attributeMetadata->normalizeEptiAttributeValue($sortedAttributes);

        if ($attributeSources === null) {
            return $normalizedAttributes;
        }

        return $this->groupAttributesBySource($normalizedAttributes, $attributeSources);
    }

    private function groupAttributesBySource($attributes, array $attributeSources = array())
    {
        $groupedAttributes = array(
            'idp' => array(),
        );

        foreach ($attributes as $attributeName => $attributeValue) {
            if (isset($attributeSources[$attributeName])) {
                $sourceName = $attributeSources[$attributeName];
            } else {
                $sourceName = 'idp';
            }

            $groupedAttributes[$sourceName][$attributeName] = $attributeValue;
        }

        return $groupedAttributes;
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
