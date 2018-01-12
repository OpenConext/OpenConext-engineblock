<?php

namespace OpenConext\EngineBlock\Exception;

use DomainException as CoreDomainException;

final class DomainException extends CoreDomainException implements Exception
{
    /**
     * We override the parent constructor to only allow the message parameter. A DomainException does not need a
     * specific code nor should it be thrown as the result of a previous exception; it is always the result of a domain
     * constraint violation.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
