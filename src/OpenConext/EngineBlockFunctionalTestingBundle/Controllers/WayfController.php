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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
        $showGlobalSiteNotice = $request->get('showGlobalSiteNotice', false);
        $message = <<<MSG
<p>
    There is nothing wrong with your television set.
    <strong>Do not attempt to adjust the picture.</strong>
    We are controlling transmission. If we wish to make it louder, we will bring up the volume.
    If we wish to make it softer, we will tune it to a whisper. We will control the horizontal.
    We will control the vertical.  We can roll the image, make it flutter.
    We can change the focus to a soft blur, or sharpen it to crystal clarity.
</p>
<p>
    <strong>For the next hour, sit quietly and we will control all that you see and hear.</strong>
    We repeat: There is nothing wrong with your television set. You are about to participate in a great adventure.
    You are about to experience the awe and mystery which reaches from the inner mind to... The Outer Limits.
</p>
MSG;
        $globalSiteNotice = $request->get('globalSiteNotice', $message);
        $defaultIdpEntityId = $request->get('defaultIdpEntityId', null);
        // Casting a string 'true' or 'false' using filter_var (bool) does not work here
        $showIdPBanner = filter_var($showIdPBanner, FILTER_VALIDATE_BOOLEAN);

        $connectedIdps = (int) $request->get('connectedIdps', 5);
        $unconnectedIdps = (int) $request->get('unconnectedIdps', 0);
        $randomIdps = (int) $request->get('randomIdps', 0);

        $idpList = $randomIdps === 0
            ? TestEntitySeeder::buildIdps($connectedIdps, $unconnectedIdps, $currentLocale, $defaultIdpEntityId)
            : TestEntitySeeder::buildRandomIdps($randomIdps, $currentLocale, $defaultIdpEntityId);

        return new Response($this->twig->render(
            '@theme/Authentication/View/Proxy/wayf.html.twig',
            [
                'action' => $this->generateUrl('functional_testing_handle_wayf'),
                'greenHeader' => $currentLocale,
                'helpLink' => '/authentication/idp/help-discover?lang='.$currentLocale,
                'backLink' => $backLink,
                'cutoffPointForShowingUnfilteredIdps' => $cutoffPointForShowingUnfilteredIdps,
                'showIdPBanner' => $showIdPBanner,
                'showGlobalSiteNotice' => $showGlobalSiteNotice,
                'globalSiteNotice' => $globalSiteNotice,
                'rememberChoiceFeature' => $rememberChoiceFeature,
                'showRequestAccess' => $displayUnconnectedIdpsWayf,
                'requestId' => 'bogus-request-id',
                'serviceProvider' => TestEntitySeeder::buildSp(),
                'idpList' => $idpList,
                'beforeScriptHtml' => '<div id="request-access-scroller"><div id="request-access-container">' .
                    '<div id="request-access"></div></div></div>',
            ]
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
