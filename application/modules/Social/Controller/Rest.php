<?php
 
class Social_Controller_Rest 
{
    public function indexAction($url)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . ENGINEBLOCK_FOLDER_LIBRARIES . 'shindig/php/');

        include_once('src/common/Config.php');
        include_once('src/common/File.php');

        // You can't inject a Config, so force it
        // to try loading and ignore errors from config file not being there :(
        global $shindigConfig;
        $shindigConfig = array();
        @Config::setConfig(array());

        spl_autoload_register(array(get_class($this), 'shindigAutoLoad'));

        $_REQUEST['REQUEST_URI'] = '/social/rest/' . $url;

        $requestMethod = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getMethod();
        $methodName = 'do' . ucfirst(strtolower($requestMethod));

        $servletInstance = new DataServiceServlet();
        if (is_callable($methodName)) {
            $servletInstance->$methodName();
        }
        else {
            // @todo Error out
        }
    }

    public static function shindigAutoLoad($className)
    {
        $locations = array(
            'src/common',
            'src/common/sample',
            'src/gadgets',
            'src/gadgets/servlet',
            'src/gadgets/oauth',
            'src/gadgets/sample',
            'src/social',
            'src/social/servlet',
            'src/social/service',
            'src/social/opensocial',
            'src/social/model',
            'src/social/spi',
            'src/social/converters',
            'src/social/oauth',
            'src/social/sample'
        );
        
        $extensionClassPaths = Config::get('extension_class_paths');
        if (!empty($extensionClassPaths)) {
            $locations = array_merge(explode(',', $extensionClassPaths), $locations);
        }

        // Check for the presense of this class in our all our directories.
        $fileName = $className . '.php';
        foreach ($locations as $path) {
            if (file_exists("{$path}/$fileName")) {
                require $path . '/' . $fileName;
                return true;
            }
        }
        return false;
    }
}