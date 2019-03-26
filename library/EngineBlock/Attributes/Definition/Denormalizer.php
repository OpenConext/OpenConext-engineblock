<?php

class EngineBlock_Attributes_Definition_Denormalizer
{
    public function denormalize(array $definitions)
    {
        foreach ($definitions as $attributeName => $definition) {
            // Has a definition, no need to denormalize.
            if (is_array($definition)) {
                continue;
            }

            // Resolve aliases and remember until we get to the actual definition.
            $aliases = array($attributeName);
            while (!is_array($definition)) {
                $attributeName = $definitions[$attributeName];

                if (!isset($definitions[$attributeName])) {
                    throw new EngineBlock_Exception(
                        sprintf(
                            'Unable to resolve definition for "%s", path: "%s"',
                            $attributeName,
                            join(' > ', $aliases)
                        )
                    );
                }

                $definition = $definitions[$attributeName];
                $aliases[] = $attributeName;
            }

            // Replace the alias by the definition and add a key to show where the definition came from.
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
