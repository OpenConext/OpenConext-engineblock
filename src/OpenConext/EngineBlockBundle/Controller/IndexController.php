<?php

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
