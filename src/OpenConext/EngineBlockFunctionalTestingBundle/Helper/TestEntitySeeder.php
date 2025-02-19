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
use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlockBundle\Service\DiscoverySelectionService;
use Webmozart\Assert\Assert;

class TestEntitySeeder
{
    /**
     * Build a collection of IdPs
     *
     * This is not an array of IdentityProvider value objects, but a derivative that can be used for showing IdPs on
     * the WAYF.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param int $numberOfIdps
     * @param int $numberOfUnconnectedIdps
     * @param string $locale
     * @return array[]
     */
    public static function buildIdps($numberOfIdps, $numberOfUnconnectedIdps, $locale, $defaultIdpEntityId, bool $addDiscoveries)
    {
        Assert::integer($numberOfIdps);
        Assert::integer($numberOfUnconnectedIdps);
        Assert::stringNotEmpty($locale);

        if ($numberOfIdps < $numberOfUnconnectedIdps) {
            throw new LogicException('The number of IdPs that are to be created should be greater or equal to the number of unconnected IdPs');
        }

        // Discoveries
        $idps = [];

        for ($i=1; $i < (int) ($numberOfIdps - $numberOfUnconnectedIdps) + 1; $i++) {
            $entityId = sprintf("https://example.com/entityId/%d", $i);
            $name = sprintf("%s IdP %d %s", 'Connected', $i, $locale);
            $isDefaultIdp = false;
            if ($defaultIdpEntityId === $entityId) {
                $isDefaultIdp = true;
            }

            $discoveries = [];
            if ($addDiscoveries && $i === 1) {
                $discoveries = [
                    Discovery::create(
                        ['en' => 'National University of the Netherlands', 'nl' => 'Rijksuniversiteit der Nederlanden'],
                        ['en' => 'royal', 'nl' => 'koninklijke'],
                        new Logo('/images/logo.png')
                    ),
                    Discovery::create(
                        ['en' => 'Foreign embassy of the Republic'],
                        [],
                        null
                    )
                ];
            }

            $idps[$entityId] = [
                'name' => $name,
                'enabled' => true,
                'isDefaultIdp' => $isDefaultIdp,
                'discoveries' => $discoveries,
            ];
        }

        if ($numberOfUnconnectedIdps > 0) {
            for ($i=1; $i < (int) $numberOfUnconnectedIdps + 1; $i++) {
                $entityId = sprintf("https://unconnected.example.com/entityId/%d", $i);
                $name = sprintf("%s IdP %d %s", 'Disconnected', $i, $locale);
                $isDefaultIdp = false;
                if ($defaultIdpEntityId === $entityId) {
                    $isDefaultIdp = true;
                }

                $discoveries = [];
                if ($addDiscoveries && $i === 1) {
                    $discoveries = [
                        Discovery::create(
                            ['en' => 'Disconnected National University of the Netherlands', 'nl' => 'Disconnected Rijksuniversiteit der Nederlanden'],
                            ['en' => 'Disconnected royal', 'nl' => 'Disconnected koninklijke'],
                            new Logo('/images/logo.png')
                        ),
                        Discovery::create(
                            ['en' => 'Disconnected Foreign embassy of the Republic'],
                            [],
                            null
                        )
                    ];
                }

                $idps[$entityId] = ['name' => $name, 'enabled' => false, 'isDefaultIdp' => $isDefaultIdp, 'discoveries' => $discoveries];
            }
        }

        return self::transformIdpsForWayf($idps, $locale);
    }

    /**
     * Build a random collection of (unconnected) IdPs
     *
     * This is not an array of IdentityProvider value objects, but a derivative that can be used for showing IdPs on
     * the WAYF.
     *
     * @param int $numberOfIdps
     * @param int $numberOfUnconnectedIdps
     * @param string $locale
     * @return array[]
     */
    public static function buildRandomIdps($numberOfIdps, $locale, $defaultIdpEntityId)
    {
        Assert::integer($numberOfIdps);
        Assert::stringNotEmpty($locale);
        $idpNames = [
            'Academisch Medisch Centrum (AMC)',
            'AMOLF',
            'Amphia Hospital',
            'Breda University of Applied Sciences',
            'Centraal Planbureau',
            'Centrum Wiskunde & Informatica',
            'Cito',
            'Delft University of Technology',
            'Drenthe College',
            'eduID (NL)',
            'Erasmus MC',
            'Fontys University of Applied Sciences',
            'Friesland College',
            'Graafschap College',
            'GÉANT Staff Identity Provider',
            'HAN University of Applied Sciences',
            'Hotelschool The Hague',
            'IHE Delft Institute for Water Education',
            'KNMI',
            'Koninklijke Nederlandse Akademie van Wetenschappen (KNAW)',
            'Leids Universitair Medisch Centrum',
            'Maastricht University',
            'Netherlands eScience Center',
            'SURF bv',
            'Thomas More Hogeschool',
            'VSNU',
        ];
        $randomIdpNames = $numberOfIdps < count($idpNames) ? array_rand($idpNames, $numberOfIdps) : array_keys($idpNames);

        $idps = [];

        for ($i=1; $i < (int) $numberOfIdps + 1; $i++) {
            $connected = rand(0, 1) === 1;
            $entityId = $connected ? sprintf("https://example.com/entityId/%d", $i) : sprintf("https://unconnected.example.com/entityId/%d", $i);

            if ($i < 25) {
                $name = sprintf("%s %d %s", $idpNames[$randomIdpNames[$i - 1]], $i, $locale);
            } else {
                $variableString = $connected ? 'Connected' : 'Disconnected';
                $name = sprintf("%s IdP %d %s", $variableString, $i, $locale);
            }

            $isDefaultIdp = false;
            if ($defaultIdpEntityId === $entityId) {
                $isDefaultIdp = true;
            }
            $idps[$entityId] = ['name' => $name, 'enabled' => $connected, 'isDefaultIdp' => $isDefaultIdp];
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
        $discoveryService = new DiscoverySelectionService();
        $identityProviders = self::findIdentityProvidersByEntityId($idpEntityIds);

        $wayfIdps = array();
        foreach ($identityProviders as $identityProvider) {
            $name = 'name' . ucfirst($currentLocale);
            $wayfIdp = array(
                'Name' => $identityProvider->$name,
                'Logo' => $identityProvider->logo ? $identityProvider->logo->url : '/images/placeholder.png',
                'Keywords' => $identityProvider->keywordsEn,
                'Access' => ($identityProvider->enabledInWayf) ? '1' : '0',
                'ID' => md5($identityProvider->entityId),
                'EntityID' => $identityProvider->entityId,
                'isDefaultIdp' => $idpEntityIds[$identityProvider->entityId]['isDefaultIdp'],
            );
            $wayfIdps[] = $wayfIdp;

            foreach ($identityProvider->getDiscoveries() as $discovery) {
                $wayfIdps[] = array(
                    'Name' => $discovery->getName($currentLocale),
                    'Logo' => $discovery->getLogo() ? $discovery->getLogo()->url : '/images/placeholder.png',
                    'Keywords' => $discovery->getKeywords('en'),
                    'Access' => ($identityProvider->enabledInWayf) ? '1' : '0',
                    'ID' => md5($identityProvider->entityId),
                    'EntityID' => $identityProvider->entityId,
                    'isDefaultIdp' => $idpEntityIds[$identityProvider->entityId]['isDefaultIdp'],
                    'DiscoveryHash' => $discoveryService->hash($discovery),
                );
            }
        }

        $nameSort = function ($a, $b) {
            return strtolower($a['Name']) > strtolower($b['Name']);
        };

        // Sort the IdP entries by name
        usort($wayfIdps, $nameSort);

        return $wayfIdps;
    }

    /**
     * @return IdentityProvider[]
     */
    private static function findIdentityProvidersByEntityId(array $idpEntityIds): array
    {
        $idps = [];
        foreach ($idpEntityIds as $idpEntityId => $idpData) {
            $idp = new IdentityProvider($idpEntityId);
            $idp->getMdui()->setLogo(new Logo('/images/logo.png'));
            $idp->nameEn = $idpData['name'];
            $idp->nameNl = $idpData['name'];
            $idp->namePt = $idpData['name'];
            $idp->keywordsEn = ['Awesome IdP', 'Another keyword', 'Example'];
            $idp->enabledInWayf = $idpData['enabled'];
            $idp->setDiscoveries($idpData['discoveries'] ?? []);

            $idps[] = $idp;
        }

        return $idps;
    }

    /**
     * Build a very rudimentary SP entity
     * @return ServiceProvider
     */
    public static function buildSp(?string $spName = null)
    {
        if (!$spName) {
            $spName = 'DisplayName';
        }
        $serviceProvider = new ServiceProvider('https://acme-sp.example.com');
        $serviceProvider->nameNl = $spName . ' NL';
        $serviceProvider->nameEn = $spName . ' EN';
        $serviceProvider->namePt = $spName . ' PT';
        $serviceProvider->displayNameNl = $spName . '';
        $serviceProvider->displayNameEn = $spName . '';
        $serviceProvider->displayNamePt = $spName . '';
        $serviceProvider->getMdui()->setLogo(new Logo('/images/logo.png'));
        return $serviceProvider;
    }

    /**
     * Build a very rudimentary IdP entity
     * @return IdentityProvider
     */
    public static function buildIdP(?string $idpName)
    {
        if (!$idpName) {
            $idpName = 'DisplayName';
        }
        $identityProvider = new IdentityProvider('https://acme-idp.example.com');
        $identityProvider->nameNl = $idpName . ' NL';
        $identityProvider->nameEn = $idpName . ' EN';
        $identityProvider->namePt = $idpName . ' PT';
        $identityProvider->displayNameNl = $idpName . ' NL';
        $identityProvider->displayNameEn = $idpName . ' EN';
        $identityProvider->displayNamePt = $idpName . ' PT';
        $identityProvider->getMdui()->setLogo(new Logo('/images/logo.png'));
        return $identityProvider;
    }
}
