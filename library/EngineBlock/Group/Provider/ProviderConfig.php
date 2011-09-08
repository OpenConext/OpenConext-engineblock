<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * Read the configuration out of the database
 */
class EngineBlock_Group_Provider_ProviderConfig {

    /**
     * Create an Zend_Config of Group Provider(s) from database configuration
     *
     * @param $providerId the unique identifier of the Provider (optional)
     * @return Zend_Config
     */
    public function createFromDatabaseFor($providerId = null)
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        $db = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_READ);
        $sql = 'SELECT *
            FROM group_provider';
        $parameters = array();
        if ($providerId) {
            $sql .= " WHERE identifier = ?";
            $parameters[] = $providerId;
        }
        $statement = $db->prepare($sql);
        $statement->execute($parameters);
        $groupProviderRows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $groupProviders = array();
        foreach ($groupProviderRows as $groupProviderRow) {
            $groupProvider = array(
                'id'        => $groupProviderRow['identifier'],
                'name'      => $groupProviderRow['name'],
                'className' => $groupProviderRow['classname'],
            );

            // Retrieve options
            $optionRows = $db->query(
                "SELECT `name`, `value`
                FROM group_provider_option
                WHERE group_provider_id = {$groupProviderRow['id']}"
            )->fetchAll(PDO::FETCH_ASSOC);
            foreach ($optionRows as $optionRow) {
                $groupProviderOptionPointer = &$groupProvider;
                $optionNameParts = explode('.', $optionRow['name']);
                $lastOptionNamePart = null;
                while ($optionNamePart = array_shift($optionNameParts)) {
                    if (!isset($groupProviderOptionPointer[$optionNamePart]) && !empty($optionNameParts)) {
                        $groupProviderOptionPointer[$optionNamePart] = array();
                    }
                    $groupProviderOptionPointer = &$groupProviderOptionPointer[$optionNamePart];
                }
                $groupProviderOptionPointer = $optionRow['value'];
            }

            // decorators
            $decoratorAndOptionsRows = $db->query(
                "SELECT gpd.id        AS id,
                        gpd.classname AS className,
                        gpdo.name     AS option_name,
                        gpdo.value    AS option_value
                FROM group_provider_decorator gpd
                LEFT JOIN group_provider_decorator_option gpdo ON gpd.id = gpdo.group_provider_decorator_id
                WHERE gpd.group_provider_id = {$groupProviderRow['id']}"
            );
            if (!empty($decoratorAndOptionsRows)) {
                $groupProvider['decorators'] = array();
                foreach ($decoratorAndOptionsRows as $decoratorOptionsRow) {
                    if (!isset($groupProvider['decorators'][$decoratorOptionsRow['id']])) {
                        $groupProvider['decorators'][$decoratorOptionsRow['id']] = array();
                    }
                    $groupProvider['decorators'][$decoratorOptionsRow['id']]['className'] = $decoratorOptionsRow['className'];
                    if (isset($decoratorOptionsRow['option_name']) && $decoratorOptionsRow['option_name']) {
                        $groupProvider['decorators'][$decoratorOptionsRow['id']][$decoratorOptionsRow['option_name']] = $decoratorOptionsRow['option_value'];
                    }
                }
            }

            // filters
            $filterAndOptionsRows = $db->query(
                "SELECT gpf.id        AS id,
                        gpf.type      AS type,
                        gpf.classname AS className,
                        gpfo.name     AS option_name,
                        gpfo.value    AS option_value
                FROM group_provider_filter gpf
                LEFT JOIN group_provider_filter_option gpfo ON gpf.id = gpfo.group_provider_filter_id
                WHERE gpf.group_provider_id = {$groupProviderRow['id']}"
            );
            foreach ($filterAndOptionsRows as $filterOptionsRow) {
                if (!isset($groupProvider[$filterOptionsRow['type'] . 'Filters'])) {
                    $groupProvider[$filterOptionsRow['type'] . 'Filters'] = array();
                }
                $filters = &$groupProvider[$filterOptionsRow['type'] . 'Filters'];
                if (!isset($filters[$filterOptionsRow['id']])) {
                    $filters[$filterOptionsRow['id']] = array();
                }
                $filters[$filterOptionsRow['id']]['className'] = $filterOptionsRow['className'];
                if (isset($filterOptionsRow['option_name']) && $filterOptionsRow['option_name']) {
                    $filters[$filterOptionsRow['id']][$filterOptionsRow['option_name']] = $filterOptionsRow['option_value'];
                }
            }

            // preconditions
            $preconditionAndOptionsRows = $db->query(
                "SELECT gpp.id        AS id,
                        gpp.classname AS className,
                        gppo.name     AS option_name,
                        gppo.value    AS option_value
                FROM group_provider_precondition gpp
                LEFT JOIN group_provider_precondition_option gppo ON gpp.id = gppo.group_provider_precondition_id
                WHERE gpp.group_provider_id = {$groupProviderRow['id']}"
            );
            if (!empty($preconditionAndOptionsRows)) {
                $groupProvider['preconditions'] = array();
                foreach ($preconditionAndOptionsRows as $preconditionOptionsRow) {
                    if (!isset($groupProvider['preconditions'][$preconditionOptionsRow['id']])) {
                        $groupProvider['preconditions'][$preconditionOptionsRow['id']] = array();
                    }
                    $groupProvider['preconditions'][$preconditionOptionsRow['id']]['className'] = $preconditionOptionsRow['className'];
                    if (isset($preconditionOptionsRow['option_name']) && $preconditionOptionsRow['option_name']) {
                        $groupProvider['preconditions'][$preconditionOptionsRow['id']][$preconditionOptionsRow['option_name']] = $preconditionOptionsRow['option_value'];
                    }
                }
            }

            $groupProviders[] = $groupProvider;
        }
        return new Zend_Config($groupProviders);
    }


}
