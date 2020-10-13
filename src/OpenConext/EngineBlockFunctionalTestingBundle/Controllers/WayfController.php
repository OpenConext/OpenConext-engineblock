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

use OpenConext\EngineBlockFunctionalTestingBundle\Helper\TestEntitySeeder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

/**
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Controllers
 * @SuppressWarnings("PMD")
 */
class WayfController extends Controller
{
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function wayfAction(Request $request)
    {
        $currentLocale = $request->get('lang', 'en');
        $request->cookies->set('lang', $currentLocale);
        $backLink = (bool) $request->get('backLink', false);
        $displayUnconnectedIdpsWayf = (bool) $request->get('displayUnconnectedIdpsWayf', false);
        $rememberChoiceFeature = (bool) $request->get('rememberChoiceFeature', false);
        $cutoffPointForShowingUnfilteredIdps = $request->get('cutoffPointForShowingUnfilteredIdps', 100);
        $showIdPBanner = $request->get('showIdPBanner', true);
        // Casting a string 'true' or 'false' using filter_var (bool) does not work here
        $showIdPBanner = filter_var($showIdPBanner, FILTER_VALIDATE_BOOLEAN);

        $connectedIdps = (int) $request->get('connectedIdps', 5);
        $unconnectedIdps = (int) $request->get('unconnectedIdps', 0);

        return new Response($this->twig->render(
            '@theme/Authentication/View/Proxy/wayf.html.twig',
            [
                'action' => 'https://engine.vm.openconext.org/',
                'greenHeader' => $currentLocale,
                'helpLink' => '/authentication/idp/help-discover?lang='.$currentLocale,
                'backLink' => $backLink,
                'cutoffPointForShowingUnfilteredIdps' => $cutoffPointForShowingUnfilteredIdps,
                'showIdPBanner' => $showIdPBanner,
                'rememberChoiceFeature' => $rememberChoiceFeature,
                'showRequestAccess' => $displayUnconnectedIdpsWayf,
                'requestId' => 'bogus-request-id',
                'serviceProvider' => TestEntitySeeder::buildSp(),
                'idpList' => TestEntitySeeder::buildIdps($connectedIdps, $unconnectedIdps, $currentLocale),
                'beforeScriptHtml' => '<div id="request-access-scroller"><div id="request-access-container">' .
                    '<div id="request-access"></div></div></div>',
            ]
        ));
    }
}
