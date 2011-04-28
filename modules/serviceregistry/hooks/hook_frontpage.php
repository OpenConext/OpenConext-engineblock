<?php

function ServiceRegistry_Hook_frontpage(&$links)
{
    assert('is_array($links)');

    $links['federation'][] = array(
        'href' => SimpleSAML_Module::getModuleURL('serviceregistry/show-entities-validation.php'),
        'text' => array('en' => 'Verify JANUS Entities'),
    );
}