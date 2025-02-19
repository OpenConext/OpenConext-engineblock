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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Command;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\JsonDataStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump the contents of the (fake) Service Registry
 */
class DumpServiceRegistryCommand extends Command
{
    const COMMAND_NAME = 'engineblock:dump:sr';

    /**
     * @var JsonDataStore
     */
    private $dataStore;

    public function __construct(JsonDataStore $jsonDataStore, ?string $name = self::COMMAND_NAME)
    {
        parent::__construct($name);
        $this->dataStore = $jsonDataStore;
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setAliases(['dump:sr'])
            ->setDescription('Find all sessions from log output on STDIN or for a given file')
            ->addArgument('file', InputArgument::OPTIONAL, 'File to get sessions from.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write(print_r($this->dataStore->load(), true));
        return 0;
    }
}
