<?php
// Move the groupProviders configuration from the local config to the database.

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

$command = new moveGroupProvidersConfigToDatabase($writer);
$command->execute();

class moveGroupProvidersConfigToDatabase
{
    const ENGINEBLOCK_LOCAL_CONFIG_FILE     = '/etc/openconext/engineblock.ini';
    const ENGINEBLOCK_MODIFIED_CONFIG_FILE  = '/tmp/engineblock.ini';

    /**
     * @var DbPatch_Core_Writer
     */
    protected $_writer;

    /**
     * @var \PDO
     */
    protected $_database;

    public function __construct($writer)
    {
        $this->_writer = $writer;

        $factory = new EngineBlock_Database_ConnectionFactory();
        $this->_database = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
    }

    public function execute()
    {
        if (!file_exists(self::ENGINEBLOCK_LOCAL_CONFIG_FILE)) {
            $this->_writer->warning(
                "Local config '" .
                self::ENGINEBLOCK_LOCAL_CONFIG_FILE .
                "'does not exist? Not doing anything"
            );
            return;
        }

        $engineBlock = EngineBlock_ApplicationSingleton::getInstance();
        $configuration = $engineBlock->getConfiguration()->toArray();

        if (!isset($configuration['groupProviders'])) {
            $this->_writer->warning("No groupProviders found in INI file (already ran?). Not doing anything");
            return;
        }

        $fileLines = file(self::ENGINEBLOCK_LOCAL_CONFIG_FILE);

        foreach ($configuration['groupProviders'] as $groupProviderIdentifier) {
            if (!isset($configuration[$groupProviderIdentifier])) {
                $this->_writer->warning(
                    "GroupProvider '$groupProviderIdentifier' mentioned, but not found? Not processing..."
                );
                continue;
            }

            // Remove configuration lines for this specific group provider
            foreach ($fileLines as $fileLineNumber => $fileLine) {
                $fileLine = trim($fileLine);
                if (strpos($fileLine, $groupProviderIdentifier . '.') === 0) {
                    unset ($fileLines[$fileLineNumber]);
                }
            }

            $statement = $this->_database->prepare("SELECT COUNT(*) as counted FROM group_provider WHERE identifier = ?");
            $statement->execute(array($groupProviderIdentifier));
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ((int)$row['counted'] > 0) {
                $this->_writer->warning("GroupProvider '$groupProviderIdentifier' already exists? Not processing...");
                continue;
            }

            $groupProviderConfig = $configuration[$groupProviderIdentifier];

            $groupProviderId = $this->_createGroupProviderRow($groupProviderIdentifier, $groupProviderConfig);
            if (!$groupProviderId) {
                $this->_writer->error(
                    "Unable to create the group provider?!? Last database error: " .
                    var_export($this->_database->errorCode(), true) .
                    var_export($this->_database->errorInfo(), true)
                );
                return;
            }

            $this->_convertGroupProviderDecorators($groupProviderId, $groupProviderConfig);
            $this->_convertGroupProviderFilters('group', $groupProviderId, $groupProviderConfig);
            $this->_convertGroupProviderFilters('groupMember', $groupProviderId, $groupProviderConfig);
            $this->_convertGroupProviderPreconditions($groupProviderId, $groupProviderConfig);
            $this->_convertGroupProviderConfigToOptions($groupProviderId, $groupProviderConfig);
        }

        // Remove groupProviders. lines from configFile
        foreach ($fileLines as $fileLineNumber => $fileLine) {
            $fileLine = trim($fileLine);
            if (strpos($fileLine, 'groupProviders.') === 0 || strpos($fileLine, 'groupProviders[]') === 0) {
                unset ($fileLines[$fileLineNumber]);
            }
        }

        // Output new config file
        $wroteFile = file_put_contents(self::ENGINEBLOCK_MODIFIED_CONFIG_FILE, implode($fileLines));
        if ($wroteFile === FALSE) {
            $this->_writer->warning(
                'Unable to write back modified local config file to ' .
                self::ENGINEBLOCK_MODIFIED_CONFIG_FILE .
                ' please remove the group providers for the local config file manually!'
            );
            return;
        }

        // Able to read and parse new INI file?
        $newLocalFileParsable = parse_ini_file(self::ENGINEBLOCK_LOCAL_CONFIG_FILE);
        if ($newLocalFileParsable === FALSE) {
            $this->_writer->warning(
                "Wrote changed config file to " .
                self::ENGINEBLOCK_MODIFIED_CONFIG_FILE .
                ' but unable to parse the INI file after writing? Please manually repair this file and move this file over to ' .
                self::ENGINEBLOCK_LOCAL_CONFIG_FILE .
                ' or manually remove the group provider configuration from ' .
                self::ENGINEBLOCK_LOCAL_CONFIG_FILE
            );
            return;
        }

        $this->_writer->warning("Wrote changed config file to " . self::ENGINEBLOCK_MODIFIED_CONFIG_FILE);
        $this->_writer->warning('Please verify that this configuration file is correct (backup the old config file)');
        $this->_writer->warning('and move this file over to ' . self::ENGINEBLOCK_LOCAL_CONFIG_FILE);
    }

    protected function _createGroupProviderRow($groupProviderIdentifier, &$groupProviderConfig)
    {
        // Create base group provider record
        $statement = $this->_database->prepare(
            "INSERT INTO group_provider (identifier, name, classname)
            VALUES (?,?,?)"
        );
        $params = array(
            $groupProviderIdentifier,
            $groupProviderConfig['name'],
            $groupProviderConfig['className'],
        );
        $result = $statement->execute($params);
        if ($result === false) {
            $this->_writer->error("Error occurred inserting the group provider, error code: " . var_export($statement->errorCode()));
            $this->_writer->error("Error info: " . print_r($statement->errorInfo(), true));
        }
        unset($groupProviderConfig['name'], $groupProviderConfig['className']);
        return $this->_database->lastInsertId();
    }

    protected function _convertGroupProviderDecorators($groupProviderId, &$groupProviderConfig)
    {
        // Convert decorators
        if (isset($groupProviderConfig['decorators'])) {
            foreach ($groupProviderConfig['decorators'] as $decorator) {
                $statement = $this->_database->prepare('
                INSERT INTO group_provider_decorator (group_provider_id, classname)
                VALUES (?, ?)
                ');
                $statement->execute(array(
                    $groupProviderId,
                    $decorator['className']
                ));
                $decoratorId = $this->_database->lastInsertId();
                unset($decorator['className']);

                foreach ($decorator as $optionName => $optionValue) {
                    $statement = $this->_database->prepare(
                        "INSERT INTO group_provider_decorator_option (group_provider_decorator_id, name, value)
                        VALUES(?,?,?)"
                    );
                    $statement->execute(
                        array(
                            $decoratorId,
                            $optionName,
                            $optionValue,
                        )
                    );
                }
            }
            unset($groupProviderConfig['decorators']);
        }
    }

    protected function _convertGroupProviderFilters($filterType, $groupProviderId, &$groupProviderConfig)
    {
        // Convert group filters
        if (isset($groupProviderConfig[$filterType . 'Filters'])) {
            foreach ($groupProviderConfig[$filterType . 'Filters'] as $filter) {
                $statement = $this->_database->prepare('
                INSERT INTO group_provider_filter (group_provider_id, type, classname)
                VALUES (?, ?, ?)
                ');
                $statement->execute(array(
                    $groupProviderId,
                    $filterType,
                    $filter['className']
                ));
                $filterId = $this->_database->lastInsertId();
                unset($filter['className']);

                foreach ($filter as $optionName => $optionValue) {
                    $statement = $this->_database->prepare(
                        "INSERT INTO group_provider_filter_option (group_provider_filter_id, name, value)
                        VALUES(?,?,?)"
                    );
                    $statement->execute(
                        array(
                            $filterId,
                            $optionName,
                            $optionValue,
                        )
                    );
                }
            }
            unset($groupProviderConfig[$filterType . 'Filters']);
        }
    }

    protected function _convertGroupProviderPreconditions($groupProviderId, &$groupProviderConfig)
    {
        // Convert preconditions
        if (isset($groupProviderConfig['preconditions'])) {
            foreach ($groupProviderConfig['preconditions'] as $precondition) {
                $statement = $this->_database->prepare('
                INSERT INTO group_provider_precondition (group_provider_id, classname)
                VALUES (?, ?)
                ');
                $statement->execute(array(
                    $groupProviderId,
                    $precondition['className']
                ));
                $preconditionId = $this->_database->lastInsertId();
                unset($precondition['className']);

                foreach ($precondition as $optionName => $optionValue) {
                    $statement = $this->_database->prepare(
                        "INSERT INTO group_provider_precondition_option (group_provider_precondition_id, name, value)
                        VALUES(?,?,?)"
                    );
                    $statement->execute(
                        array(
                            $preconditionId,
                            $optionName,
                            $optionValue,
                        )
                    );
                }
            }
            unset($groupProviderConfig['preconditions']);
        }
    }

    protected function _convertGroupProviderConfigToOptions($groupProviderId, $groupProviderConfig)
    {
        $groupProviderConfig = $this->_flattenGroupProviderConfig($groupProviderConfig);

        foreach ($groupProviderConfig as $optionName => $optionValue) {
            $statement = $this->_database->prepare(
               "INSERT INTO group_provider_option (group_provider_id, name, value)
               VALUES(?,?,?)"
            );
            $statement->execute(
                array(
                    $groupProviderId,
                    $optionName,
                    $optionValue,
                )
            );
        }
    }

    protected function _flattenGroupProviderConfig($groupProviderConfig, $prefix = "")
    {
        $newConfig = array();
        foreach ($groupProviderConfig as $optionName => $optionValue) {
            if (!is_array($optionValue)) {
                $newConfig[$prefix . $optionName] = $optionValue;
                continue;
            }

            $newConfig = array_merge($newConfig, $this->_flattenGroupProviderConfig($optionValue, $prefix . $optionName . '.'));
        }
        return $newConfig;
    }
}
