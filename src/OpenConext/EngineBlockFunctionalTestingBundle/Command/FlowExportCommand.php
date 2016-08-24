<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OpenConext\EngineBlockFunctionalTestingBundle\Helper\FileOrStdInHelper;
use OpenConext\EngineBlockFunctionalTestingBundle\Helper\LogStreamHelper;

/**
 * Export the first flow in a session a directory.
 * @SuppressWarnings("PMD")
 */
class FlowExportCommand extends Command
{
    /**
     * Command line usage error.
     *
     * Adopted from sysexits.h.
     */
    const EX_USAGE = 64;

    /**
     * @var string
     */
    protected $logFile;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('engineblock:replay:flow:export')
            ->setAliases(['replay:flow:export'])
            ->setDescription('Export all flows to a directory')
            ->addArgument(
                'logfile',
                InputArgument::REQUIRED,
                'File to get flows from.'
            )
            ->addArgument(
                'outputDir',
                InputArgument::OPTIONAL,
                'Directory to export flows to (defaults to the temporary directory).',
                sys_get_temp_dir()
            )
            ->addArgument(
                'sessionFile',
                InputArgument::OPTIONAL,
                'File to get sessions from (defaults to STDIN).'
            )
            ->setHelp(
                'The <info>%command.name%</info> command exports flows to a directory, example:' . PHP_EOL
                . '<info>grep "something" engineblock.log | app/console fu:sessions:find |  '. PHP_EOL
                . 'app/console fu:flow:filter | %command.full_name% engineblock.log</info>' . PHP_EOL . PHP_EOL
                . 'Find log lines with "something", from those get the sessions, for those sessions '
                . 'give only the sessions that have complete flows, for those sessions export all flows to /tmp.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->setLogFile($input, $output)) {
            return self::EX_USAGE;
        }

        if (!$this->setOutputDir($input, $output)) {
            return self::EX_USAGE;
        }

        $sessionsStream = new FileOrStdInHelper($input, $output, 'sessionFile');

        /**
         * Required because 5.3 doesn't allow you to use $this in a closure.
         * @var $that
         */
        $that = $this;
        $sessionsStream->mapLines(function ($line) use ($input, $output, $that) {
            $sessionId = trim($line);
            if (!$sessionId) {
                return;
            }

            $exportTarget = $that->exportSession($sessionId);
            $output->writeln("Exported flow for $sessionId to $exportTarget");
        });

        return 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function setLogFile(InputInterface $input, OutputInterface $output)
    {
        $logFile = $input->getArgument('logfile');
        if (!is_file($logFile)) {
            $output->writeln('<error>Logfile does not exist</error>');
            return false;
        }

        $this->logFile = $logFile;
        return true;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function setOutputDir(InputInterface $input, OutputInterface $output)
    {
        $outputDir = $input->getArgument('outputDir');
        if (!is_dir($outputDir) || !is_writable($outputDir)) {
            $output->writeln("<error>Directory '$outputDir' does not exist or is nog writable</error>");
            return false;
        }

        $this->outputDir = $outputDir;
        return true;
    }

    /**
     * @param $sessionId
     * @return string
     */
    public function exportSession($sessionId)
    {
        $sessionLog = $this->getSessionLog($sessionId);

        $sessionLog = $this->fixMessageOrdering($sessionLog);

        $directory = $this->outputDir. '/eb-flow-' . $sessionId;
        $this->createDirectory($directory);

        $this->writeFile($directory . '/session.log', $sessionLog);
        $this->writeFile($directory . '/sp.request.log', $this->getSpRequestFromSessionLog($sessionLog));
        $this->writeFile($directory . '/eb.request.log', $this->getEbRequestFromSessionLog($sessionLog));
        $this->writeFile($directory . '/idp.response.log', $this->getIdpResponseFromSessionLog($sessionLog));
        $this->writeFile($directory . '/eb.response.log', $this->getEbResponseFromSessionLog($sessionLog));
        return $directory;
    }

    /**
     * @param $sessionId
     * @return LogStreamHelper
     */
    protected function getSessionLog($sessionId)
    {
        // Look through the logfile for all loglines for this session.
        $logStream = new LogStreamHelper(fopen($this->logFile, 'r'));
        $sessionLogStream = new LogStreamHelper(fopen('php://temp', 'r+'));
        $logStream->foreachLine(function ($line) use ($sessionLogStream, $sessionId) {
            if (strpos($line, "EB[$sessionId]") === false) {
                return;
            }

            $sessionLogStream->write($line);
        });
        return $sessionLogStream;
    }

    // [Message INFO] FLUSHING ... [Message INFO] END OF LOG MESSAGE QUEUE
    /**
     * @param LogStreamHelper $sessionLog
     * @return LogStreamHelper
     */
    protected function fixMessageOrdering(LogStreamHelper $sessionLog)
    {
        $sessionLog->rewind();

        while (!$sessionLog->isAtEndOfFile()) {
            $sessionLog->foreachLine(function ($line) {
                if (strstr($line, '[Message INFO] END OF LOG MESSAGE QUEUE')) {
                    return LogStreamHelper::STOP;
                }
                return null;
            });
            if ($sessionLog->isAtEndOfFile()) {
                break;
            }

            $reversed = '';
            $maxLogLines = 100;

            // Look back for the start of the flush, collecting all the lines in reverse.
            $chunk = '';
            $sessionLog->foreachLineReverse(function ($line) use ($sessionLog, &$reversed, &$maxLogLines, &$chunk) {
                // EXCEPT for chunks, which must be kept in their original order...

                // And when we find a CHUNKEND we start 'Chunk mode' and append line to the chunk
                if (strstr($line, ']!CHUNKEND>')) {
                    $chunk = $line;
                    return null;
                }

                // So when we find a CHUNKSTART we start 'Chunk mode'
                if (strstr($line, ']!CHUNKSTART>')) {
                    $chunk = $line . $chunk;
                    $reversed .= $chunk;
                    $chunk = '';
                    return null;
                }

                // If we're not in chunk mode we can append the line though.
                // Going in reverse order so basically reversing the line order.
                if (!$chunk) {
                    $reversed .= $line;
                } else {
                    $chunk = $line . $chunk;
                }

                if (strstr($line, '[Message INFO] FLUSHING')) {
                    return LogStreamHelper::STOP;
                }

                if ($maxLogLines-- === 0) {
                    throw new \RuntimeException('Unable to find start of log flush in 100 lines?');
                }
                return null;
            });

            if (empty($reversed)) {
                throw new \RuntimeException("No chunk found?");
            }

            if ($sessionLog->isAtStartOfFile()) {
                throw new \RuntimeException("Unable to return to where queue was flushed?");
            }

            $sessionLog->write($reversed);
        }

        return $sessionLog;
    }

    /**
     * @param LogStreamHelper $sessionLogStream
     * @return bool|string
     */
    protected function getSpRequestFromSessionLog(LogStreamHelper $sessionLogStream)
    {
        // Starting at the beginning.
        $sessionLogStream->rewind();

        $message = $this->findFirstChunkedDumpPostfixedWith(
            $sessionLogStream,
            '[Message INFO] Received request'
        );
        if ($message) {
            return $message;
        }

        $message = $this->findLineWith(
            $sessionLogStream,
            "DUMP 'Unsolicited Request'"
        );
        if ($message) {
            return $message;
        }

        throw new \RuntimeException('No received request found.');
    }

    /**
     * @param LogStreamHelper $sessionLogStream
     * @return bool|string
     */
    protected function getEbRequestFromSessionLog(LogStreamHelper $sessionLogStream)
    {
        $message = $this->getEbRequestViaRedirect($sessionLogStream);
        if ($message) {
            return $message;
        }

        $message = $this->findMessageSentViaPostWithTag($sessionLogStream, 'AuthnRequest');
        if ($message) {
            return $message;
        }


        throw new \RuntimeException('No EB Request found?');
    }

    /**
     * @param LogStreamHelper $sessionLogStream
     * @return bool
     */
    protected function getEbRequestViaRedirect(LogStreamHelper $sessionLogStream)
    {
        // Starting at the beginning.
        $sessionLogStream->rewind();

        $found = false;
        $sessionLogStream->foreachLine(function ($line) use (&$found) {
            if (strstr($line, '[Message INFO] Redirecting to') && strstr($line, 'SAMLRequest=')) {
                $found = $line;
                return LogStreamHelper::STOP;
            }
        });
        return $found;
    }

    protected function getIdpResponseFromSessionLog(LogStreamHelper $sessionLogStream)
    {
        // Starting at the beginning.
        $sessionLogStream->rewind();

        $receivedResponse = $this->findReceivedResponse($sessionLogStream);
        if ($receivedResponse) {
            return $receivedResponse;
        }

        throw new \RuntimeException('No idp Response found?');
    }

    protected function getEbResponseFromSessionLog(LogStreamHelper $sessionLogStream)
    {
        $message = $this->findMessageSentViaPostWithTag($sessionLogStream, 'Response');
        if ($message) {
            return $message;
        }

        // If we can't find it, that's an error.
        throw new \RuntimeException('No Response found?');
    }

    protected function findMessageSentViaPostWithTag(LogStreamHelper $sessionLogStream, $tagName)
    {
        // Starting at the beginning.
        $sessionLogStream->rewind();

        // Try to find posted messages (Responses may only be POSTED).
        while ($postedMessage = $this->findMessageSentViaPost($sessionLogStream)) {

            // If the message found is NOT a response, keep looking.
            if (!strstr($postedMessage, '[__t] => samlp:' . $tagName)) {
                continue;
            }
            // Otherwise we found it.
            return $postedMessage;
        }
    }

    protected function findReceivedResponse(LogStreamHelper $sessionLogStream)
    {
        return $this->findFirstChunkedDumpPostfixedWith(
            $sessionLogStream,
            '[Message INFO] Received response'
        );
    }

    protected function findMessageSentViaPost(LogStreamHelper $sessionLogStream)
    {
        return $this->findFirstChunkedDumpPostfixedWith(
            $sessionLogStream,
            '[Message INFO] HTTP-Post: Sending Message'
        );
    }

    /**
     * @param LogStreamHelper $sessionLogStream
     * @param $needle
     * @return bool
     */
    protected function findLineWith(LogStreamHelper $sessionLogStream, $needle)
    {
        $sessionLogStream->rewind();

        // Loop through all the lines until you find the "Received message" line.
        $result = false;
        $sessionLogStream->foreachLine(function ($line) use ($needle, &$result) {
            if (strpos($line, $needle) !== false) {
                $result = $line;
                return LogStreamHelper::STOP;
            }
            return null;
        });
        return $result;
    }

    /**
     * @param LogStreamHelper $sessionLogStream
     * @param $postfixMessage
     * @return bool|string
     */
    protected function findFirstChunkedDumpPostfixedWith(LogStreamHelper $sessionLogStream, $postfixMessage)
    {
        // Loop through all the lines until you find the "Received message" line.
        $sessionLogStream->foreachLine(function ($line) use ($postfixMessage) {
            if (strpos($line, $postfixMessage)) {
                return LogStreamHelper::STOP;
            }
        });

        // If we reached the end then it must not have been found.
        if ($sessionLogStream->isAtEndOfFile()) {
            return false;
        }
        // Otherwise we start collecting the log chunk.

        // Find your way back to CHUNKSTART then stop.
        $sessionLogStream->foreachLineReverse(function ($line) {
            if (strpos($line, '!CHUNKSTART>')) {
                return LogStreamHelper::STOP;
            }
        });
        $sessionLogStream->onEndOfFile(function () {
            throw new \RuntimeException('No CHUNKSTART');
        });

        // Go forward again, collecting lines, until we find the "Received message" line.
        $logChunk = '';
        $sessionLogStream->foreachLine(function ($line) use (&$logChunk, $postfixMessage) {
            $logChunk .= $line;
            if (strpos($line, $postfixMessage)) {
                return LogStreamHelper::STOP;
            }
        });
        $sessionLogStream->onEndOfFile(function () use ($postfixMessage) {
            throw new \RuntimeException("Unable to find our way back to '$postfixMessage'");
        });

        return $logChunk;
    }

    /**
     * @param $path
     */
    protected function createDirectory($path)
    {
        @mkdir($path, 0700);
    }

    /**
     * @param $path
     * @param $log
     */
    protected function writeFile($path, $log)
    {
        file_put_contents($path, $log);
        chmod($path, 0700);
    }
}
