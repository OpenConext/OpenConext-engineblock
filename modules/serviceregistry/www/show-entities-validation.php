<?php
/**
 * SURFconext Service Registry
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
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

$session = SimpleSAML_Session::getInstance();

$janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
$authSource = $janusConfig->getValue('auth', 'login-admin');

// Validate user
if (!$session->isValid($authSource)) {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php', array('selectedtab'=>"'federation'")));
}

$entities = array();

$util = new sspmod_janus_AdminUtil();
foreach ($util->getEntities() as $entity) {
    $entityId = $entity['eid'];

    $entityController = new sspmod_serviceregistry_EntityController($janusConfig);
    $entityController->setEntity($entityId);
    $entityController->loadEntity();

    $controllerEntity = $entityController->getEntity();

    $entityType         = $controllerEntity->getType();
    if (!isset($entities[$entityType])) {
        $entities[$entityType] = array();
    }
    $entities[$entityType][] = array(
        'Id'                => $controllerEntity->getEntityid(),
        'Name'              => $controllerEntity->getPrettyname(),
        'WorkflowStatus'    => $controllerEntity->getWorkflow(),
        'MetadataUrl'       => $controllerEntity->getMetadataURL(),
    );
}
ksort($entities);
$template = new SimpleSAML_XHTML_Template(
    SimpleSAML_Configuration::getInstance(),
    'serviceregistry:show-entities-validation.php',
    'serviceregistry:show-entities-validation'
);

$template->data['header'] = "Service Registry JANUS entities validation";
$template->data['entities'] = $entities;
$template->show();