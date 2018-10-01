<?php

namespace OpenConext\EngineBlock\Validator;

interface ValidatorInterface
{
    /**
     * @param mixed $input
     * @return boolean
     */
    public function validate($input);
}
