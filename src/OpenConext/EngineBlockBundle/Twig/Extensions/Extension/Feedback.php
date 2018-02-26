<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use EngineBlock_ApplicationSingleton;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFunction;
use Twig_Extension;

class Feedback extends Twig_Extension
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $application;

    public function __construct(EngineBlock_ApplicationSingleton $application)
    {
        $this->application = $application;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('feedbackInfo', [$this, 'getFeedbackInfo'], ['is_safe' => ['html']]),
            new TwigFunction('flushLog', [$this, 'flushLog'], ['is_safe' => ['html']]),
            new TwigFunction('var_export', [$this, 'varExport'], ['is_safe' => ['html']]),
            new TwigFunction('var_dump', [$this, 'varDump'], ['is_safe' => ['html']]),
            new TwigFunction('print_r', [$this, 'printHumanReadable'], ['is_safe' => ['html']]),
        ];
    }

    public function flushLog($message)
    {
        // For now use the EngineBlock_ApplicationSingleton to flush the log
        $this->application->flushLog($message);
    }

    public function getFeedbackInfo()
    {
        return $this->retrieveFeedbackInfo();
    }

    /**
     * Provides var dump functionality for use in Twig templates
     *
     * @SuppressWarnings(PHPMD.CamelCaseParameterName)
     *
     * @param mixed $expression
     * @param mixed $_
     * @return string
     */
    public function varDump($expression, $_ = null)
    {
        ob_start();
        var_dump(func_get_args());
        return ob_get_flush();
    }

    /**
     * Provide var export functionality for use in Twig templates
     * @param mixed $expression
     * @return string
     */
    public function varExport($expression)
    {
        return var_export($expression, true);
    }

    /**
     * Returns the output of print_r with the added instruction to return the output as a string.
     * @param mixed $expression
     * @return string
     */
    public function printHumanReadable($expression)
    {
        return print_r($expression, true);
    }

    /**
     * Loads the feedbackInfo from the session and filters out empty valued entries.
     *
     * @SuppressWarnings(PHPMD.Superglobals) In this case the SESSION super global is used to read error feedback. This
     *                                       feedback is not yet stored in a Symfony managed session but uses the
     *                                       super global.
     *
     * @return array|mixed
     */
    private function retrieveFeedbackInfo()
    {
        $feedbackInfo = $_SESSION['feedbackInfo'];

        // Remove the empty valued feedback info entries.
        if (!empty($feedbackInfo)) {
            foreach ($feedbackInfo as $key => $value) {
                if (empty($value)) {
                    unset($feedbackInfo[$key]);
                }
            }
        }
        return $feedbackInfo;
    }
}
