<?php
/**
 * Replaces bindings like SingleSignOn and SingleLogout with bindings of EngineBlock
  */
class EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer
{
    /**
     * @var array
     */
    private $entity;

    /**
     * @param array $entity
     */
    public function __construct(array &$entity)
    {
        $this->entity = &$entity;
    }

    /**
     * @param string $key
     * @param string $url
     * @param array $supportedBindings
     */
    public function replace($key, $location, array $supportedBindings)
    {
        $this->entity[$key] = array();
        foreach($supportedBindings as $binding) {
            $this->entity[$key][] = array(
                'Location'=> $location,
                'Binding' => $binding
            );
        }
    }
}