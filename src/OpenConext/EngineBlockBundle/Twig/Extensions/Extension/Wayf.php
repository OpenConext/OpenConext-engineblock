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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFunction;
use Twig_Extension;

class Wayf extends Twig_Extension
{
    const PREVIOUS_SELECTION_COOKIE_NAME = 'selectedidps';
    const REMEMBER_CHOICE_COOKIE_NAME = 'rememberchoice';


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

        ];
    }

    /**
     * @param array $idpList
     * @param $locale
     *
     * @return ConnectedIdps
     */
    public function getConnectedIdps(
        array $idpList,
        $locale
    ) {
        $previousSelectionIndex = [];
        if (!empty($this->previousSelection)) {
            foreach ($this->previousSelection as $idp) {
                $previousSelectionIndex[$idp['idp']] = $idp;
            }
        }

        $formattedPreviousSelectionList = [];
        $formattedIdpList = [];
        foreach ($idpList as $idp) {
            $idpKeywords = $idp['Keywords'] === 'Undefined' ? array() : array_values((array)$idp['Keywords']);

            if (isset($previousSelectionIndex[$idp['EntityID']])) {
                $formattedPreviousSelectionList[] = array_merge(
                    $previousSelectionIndex[$idp['EntityID']],
                    [
                        'entityId' => $idp['EntityID'],
                        'connected' => $idp['Access'] === '1',
                        'displayTitle' => $idp['Name'],
                        'title' => strtolower($idp['Name']),
                        'keywords' => strtolower(join('|', $idpKeywords)),
                        'logo' => $idp['Logo'],
                        'isDefaultIdp' => (bool) $idp['isDefaultIdp'],
                    ]
                );
            }

            $formattedIdpList[] = [
                'entityId' => $idp['EntityID'],
                'connected' => $idp['Access'] === '1',
                'displayTitle' => $idp['Name'],
                'title' => strtolower($idp['Name']),
                'keywords' => strtolower(join('|', $idpKeywords)),
                'logo' => $idp['Logo'],
                'isDefaultIdp' => (bool) $idp['isDefaultIdp'],
            ];
        }

        return new ConnectedIdps($formattedPreviousSelectionList, $formattedIdpList);
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
}
