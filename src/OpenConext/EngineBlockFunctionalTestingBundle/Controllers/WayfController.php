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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Controllers;

use OpenConext\EngineBlock\Service\Wayf\IdpSplitter;
use OpenConext\EngineBlockBundle\Service\WayfViewModelFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Helper\TestEntitySeeder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Controllers
 * @SuppressWarnings("PMD")
 */
class WayfController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly IdpSplitter $idpSplitter,
        private readonly WayfViewModelFactory $wayfViewModelFactory,
    ) {
    }

    public function wayfAction(Request $request)
    {
        $currentLocale = $request->query->getString('lang', 'en');
        $request->cookies->set('lang', $currentLocale);
        $backLink = $request->query->getBoolean('backLink');
        $displayUnconnectedIdpsWayf = $request->query->getBoolean('displayUnconnectedIdpsWayf');
        $addDiscoveries = $request->query->getBoolean('addDiscoveries', true);
        $rememberChoiceFeature = $request->query->getBoolean('rememberChoiceFeature');
        $cutoffPointForShowingUnfilteredIdps = $request->query->getInt('cutoffPointForShowingUnfilteredIdps', 100);
        $showIdPBanner = $request->query->getBoolean('showIdPBanner', true);
        $defaultIdpEntityId = $request->query->get('defaultIdpEntityId');
        $preferredIdpEntityIds = $request->query->all('preferredIdpEntityIds');

        $connectedIdps = $request->query->getInt('connectedIdps', 5);
        $unconnectedIdps = $request->query->getInt('unconnectedIdps');
        $randomIdps = $request->query->getInt('randomIdps');

        $idpList = $randomIdps === 0
            ? TestEntitySeeder::buildIdps($connectedIdps, $unconnectedIdps, $currentLocale, $defaultIdpEntityId, $addDiscoveries)
            : TestEntitySeeder::buildRandomIdps($randomIdps, $currentLocale, $defaultIdpEntityId);

        $split = $this->idpSplitter->split($idpList, $preferredIdpEntityIds);
        $preferredIdpList = $split['preferred'];
        $regularIdpList = $split['regular'];
        $showPreferredIdps = !empty($preferredIdpList);

        $isDefaultIdpPreferred = in_array($defaultIdpEntityId, $preferredIdpEntityIds, true);
        $showIdPBanner = $showIdPBanner && (!$showPreferredIdps || !$isDefaultIdpPreferred);

        $viewModel = $this->wayfViewModelFactory->create(
            idpList: $idpList,
            regularIdpList: $regularIdpList,
            preferredIdpList: $preferredIdpList,
            showPreferredIdps: $showPreferredIdps,
            action: $this->generateUrl('functional_testing_handle_wayf'),
            greenHeader: $currentLocale,
            helpLink: '/authentication/idp/help-discover?lang=' . $currentLocale,
            backLink: $backLink,
            cutoffPointForShowingUnfilteredIdps: $cutoffPointForShowingUnfilteredIdps,
            showIdPBanner: $showIdPBanner,
            rememberChoiceFeature: $rememberChoiceFeature,
            showRequestAccess: $displayUnconnectedIdpsWayf,
            requestId: 'bogus-request-id',
            serviceProvider: TestEntitySeeder::buildSp(),
            showRequestAccessContainer: true,
        );

        return new Response($this->twig->render(
            '@theme/Authentication/View/Proxy/wayf.html.twig',
            $viewModel->toArray()
        ));
    }

    public function handleWayfAction(Request $request)
    {
        if ($request->request->has('idp')) {
            return $this->redirectToRoute(
                'open_conext_engine_block_authentication_homepage',
                [
                    'idp' => $request->request->get('idp')
                ]
            );
        }
        throw new AccessDeniedException('No IdP parameter found');
    }
}
