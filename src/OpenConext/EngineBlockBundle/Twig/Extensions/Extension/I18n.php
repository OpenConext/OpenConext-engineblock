<?php
namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use Twig_Extensions_Extension_I18n;
use Zend_Translate_Adapter;

class I18n extends Twig_Extensions_Extension_I18n implements \Twig_Extension_InitRuntimeInterface
{

    /**
     * @var Zend_Translate_Adapter
     */
    private $translator;

    public function __construct(Zend_Translate_Adapter $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array($this, 'translateSingular')),
            new \Twig_SimpleFilter('transchoice', array($this, 'translatePlural')),
        );
    }


    /**
     * @return string
     */
    public function translateSingular()
    {
        $arguments = func_get_args();
        $arguments[0] = $this->translator->translate($arguments[0]);

        if (count($arguments) === 1) {
            return $arguments[0];
        }

        return call_user_func_array('sprintf', $arguments);
    }


    /**
     * Wrapper around the given callable we have to use to translate plural strings.
     *
     * Defaults to ngettext().
     *
     * @return string
     */
    public function translatePlural()
    {
        $args = func_get_args();
        return call_user_func_array($this->plural, $args);
    }
}
