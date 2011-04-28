<?php

$janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
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