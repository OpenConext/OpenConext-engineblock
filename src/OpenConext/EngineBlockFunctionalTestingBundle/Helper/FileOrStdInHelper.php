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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Helper;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FileOrStdInHelper
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Command\Helper
 */
class FileOrStdInHelper
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $argName;

    /**
     * @param InputInterface    $input
     * @param OutputInterface   $output
     * @param string            $argName
     */
    public function __construct(InputInterface $input, OutputInterface $output, $argName = 'file')
    {
        $this->input = $input;
        $this->output = $output;
        $this->argName = $argName;
    }

    /**
     * @param $fn
     * @return array
     */
    public function mapLines($fn)
    {
        $stream = $this->getStream();

        $output = [];
        while (!feof($stream)) {
            $line = stream_get_line($stream, 1024, "\n");

            $fnOutput = $fn($line);
            if ($fnOutput) {
                $output[] = $fnOutput;
            }
        }
        return $output;
    }

    /**
     * @return resource|bool
     * @throws \Doctrine\Common\Proxy\Exception\InvalidArgumentException
     */
    protected function getStream()
    {
        $filename = $this->input->getArgument($this->argName);
        if (!$filename) {
            return STDIN;
        }

        if (!is_file($filename)) {
            throw new InvalidArgumentException(sprintf('File "%s" doesn\'t exist.', $filename));
        }

        return fopen($filename, 'r');
    }
}
