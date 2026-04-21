<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlockBundle\Service;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\Wayf\IdpSplitter;
use Twig\Environment;

class WayfRenderer
{
    public function __construct(
        private readonly WayfViewModelFactory $factory,
        private readonly IdpSplitter $splitter,
        private readonly Environment $twig,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function render(
        array $idpList,
        array $preferredIdpEntityIds,
        string $action,
        string $currentLocale,
        string $defaultIdpEntityId,
        bool $shouldDisplayBanner,
        bool $backLink,
        int $cutoffPoint,
        bool $rememberChoice,
        bool $showRequestAccess,
        string $requestId,
        ServiceProvider $serviceProvider,
    ): string {
        $split = $this->splitter->split($idpList, $preferredIdpEntityIds);
        $showPreferredIdps = !empty($split['preferred']);
        $isDefaultIdpPreferred = in_array($defaultIdpEntityId, $preferredIdpEntityIds, true);

        $showIdPBanner = $shouldDisplayBanner
            && $this->isDefaultIdpPresent($idpList)
            && (!$showPreferredIdps || !$isDefaultIdpPreferred);

        $viewModel = $this->factory->create(
            idpList: $idpList,
            regularIdpList: $split['regular'],
            preferredIdpList: $split['preferred'],
            showPreferredIdps: $showPreferredIdps,
            action: $action,
            greenHeader: $serviceProvider->getDisplayName($currentLocale),
            helpLink: '/authentication/idp/help-discover?lang=' . $currentLocale,
            backLink: $backLink,
            cutoffPointForShowingUnfilteredIdps: $cutoffPoint,
            showIdPBanner: $showIdPBanner,
            rememberChoiceFeature: $rememberChoice,
            showRequestAccess: $showRequestAccess,
            requestId: $requestId,
            serviceProvider: $serviceProvider,
            showRequestAccessContainer: true,
        );

        return $this->twig->render('@theme/Authentication/View/Proxy/wayf.html.twig', $viewModel->toArray());
    }

    private function isDefaultIdpPresent(array $idpList): bool
    {
        return array_any($idpList, fn($idp) => ($idp['isDefaultIdp'] ?? false) === true);
    }
}
