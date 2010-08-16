<?php
 
class EngineBlock_Dispatcher 
{
    private $_routers = array();

    public function __construct()
    {
        $this->_routers[] = new EngineBlock_Router_Default();
    }

    public function getRouters()
    {
        return $this->_routers;
    }

    public function setRouters($routers)
    {
        $this->_routers = $routers;
        return $this;
    }

    public function dispatch($uri = "")
    {
        if (!$uri) {
            $uri = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getUri();
        }

        $router = $this->_getRouter($uri);
       
        $module             = $router->getModuleName();
        $controllerName     = $router->getControllerName();
        $action             = $router->getActionName();
        $attributeArguments = $router->getActionArguments();

        $controllerInstance = $this->_getControllerInstance($module, $controllerName);

        if (!$controllerInstance->hasAction($action)) {
            // @todo error out!
            throw new EngineBlock_Exception("Unable to load action '$action'");
        }

        $controllerInstance->handleAction($action, $attributeArguments);
    }

    protected function _getControllerInstance($module, $controllerName)
    {
        $className = $this->_getControllerClassName($module, $controllerName);
        if (!class_exists($className)) {
            // @todo error out!
            throw new EngineBlock_Exception("Unable to load $className");
        }

        $controllerInstance = new $className($module, $controllerName);

        if (!($controllerInstance instanceof EngineBlock_Controller_Abstract)) {
            throw new EngineBlock_Exception("Controller $className is not an EngineBlock controller (does not extend EngineBlock_Controller_Abstract)!");
        }

        return $controllerInstance;
    }

    /**
     * 
     *
     * @param  string $uri
     * @return EngineBlock_Router_Abstract
     */
    protected function _getRouter($uri)
    {
        /**
         * @var EngineBlock_Router_Abstract $router
         */
        foreach ($this->_routers as $router) {
            $routable = $router->route($uri);
            if ($routable) {
                break;
            }
        }
        return $router;
    }

    protected function _getControllerClassName($module, $controller)
    {
        return ucfirst($module) . '_Controller_' . $controller;
    }
}
