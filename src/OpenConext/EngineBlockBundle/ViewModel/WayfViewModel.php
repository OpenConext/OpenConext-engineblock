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

namespace OpenConext\EngineBlockBundle\ViewModel;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\Wayf\WayfIdp;
use OpenConext\EngineBlockBundle\Twig\Extensions\Extension\ConnectedIdps;

final readonly class WayfViewModel
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public string $action,
        public string $greenHeader,
        public string $helpLink,
        public bool $backLink,
        public int $cutoffPointForShowingUnfilteredIdps,
        public bool $showIdPBanner,
        public bool $rememberChoiceFeature,
        public bool $showRequestAccess,
        public string $requestId,
        public ServiceProvider $serviceProvider,
        public ConnectedIdps $connectedIdps,
        public ConnectedIdps $regularConnectedIdps,
        public ConnectedIdps $preferredConnectedIdps,
        public bool $showPreferredIdps,
        // These WayfIdp arrays kept for backward compatibility with custom theme overrides.
        // The base / skeune theme do not use them, but potentially downstream themes may rely on them so we keep them in.
        /** @var WayfIdp[] */
        public array $idpList,
        /** @var WayfIdp[] */
        public array $regularIdpList,
        /** @var WayfIdp[] */
        public array $preferredIdpList,
    ) {
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $vars['idpList'] = array_map(static fn(WayfIdp $idp) => $idp->toArray(), $this->idpList);
        $vars['regularIdpList'] = array_map(static fn(WayfIdp $idp) => $idp->toArray(), $this->regularIdpList);
        $vars['preferredIdpList'] = array_map(static fn(WayfIdp $idp) => $idp->toArray(), $this->preferredIdpList);
        return $vars;
    }
}
