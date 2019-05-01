<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Controllers;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

/**
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Controllers
 * @SuppressWarnings("PMD")
 */
class FeedbackController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TranslatorInterface $translator,
        Twig_Environment $twig,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->logger = $logger;

        // we have to start the old session in order to be able to retrieve the feedback info
        $server = new \EngineBlock_Corto_ProxyServer($twig);
        $server->startSession();
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function feedbackAction(Request $request)
    {
        $key = $this->getTemplate($request);
        $feedbackInfo = $this->getFeedbackInfo($request);
        $parameters = $this->getTemplateParameters($request);

        $this->validateFooterTemplateParameters($parameters);

        $session = $request->getSession();

        $template = sprintf(
            '@theme/Authentication/View/Feedback/%s.html.twig',
            $key
        );

        $session->set('feedbackInfo', $feedbackInfo);

        return new Response($this->twig->render($template, $parameters), 200);
    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    private function getTemplate(Request $request)
    {
        $key = $request->get('template');
        if (!$key) {
            $key = 'session-lost';
        }

        return $key;
    }

    /**
     * @param Request $request
     * @return mixed|string
     */
    private function getFeedbackInfo(Request $request)
    {
        $default = '{
            "timestamp":"2019-04-15T19:21:06+02:00",
            "requestId":"5cb4bd3879b49",
            "userAgent":"Mozilla\/5.0 (X11; Ubuntu; Linux x86_64; rv:66.0) Gecko\/20100101 Firefox\/66.0",
            "ipAddress":"192.168.66.98",
            "artCode":"31914"
        }';

        $feedbackInfo = $request->get('feedback-info', $default);

        $feedbackInfo = json_decode($feedbackInfo, true);

        return $feedbackInfo;
    }


    /**
     * @param Request $request
     * @return mixed|string
     */
    private function getTemplateParameters(Request $request)
    {
        $default = '{}';

        $parameters = $request->get('parameters', $default);

        $parameters = json_decode($parameters, true);

        return $parameters;
    }


    /**
     * @param array $parameters
     * @return null|string
     */
    private function validateFooterTemplateParameters(array &$parameters)
    {
        $key = 'supportEmail';
        if (!array_key_exists($key, $parameters)) {
            $parameters[$key] = null;
        }

        $key = 'showWikiButton';
        if (!array_key_exists($key, $parameters)) {
            $parameters[$key] = false;
        }
    }
}
