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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump the contents of the (fake) application Registry
 */
class DumpServiceRegistryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('engineblock:dump:sr')
            ->setAliases(['dump:sr'])
            ->setDescription('Find all sessions from log output on STDIN or for a given file')
            ->addArgument('file', InputArgument::OPTIONAL, 'File to get sessions from.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var JsonDataStore $jsonDataStore */
        $jsonDataStore = $this->getContainer()->get('engineblock.functional_testing.data_store.service_registry');
        $output->write(print_r($jsonDataStore->load(), true));
        return 0;
    }
}
