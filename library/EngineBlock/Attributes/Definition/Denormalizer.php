<?php

class EngineBlock_Attributes_Definition_Denormalizer
{
    public function denormalize(array $definitions)
    {
        foreach ($definitions as $attributeName => $definition) {
            if (is_array($definition)) {
                continue;
            }

            $aliases = array($attributeName);
            while (!is_array($definition)) {
                $attributeName = $definitions[$attributeName];

                if (empty($definitions[$attributeName])) {
                    // @todo log
                    break;
                }
                $definition = $definitions[$attributeName];
                $aliases[] = $attributeName;
            }

            foreach ($aliases as $alias) {
                $definitions[$alias] = $definition;
                if ($attributeName !== $alias && is_array($definitions[$alias])) {
                    $definitions[$alias]['__original__'] = $attributeName;
                }
            }
        }
        return $definitions;
    }
}
