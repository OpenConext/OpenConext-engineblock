<?php

$attributes['email'] = $attributes['urn:mace:dir:attribute-def:mail'];
unset($attributes['urn:mace:dir:attribute-def:mail']);
$attributes['uid'] = $subjectId;
$attributes['sp']  = $response['__']['destinationid'];
