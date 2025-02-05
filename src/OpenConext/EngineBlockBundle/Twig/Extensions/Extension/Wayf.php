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
use OpenConext\EngineBlockBundle\Service\IdpHistoryService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\TwigFunction;
use Twig_Extension;

class Wayf extends Twig_Extension
{
    const PREVIOUS_SELECTION_COOKIE_NAME = 'selectedidps';
    const REMEMBER_CHOICE_COOKIE_NAME = 'rememberchoice';

    private const ACCESS_ENABLED = '1';

    /**
     * @var TranslatorInterface
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

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'wayfConfig',
                [$this, 'getWayfJsonConfig']
            ),
            new TwigFunction(
                'connectedIdps',
                [$this, 'getConnectedIdps']
            ),
            new TwigFunction(
                'idpDiscoveryHash',
                [$this, 'idpDiscoveryHash']
            ),

        ];
    }

    /**
     * @param array $idpList
     *
     * @return ConnectedIdps
     */
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

    private function formatIdpEntry(array $idp): array
    {
        $keywords = $idp['Keywords'] === 'Undefined'
            ? []
            : array_values((array)$idp['Keywords']);

        // In SingleSignOn.php, the IDP is transformed into an array for the frontend
        // Then, here, the array is transformed into another array for the frontend which is actually used in twig
        return [
            'entityId' => $idp['EntityID'],
            'connected' => $idp['Access'] === self::ACCESS_ENABLED,
            'displayTitle' => $idp['Name'],
            'title' => strtolower($idp['Name']),
            'keywords' => strtolower(implode('|', $keywords)),
            'logo' => $idp['Logo'],
            'isDefaultIdp' => (bool)$idp['isDefaultIdp'],
            'discoveryHash' => $idp['DiscoveryHash']
        ];
    }

    private function formatIdpList(array $idpList): array
    {
        return array_map(
            function (array $idp) {
                return $this->formatIdpEntry($idp);
            },
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
                $request->cookies->get(self::PREVIOUS_SELECTION_COOKIE_NAME, null),
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

    public function idpDiscoveryHash(string $entityId, ?string $discoveryHash = null): string
    {
        return (new IdpHistoryService())->makeIdpDiscoveryHash($entityId, $discoveryHash);
    }
}
