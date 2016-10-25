<?php

namespace OpenConext\EngineBlockBundle\Controller\Api;

use EngineBlock_Arp_AttributeReleasePolicyEnforcer;
use OpenConext\EngineBlock\Service\MetadataService;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use OpenConext\EngineBlockBundle\Http\Request\JsonRequestHelper;
use OpenConext\EngineBlockBundle\Http\Response\JsonResponse;
use OpenConext\Value\Saml\EntityId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity) Extensive request validation
 * @SuppressWarnings(PHPMD.NPathComplexity) Extensive request validation
 */
final class AttributeReleasePolicyController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var MetadataService
     */
    private $metadataService;

    /**
     * @var EngineBlock_Arp_AttributeReleasePolicyEnforcer
     */
    private $arpEnforcer;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        MetadataService $metadataService,
        EngineBlock_Arp_AttributeReleasePolicyEnforcer $arpEnforcer
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->metadataService      = $metadataService;
        $this->arpEnforcer          = $arpEnforcer;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function applyArpAction(Request $request)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_API_USER_PROFILE')) {
            throw new ApiAccessDeniedHttpException('Access to the ARP API requires the role ROLE_API_USER_PROFILE');
        }

        $body = JsonRequestHelper::decodeContentAsArrayOf($request);

        if (!is_array($body)) {
            throw new BadApiRequestHttpException(sprintf(
                'Unrecognized structure for JSON: expected decoded root value to be an array, got "%s"',
                gettype($body)
            ));
        }

        if (!isset($body['entityIds'])) {
            throw new BadApiRequestHttpException('Invalid JSON structure: key "entityIds" not found');
        }

        if (!is_array($body['entityIds']) || empty($body['entityIds'])) {
            throw new BadApiRequestHttpException('Invalid JSON structure: "entityIds" must be a non-empty array');
        }

        if (!isset($body['attributes'])) {
            throw new BadApiRequestHttpException('Invalid JSON structure: key "attributes" not found');
        }

        if (!is_array($body['attributes'])) {
            throw new BadApiRequestHttpException('Invalid JSON structure: "attributes" must be a JSON object');
        }

        foreach ($body['attributes'] as $attributeName => $attributeValues) {
            if (!is_string($attributeName) || !is_array($attributeValues)) {
                throw new BadApiRequestHttpException(
                    'Invalid JSON structure: attributes should have strings as keys and an array of values'
                );
            }
        }

        $releasedAttributes = [];
        foreach ($body['entityIds'] as $entityId) {
            $arp = $this->metadataService->findArpForServiceProviderByEntityId(new EntityId($entityId));
            $releasedAttributes[$entityId] = $this->arpEnforcer->enforceArp($arp, $body['attributes']);
        }

        return new JsonResponse(json_encode($releasedAttributes));
    }
}
