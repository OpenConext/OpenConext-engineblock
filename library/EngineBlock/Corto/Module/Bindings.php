<?php

class EngineBlock_Corto_Module_Bindings extends Corto_Module_Bindings
{
      
    protected function _receiveMessage($key)
    {
        $message = parent::_receiveMessage($key);
        
        if ($key==Corto_Module_Bindings::KEY_REQUEST) {
           // We're dealing with a request, on its way towards the idp. If there's a VO context, we need to store it in the request.
           
            $voContext = $this->_server->getVirtualOrganisationContext();
            if ($voContext!=NULL) {
                $message['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_KEY] = $voContext;
            }
                        
        }
        
        return $message;
    }
}