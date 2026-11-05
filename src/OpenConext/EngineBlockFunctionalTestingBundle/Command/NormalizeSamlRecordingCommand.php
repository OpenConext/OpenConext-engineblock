<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlockFunctionalTestingBundle\Command;

use InvalidArgumentException;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Recording\SamlXmlNormalizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'engineblock:saml:normalize-recording',
    description: 'Normalize SAML messages recorded during a Behat run so two runs can be compared with diff'
)]
final class NormalizeSamlRecordingCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED, 'Directory written by the recorder (EB_SAML_RECORD_DIR)')
            ->addArgument('target', InputArgument::REQUIRED, 'Directory to write the normalized dumps to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = rtrim((string) $input->getArgument('source'), '/');
        $target = rtrim((string) $input->getArgument('target'), '/');

        if (!is_dir($source)) {
            $output->writeln(sprintf('<error>Source directory "%s" does not exist</error>', $source));
            return Command::FAILURE;
        }

        $scenarios = 0;
        $messages = 0;
        $failures = 0;

        foreach (glob($source . '/*', GLOB_ONLYDIR) ?: [] as $scenarioDirectory) {
            $scenario = basename($scenarioDirectory);
            $files = glob($scenarioDirectory . '/*.xml') ?: [];
            sort($files);

            if (!is_dir($target . '/' . $scenario)) {
                mkdir($target . '/' . $scenario, 0777, true);
            }

            $normalizer = new SamlXmlNormalizer();
            $scenarios++;

            foreach ($files as $file) {
                try {
                    $dump = $normalizer->normalize((string) file_get_contents($file));
                } catch (InvalidArgumentException $exception) {
                    $dump = '!! ' . $exception->getMessage() . "\n";
                    $failures++;
                }

                file_put_contents($target . '/' . $scenario . '/' . basename($file, '.xml') . '.txt', $dump);
                $messages++;
            }
        }

        $output->writeln(sprintf(
            'Normalized %d messages from %d scenarios (%d unparsable)',
            $messages,
            $scenarios,
            $failures
        ));

        return Command::SUCCESS;
    }
}
