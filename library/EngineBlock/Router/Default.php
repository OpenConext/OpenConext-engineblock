<?php


/**
 * Default router, expects a format like /module/controller/action and routes it.
 */
class EngineBlock_Router_Default extends EngineBlock_Router_Abstract
{
    protected $DEFAULT_MODULE_NAME     = "Default";
    protected $DEFAULT_CONTROLLER_NAME = "Index";
    protected $DEFAULT_ACTION_NAME     = "index";

    /**
     * Note that this router interprets ////tekno as /tekno, NOT as /default/index/index/tekno
     *
     * @param  $uri
     * @return bool
     */
    public function route($uri)
    {
        $urlParts = preg_split('/\//', $uri, 0, PREG_SPLIT_NO_EMPTY);
        $urlPartsCount = count($urlParts);

        $module     = $this->DEFAULT_MODULE_NAME;
        $controller = $this->DEFAULT_CONTROLLER_NAME;
        $action     = $this->DEFAULT_ACTION_NAME;
        $arguments  = array();

        // Note how we actually use the fall-through
        switch($urlPartsCount)
        {
            case 3:
                if ($urlParts[2]) {
                    $action     = $urlParts[2];
                }

            case 2:
                if ($urlParts[1]) {
                    $controller = $urlParts[1];
                }

            case 1:
                if ($urlParts[0]) {
                    $module     = $urlParts[0];
                }

            case 0:
                break;

            default: // URL: /authentication/idp/single-sign-on/myidp/other/arguments/in/url
                if ($urlParts[2]) {
                    $action     = $urlParts[2];
                }
                if ($urlParts[1]) {
                    $controller = $urlParts[1];
                }
                if ($urlParts[0]) {
                    $module     = $urlParts[0];
                }
                $arguments = array_slice($urlParts, 3);
        }

        $this->_moduleName      = $module;
        $this->_controllerName  = $controller;
        $this->_actionName      = $action;
 
        $this->setActionArguments($arguments);

        return true;
    }
}
