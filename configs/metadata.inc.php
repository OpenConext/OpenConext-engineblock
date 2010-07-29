<?php

$GLOBALS['metabase'] = array();

$GLOBALS['metabase']['hosted']= array(
    CORTO_BASE_URL . 'main' => array(
        'WantResponsesSigned' => true,
        'WantAssertionsSigned' => true,
        'outfilter'=>'engineBlockOutFilter',
    ),
);

engineBlockSetupMetadata();