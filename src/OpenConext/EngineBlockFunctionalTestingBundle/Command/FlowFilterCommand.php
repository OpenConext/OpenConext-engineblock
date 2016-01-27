<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use OpenConext\EngineBlockFunctionalTestingBundle\Helper\FileOrStdInHelper;

/**
 * For a given list of session identifiers, filter out those without a login flow.
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Command
 * @SuppressWarnings("PMD")
 */
class FlowFilterCommand extends Command
{
    protected $debugging = false;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('engineblock:replay:flow:filter')
            ->setAliases(array('replay:flow:filter'))
            ->setDescription('Find all sessions that have an attached flow')
            ->addArgument('logfile', InputArgument::REQUIRED, 'File to get flows from')
            ->addArgument('sessionFile', InputArgument::OPTIONAL, 'File to get sessions from.')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Turn on debugging information')

            ->setHelp(
                'The <info>%command.name%</info> filters out the sessions with incomplete flows:' . PHP_EOL . PHP_EOL
                . '<info>grep "something" engineblock.log | app/console functional-testing:sessions:find |'
                . ' %command.full_name% engineblock.log</info>' . PHP_EOL . PHP_EOL
                . 'The optional argument specifies to read from a file (by default it reads from '
                . 'the standard input):' . PHP_EOL . PHP_EOL
                . '<info>php %command.full_name% engineblock.log engineblock.log</info>'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $debug = $this->debugging = $input->getOption('debug');

        if ($debug) {
            $output->writeln('[DEBUG] Starting filtering...');
        }

        // Open a stream to the logfile
        $logFile = $input->getArgument('logfile');
        if (!is_file($logFile)) {
            $output->writeln("<error>Logfile does not exist</error>");
            return 64;
        }
        $logStream = fopen($logFile, 'r');

        // Open a stream to the session file / input
        $sessionsStream = new FileOrStdInHelper($input, $output, 'sessionFile');

        // Loop through every line for the session file / input
        $that = $this;
        $sessionsStream->mapLines(function ($line) use ($logStream, $output, $that, $debug) {
            $sessionId = trim($line);
            if (!$sessionId) {
                return;
            }

            if ($debug) {
                $output->writeln("[DEBUG] Session: $sessionId");
            }

            $that->filterForSession($sessionId, $logStream, $output);
        });

        fclose($logStream);

        if ($debug) {
            $output->writeln("[DEBUG] Done filtering");
        }

        return 0;
    }

    /**
     * @param $sessionId
     * @param $logStream
     * @param OutputInterface $output
     */
    public function filterForSession($sessionId, $logStream, OutputInterface $output)
    {
        // The four horsemen^H^H^H^H^H^H^H^H^H messages we need to reconstruct a flow.
        $hasSpRequest   = false;
        $hasEbRequest   = false;
        $hasIdpResponse = false;
        $hasEbResponse  = false;

        rewind($logStream);
        $history = array(
            0 => '',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
        );
        while (!feof($logStream)) {
            $logLine = stream_get_line($logStream, 2048, "\n");

            if (strpos($logLine, $sessionId) === false) {
                continue;
            }

            if (!preg_match("/EB\\[$sessionId\\]\\[/", $logLine)) {
                continue;
            }

            if (strpos($logLine, '[Message INFO] Received response') !== false) {
                $hasIdpResponse = true;
            }

            if (strpos($logLine, '[Message INFO] Received request') !== false) {
                $hasSpRequest = true;
            }
            if (strpos($logLine, "DUMP 'Unsolicited Request'")) {
                $hasSpRequest = true;
            }

            if (strpos($logLine, '[Message INFO] Redirecting to ') !== false &&
                strpos($logLine, 'SAMLRequest') !== false) {
                $hasEbRequest = true;
            }

            if (strpos($logLine, '[Message INFO] HTTP-Post: Sending Message') !== false) {
                $hasAttributeValue = false;
                foreach ($history as $historyLine) {
                    if ($hasAttributeValue || strpos($historyLine, '[saml:AttributeValue]')) {
                        $hasAttributeValue = true;
                    }
                }

                if ($hasAttributeValue) {
                    $hasEbResponse = true;
                } else {
                    $hasEbRequest = true;
                }
            }

            if ($hasSpRequest && $hasEbRequest && $hasIdpResponse && $hasEbResponse) {
                $output->writeln(($this->debugging ? '[DEBUG] FOUND: ' : '') . $sessionId);
                return; // Done! Next session id
            }

            $history[] = $logLine;
            if (count($history) > 5) {
                array_shift($history);
            }
        }

        if ($this->debugging) {
            // Should output something like:
            // SP [X]<- ->[V] EB [V]<- ->[V] IDP
            $output->writeln(
                '[DEBUG] ' .
                'SP [' . ($hasEbResponse  ? '<fg=green>V</fg=green>':'<fg=red>X</fg=red>')   . ']<- ' .
                '->['  . ($hasSpRequest   ? '<fg=green>V</fg=green>':'<fg=red>X</fg=red>') . '] '.
                'EB [' . ($hasIdpResponse ? '<fg=green>V</fg=green>':'<fg=red>X</fg=red>') . ']<- '.
                '->['  . ($hasEbRequest   ? '<fg=green>V</fg=green>':'<fg=red>X</fg=red>') . '] IDP '
            );
        }
    }
}
