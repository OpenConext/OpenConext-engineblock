<?php

header('Content-Type: application/samlmetadata+xml');
echo file_get_contents('metadata-idp.xml');