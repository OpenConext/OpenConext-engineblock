<?php
$hostUrl = 'https://' . $_SERVER['HTTP_HOST'];
return array(
    $hostUrl .'/dummy/sp' => array(
        0 => $hostUrl .'/authentication/idp/metadata',
        1 => $hostUrl .'/dummy/idp',
        2 => $hostUrl .'/dummy/idp#2'
    ),
    $hostUrl .'/dummy/sp?nr=2' => array()
);