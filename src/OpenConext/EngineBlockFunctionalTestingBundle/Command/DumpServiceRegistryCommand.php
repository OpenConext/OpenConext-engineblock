<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Command;

use OpenConext\Component\EngineBlockFixtures\DataStore\JsonDataStore;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump the contents of the (fake) Service Registry
 */
class DumpServiceRegistryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('engineblock:dump:sr')
            ->setAliases(array('dump:sr'))
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
