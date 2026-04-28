<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\Wayf\WayfIdp;
use OpenConext\EngineBlockBundle\Service\IdpHistoryService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

class Wayf
{
    const PREVIOUS_SELECTION_COOKIE_NAME = 'selectedidps';
    const REMEMBER_CHOICE_COOKIE_NAME = 'rememberchoice';

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var array|null
     */
    private $previousSelection;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->previousSelection = $this->loadPreviousSelectionFromCookie($requestStack);
        $this->translator = $translator;
    }

    /**
     * @param array $idpList
     *
     * @return ConnectedIdps
     */
    #[AsTwigFunction(name: 'connectedIdps')]
    public function getConnectedIdps(array $idpList): ConnectedIdps
    {
        $previousSelectionIndex = $this->indexPreviousSelection();

        $formattedIdpList = $this->formatIdpList($idpList);
        $previousSelected = $this->filterPreviouslySelected(
            $formattedIdpList,
            $previousSelectionIndex
        );

        return new ConnectedIdps($previousSelected, $formattedIdpList);
    }

    /**
     * Create an index of previous selections by IDP identifier
     *
     * @return array<string, array<mixed>>
     */
    private function indexPreviousSelection(): array
    {
        if (empty($this->previousSelection)) {
            return [];
        }

        return array_column($this->previousSelection, null, 'idp');
    }

    private function formatIdpEntry(WayfIdp $idp): array
    {
        $keywords = $idp->keywords;

        return [
            'entityId'      => $idp->entityId,
            'connected'     => $idp->accessible,
            'displayTitle'  => $idp->name,
            'title'         => strtolower($idp->name ?? ''),
            'keywords'      => strtolower(implode('|', $keywords)),
            'logo'          => $idp->logo,
            'isDefaultIdp'  => $idp->isDefaultIdp,
            'discoveryHash' => $idp->discoveryHash,
        ];
    }

    private function formatIdpList(array $idpList): array
    {
        return array_map(
            fn(WayfIdp $idp) => $this->formatIdpEntry($idp),
            $idpList
        );
    }

    private function filterPreviouslySelected(
        array $formattedList,
        array $previousSelectionIndex
    ): array {
        return array_filter(
            array_map(
                function (array $idp) use ($previousSelectionIndex) {
                    $entryKey = $this->idpDiscoveryHash($idp['entityId'], $idp['discoveryHash']);
                    if (!isset($previousSelectionIndex[$entryKey])) {
                        return null;
                    }
                    return array_merge(
                        $previousSelectionIndex[$entryKey],
                        $idp
                    );
                },
                $formattedList
            )
        );
    }

    /**
     * Retrieve the Wayf config used in JavaScript
     *
     * @param ConnectedIdps $connectedIdPs,
     * @param ServiceProvider $serviceProvider
     * @param string $currentLocale
     * @param bool $showRequestAccess Show unconnected IdP's ?
     * @param bool $rememberChoiceFeature Remember the chosen IdP in Wayf?
     * @param int $cutoffPointForShowingUnfilteredIdps The cutoff point for showing unfiltered IdP's
     *
     * @return string Returns a json encoded config string. Used by the JavaScript of the Wayf to behave as intended.
     */
    #[AsTwigFunction(name: 'wayfConfig')]
    public function getWayfJsonConfig(
        ConnectedIdps $connectedIdPs,
        ServiceProvider $serviceProvider,
        $currentLocale,
        $showRequestAccess,
        $rememberChoiceFeature,
        $cutoffPointForShowingUnfilteredIdps
    ) {

        if ($showRequestAccess === true) {
            $unconnectedIdps = array_filter(
                $connectedIdPs->getFormattedIdpList(),
                function ($idp) {
                    return !$idp['connected'];
                }
            );
        } else {
            $unconnectedIdps = [];
        }

        return json_encode(
            [
                'previousSelectionCookieName' => self::PREVIOUS_SELECTION_COOKIE_NAME,
                'previousSelectionList' => $connectedIdPs->getFormattedPreviousSelectionList(),
                'connectedIdps' => array_values($connectedIdPs->getConnectedIdps()),
                'unconnectedIdps' => array_values($unconnectedIdps),
                'cutoffPointForShowingUnfilteredIdps' => $cutoffPointForShowingUnfilteredIdps,
                'rememberChoiceCookieName' => self::REMEMBER_CHOICE_COOKIE_NAME,
                'rememberChoiceFeature' => $rememberChoiceFeature,
                'messages' => [
                    'moreIdpResults' => $this->translator->trans('more_idp_results'),
                    'requestAccess' => $this->translator->trans('request_access'),
                ],
                'requestAccessUrl' => '/authentication/idp/requestAccess?'.http_build_query(
                    [
                        'lang' => $currentLocale,
                        'spEntityId' => $serviceProvider->entityId,
                        'spName' => $serviceProvider->getDisplayName($currentLocale),
                    ]
                ),
            ],
            JSON_PRETTY_PRINT
        );
    }

    private function loadPreviousSelectionFromCookie(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();
        $previousSelection = null;
        $previousSelectionIndexed = [];
        if ($request) {
            $previousSelection = json_decode(
                $request->cookies->get(self::PREVIOUS_SELECTION_COOKIE_NAME, ''),
                true
            );
            if ($previousSelection) {
                // And index the previous selection on IdP entity ID
                foreach ($previousSelection as $item) {
                    $previousSelectionIndexed[$item['idp']] = $item;
                }
            }
        }
        return $previousSelectionIndexed;
    }

    #[AsTwigFunction(name: 'idpDiscoveryHash')]
    public function idpDiscoveryHash(string $entityId, ?string $discoveryHash = null): string
    {
        return (new IdpHistoryService())->makeIdpDiscoveryHash($entityId, $discoveryHash);
    }
}
