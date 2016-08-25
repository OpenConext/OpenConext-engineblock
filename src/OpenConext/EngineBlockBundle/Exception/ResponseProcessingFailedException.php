<?php

namespace OpenConext\EngineBlockBundle\Exception;

use RuntimeException;

class ResponseProcessingFailedException extends RuntimeException implements PadResponseTimeMarkerInterface
{
}
