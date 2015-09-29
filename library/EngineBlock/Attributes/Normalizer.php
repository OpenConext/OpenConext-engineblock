<?php

class EngineBlock_Attributes_Normalizer
{
    /** @var Psr\Log\LoggerInterface */
    protected $_logger;

    /** @var array */
    protected $_attributes;

    /** @var array */
    protected $_definitions;

    /** @var array */
    protected $_definitionAliases;

    public function __construct($attributes)
    {
        $this->_attributes = $attributes;
    }

    public function normalize()
    {
        $this->_loadLogger();
        $this->_loadAttributeDefinitions();

        $newAttributes = array();

        /**
         * @var string $attributeName
         * @var array  $attributeValues
         */
        foreach ($this->_attributes as $attributeName => $attributeValues) {
            // Not defined in SURFconext attributes... can't find any aliases.
            if (!isset($this->_definitions[$attributeName])) {
                $newAttributes[$attributeName] = $attributeValues;
                continue;
            }

            // Traverse aliases to actual definition
            $originalAttributeName = $attributeName;
            $attributesSeen = array($attributeName);
            while (isset($this->_definitions[$attributeName]) && !is_array($this->_definitions[$attributeName])) {
                // Circular dependency check (Topological sorting)
                if (in_array($this->_definitions[$attributeName], $attributesSeen)) {
                    $this->_logger->error(
                        "Circular dependency detected in tree: " . implode(' => ', $attributesSeen) . ' => ' . $this->_definitions[$attributeName] .
                            " reverting back to original '$originalAttributeName'"
                    );
                    $attributeName = $originalAttributeName;
                    break;
                }

                $attributeName    = $this->_definitions[$attributeName];

                $attributesSeen[] = $attributeName;
            }
            if ($attributeName !== $originalAttributeName) {
                $this->_logger->debug(
                    "Attribute Normalization: '$originalAttributeName' resolves to '$attributeName'"
                );
            }

            // Whoa, a resolved alias that doesn't have a definition?
            if (!isset($this->_definitions[$attributeName])) {
                $this->_logger->error(
                    "Attribute Normalization: Attribute '$originalAttributeName' resolved to '$attributeName'".
                        " but this does not have a definition? Skipping this attribute and it's values"
                );
            }

            if (!isset($newAttributes[$attributeName])) {
                $newAttributes[$attributeName] = $attributeValues;
                continue;
            }

            // Note that array_diff does not work recursively
            $valuesDiff = array_diff($newAttributes[$attributeName], $attributeValues);
            if (empty($valuesDiff)) {
                $this->_logger->debug(
                    "Attribute Normalization: '$attributeName' (originally '$attributeName') ".
                        "already exists with the same value... doing nothing."
                );
                continue;
            }
            else {
                $this->_logger->notice(
                    "Attribute Normalization: '$attributeName' (originally '$attributeName') ".
                        "already exists with a different value... overwriting."
                );
                $newAttributes[$attributeName] = $attributeValues;
            }
        }
        return $newAttributes;
    }

    public function denormalize()
    {
        $this->_loadLogger();
        $this->_loadAttributeDefinitions();

        $newAttributes = array();
        foreach ($this->_attributes as $attributeName => $attributeValues) {
            $newAttributes[$attributeName] = $attributeValues;

            // Not defined in SURFconext attributes... can't find any aliases.
            if (!isset($this->_definitions[$attributeName])) {
                $this->_logger->debug(
                    "Attribute Denormalization: Don't have a definition for '$attributeName', unable to add any aliases"
                );
                continue;
            }

            $aliases = $this->_getAliasesForAttribute($attributeName);

            // And add the values for those aliases
            foreach ($aliases as $aliasName) {
                $this->_logger->debug(
                    "Attribute Denormalization: Adding alias '$aliasName' for '$attributeName'"
                );
                $newAttributes[$aliasName] = $attributeValues;
            }
        }
        return $newAttributes;
    }

    public function _getAliasesForAttribute($name, $aliases = array())
    {
        foreach ($this->_definitions as $attributeName => $attributeDefinition) {
            if ($attributeDefinition === $name) {
                $aliases[] = $attributeName;
                $aliases = $this->_getAliasesForAttribute($attributeName, $aliases);
            }
        }
        return $aliases;
    }

    protected function _loadAttributeDefinitions()
    {
        // Definitions loading default
        if (isset($this->_definitions)) {
            return $this->_definitions;
        }

        $this->_definitions = json_decode(
            file_get_contents(
                EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue(
                    'attributeDefinitionFile',
                    ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.json'
                )
            ),
            true
        );
        return $this->_definitions;
    }

    public function setLogger(Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    protected function _loadLogger()
    {
        if (!isset($this->_logger)) {
            $this->setLogger(EngineBlock_ApplicationSingleton::getLog());
        }
        return $this->_logger;
    }

    public function setDefinition($definitions)
    {
        $this->_definitions = $definitions;
        unset($this->_definitionAliases);
    }
}
