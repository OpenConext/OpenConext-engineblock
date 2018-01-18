<?php

class EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException extends EngineBlock_Corto_Module_Bindings_Exception
{
    /**
     * @var string $signatureMethod
     */
    private $signatureMethod;

    /**
     * EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException constructor.
     * @param string $signatureMethod
     */
    public function __construct($signatureMethod)
    {
        parent::__construct(
            sprintf(
                'The signature method %s is not supported',
                $signatureMethod
            )
        );

        $this->signatureMethod = $signatureMethod;
    }

    /**
     * @return string
     */
    public function getSignatureMethod()
    {
        return $this->signatureMethod;
    }
}
