<?php
/**
 * Can be used to get the merged config of engineblock in a recursively sorted format
 * This is useful to compare configs of various environments
 *
 * usage:
 * $configDumper = new EngineBlock_Application_ConfigDumper();
 * echo $configDumper->dump($zendConfigObject);
 */
class EngineBlock_Debug_ConfigExporter
{
    /**
     * Exports config
     *
     * @param Zend_Config $config
     * @param string $envId
     * @return string
     */
    public static function export(Zend_Config $config, $envId) {
        $configCopy = $config->toArray();
        self::ksortTree($configCopy);
        return print_r($configCopy, true);
    }

    /**
     * Sorts an array recursively by key
     *
     * @param array $array
     * @return bool
     */
    private static function ksortRecursive(array &$array)
    {
        if (!is_array($array)) {
            return false;
        }

        ksort($array);
        foreach ($array as $k=>$v) {
            self::ksortRecursive($array[$k]);
        }
        return true;
    }
}