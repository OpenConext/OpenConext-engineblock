<?php
namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use OpenConext\EngineBlockBundle\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;
use Twig_Extension;

/**
 * The Locale extension can be used to retrieve the currently active locale. By default returns the locale that
 * can be found in the RequestStack. If none can be found in the request stack, the default locale is returned.
 */
class Locale extends Twig_Extension
{
    /**
     * @var string
     */
    private $locale;

    public function __construct(RequestStack $requestStack, $defaultLocale)
    {
        $this->locale = $this->retrieveLocale($requestStack, $defaultLocale);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('locale', [$this, 'getLocale'], ['is_safe' => ['html']])
        ];
    }

    public function getLocale()
    {
        return $this->locale;
    }

    private function retrieveLocale(RequestStack $requestStack, $defaultLocale)
    {
        $currentRequest = $requestStack->getCurrentRequest();
        $locale = $defaultLocale;
        if ($currentRequest) {
            $locale = $currentRequest->getLocale();
        }
        return $locale;
    }
}
