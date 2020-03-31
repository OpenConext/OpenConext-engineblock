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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Helper;

use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Logo;
use Webmozart\Assert\Assert;

class TestEntitySeeder
{
    /**
     * Build a collection of IdPs
     *
     * This is not an array of IdentityProvider value objects, but a derivative that can be used for showing IdPs on
     * the WAYF.
     *
     * @param int $numberOfIdps
     * @param int $numberOfUnconnectedIdps
     * @param string $locale
     * @return array[]
     */
    public static function buildIdps($numberOfIdps, $numberOfUnconnectedIdps, $locale)
    {
        Assert::integer($numberOfIdps);
        Assert::integer($numberOfUnconnectedIdps);
        Assert::stringNotEmpty($locale);

        if ($numberOfIdps < $numberOfUnconnectedIdps) {
            throw new LogicException('The number of IdPs that are to be created should be greater or equal to the number of unconnected IdPs');
        }

        $idps = [];

        for ($i=1; $i < (int) ($numberOfIdps - $numberOfUnconnectedIdps) + 1; $i++) {
            $entityId = sprintf("https://example.com/entityId/%d", $i);
            $name = sprintf("%s IdP %d", 'Connected', $i);
            $idps[$entityId] = ['name' => $name, 'enabled' => true];
        }

        if ($numberOfUnconnectedIdps > 0) {
            for ($i=1; $i < (int) $numberOfUnconnectedIdps + 1; $i++) {
                $entityId = sprintf("https://unconnected.example.com/entityId/%d", $i);
                $name = sprintf("%s IdP %d", 'Disconnected', $i);
                $idps[$entityId] = ['name' => $name, 'enabled' => false];
            }
        }

        return self::transformIdpsForWayf($idps, $locale);
    }

    /**
     * @param array $idpEntityIds
     * @param string $currentLocale
     * @return array[]
     */
    private static function transformIdpsForWayf(array $idpEntityIds, $currentLocale)
    {
        $identityProviders = self::findIdentityProvidersByEntityId($idpEntityIds);

        $wayfIdps = array();
        foreach ($identityProviders as $identityProvider) {
            $wayfIdp = array(
                'Name_nl' => $identityProvider->nameNl,
                'Name_en' => $identityProvider->nameEn,
                'Logo' => $identityProvider->logo ? $identityProvider->logo->url : '/images/placeholder.png',
                'Keywords' => $identityProvider->keywordsEn,
                'Access' => ($identityProvider->enabledInWayf) ? '1' : '0',
                'ID' => md5($identityProvider->entityId),
                'EntityID' => $identityProvider->entityId,
            );
            $wayfIdps[] = $wayfIdp;
        }

        $nameSort = function ($a, $b) use ($currentLocale) {
            return strtolower($a['Name_'.$currentLocale]) > strtolower($b['Name_'.$currentLocale]);
        };

        // Sort the IdP entries by name
        usort($wayfIdps, $nameSort);

        return $wayfIdps;
    }

    private static function findIdentityProvidersByEntityId(array $idpEntityIds)
    {
        $idps = [];
        foreach ($idpEntityIds as $idpEntityId => $idpData) {
            $idp = new IdentityProvider($idpEntityId);
            $idp->logo = new Logo('/images/logo.png');
            $idp->nameEn = $idpData['name'];
            $idp->nameNl = $idpData['name'];
            $idp->keywordsEn = ['Awesome IdP', 'Another keyword', 'Example'];
            $idp->enabledInWayf = $idpData['enabled'];

            $idps[] = $idp;
        }

        return $idps;
    }

    /**
     * Build a very rudimentary SP entity
     * @return ServiceProvider
     */
    public static function buildSp()
    {
        $serviceProvider = new ServiceProvider('https://acme-sp.example.com');
        $serviceProvider->nameNl = 'DisplayName';
        $serviceProvider->nameEn = 'DisplayName';
        $serviceProvider->displayNameNl = 'DisplayName';
        $serviceProvider->displayNameEn = 'DisplayName';
        $serviceProvider->logo = new Logo('/images/logo.png');
        return $serviceProvider;
    }

    /**
     * Build a very rudimentary IdP entity
     * @return IdentityProvider
     */
    public static function buildIdP()
    {
        $identityProvider = new IdentityProvider('https://acme-idp.example.com');
        $identityProvider->nameNl = 'DisplayName';
        $identityProvider->nameEn = 'DisplayName';
        $identityProvider->displayNameNl = 'DisplayName';
        $identityProvider->displayNameEn = 'DisplayName';
        $identityProvider->logo = new Logo('/images/logo.png');
        return $identityProvider;
    }
}
