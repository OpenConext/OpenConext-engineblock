<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class IndexController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $keyPairs;

    public function __construct(Environment $twig, array $keyPairs)
    {
        $this->twig = $twig;
        $this->keyPairs = $keyPairs;
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        $keyPairIds = [];
        if ($this->keyPairs) {
            $keyPairIds = array_keys($this->keyPairs);
        }

        return new Response(
            $this->twig->render(
                '@theme/Authentication/View/Index/index.html.twig',
                [
                    'subHeader' => 'IdP Certificate and Metadata',
                    'wide' => true,
                    'displayLanguageSwitcher' => false,
                    'keyPairIds' => $keyPairIds
                ]
            )
        );
    }
}
