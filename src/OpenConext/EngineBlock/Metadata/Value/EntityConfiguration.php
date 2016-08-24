<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class EntityConfiguration implements Serializable
{
    /**
     * @var AttributeManipulationCode
     */
    private $attributeManipulationCode;

    /**
     * @var WorkflowState
     */
    private $workflowState;

    /**
     * @var bool
     */
    private $requiresAdditionalLogging;

    /**
     * @var bool
     */
    private $disableScoping;

    /**
     * @var bool
     */
    private $requiresSignedRequests;

    /**
     * @param AttributeManipulationCode $attributeManipulationCode
     * @param WorkflowState             $workflowState
     * @param bool                      $requiresAdditionalLogging
     * @param bool                      $disableScoping
     * @param bool                      $requiresSignedRequests
     */
    public function __construct(
        AttributeManipulationCode $attributeManipulationCode,
        WorkflowState $workflowState,
        $requiresAdditionalLogging,
        $disableScoping,
        $requiresSignedRequests
    ) {
        Assertion::boolean($requiresAdditionalLogging);
        Assertion::boolean($disableScoping);
        Assertion::boolean($requiresSignedRequests);

        $this->attributeManipulationCode = $attributeManipulationCode;
        $this->workflowState             = $workflowState;
        $this->requiresAdditionalLogging = $requiresAdditionalLogging;
        $this->disableScoping            = $disableScoping;
        $this->requiresSignedRequests    = $requiresSignedRequests;
    }

    /**
     * @return AttributeManipulationCode
     */
    public function getAttributeManipulationCode()
    {
        return $this->attributeManipulationCode;
    }

    /**
     * @return WorkflowState
     */
    public function getWorkflowState()
    {
        return $this->workflowState;
    }

    /**
     * @return bool
     */
    public function requiresAdditionalLogging()
    {
        return $this->requiresAdditionalLogging;
    }

    /**
     * @return bool
     */
    public function isScopingDisabled()
    {
        return $this->disableScoping;
    }

    /**
     * @return bool
     */
    public function requiresSignedRequests()
    {
        return $this->requiresSignedRequests;
    }

    /**
     * @param EntityConfiguration $other
     * @return bool
     */
    public function equals(EntityConfiguration $other)
    {
        return $this->attributeManipulationCode->equals($other->attributeManipulationCode)
                && $this->workflowState->equals($other->workflowState)
                && $this->requiresAdditionalLogging === $other->requiresAdditionalLogging
                && $this->disableScoping === $other->disableScoping
                && $this->requiresSignedRequests === $other->requiresSignedRequests;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist(
            $data,
            [
                'attribute_manipulation_code',
                'workflow_state',
                'requires_additional_logging',
                'disable_scoping',
                'requires_signed_requests'
            ]
        );

        return new self(
            AttributeManipulationCode::deserialize($data['attribute_manipulation_code']),
            WorkflowState::deserialize($data['workflow_state']),
            $data['requires_additional_logging'],
            $data['disable_scoping'],
            $data['requires_signed_requests']
        );
    }

    public function serialize()
    {
        return [
            'attribute_manipulation_code' => $this->attributeManipulationCode->serialize(),
            'workflow_state'              => $this->workflowState->serialize(),
            'requires_additional_logging' => $this->requiresAdditionalLogging,
            'disable_scoping'             => $this->disableScoping,
            'requires_signed_requests'    => $this->requiresSignedRequests
        ];
    }

    public function __toString()
    {
        return sprintf(
            'EntityConfiguration(%s%srequiresAdditionalLogging=%s, disableScoping=%s, requiresSignedRequests=%s)',
            $this->attributeManipulationCode . ', ', // string concatenation so the line above doesn't need to be split
            $this->workflowState . ', ',
            ($this->requiresAdditionalLogging ? 'true' : 'false'),
            ($this->disableScoping ? 'true' : 'false'),
            ($this->requiresSignedRequests ? 'true' : 'false')
        );
    }
}
