<?php
 
class Default_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
    }

    public function unknownUriAction($uri)
    {
        die("Unknown URI: $uri");
    }
}
