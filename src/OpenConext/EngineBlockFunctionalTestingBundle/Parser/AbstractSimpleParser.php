<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Parser;

/**
 * Class AbstractSimpleParser
 */
abstract class AbstractSimpleParser
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * Create a new PrintRParser giving it the content it needs to parse.
     *
     * @param $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Turn on 'echo' debugging (off by default).
     */
    public function setDebugMode()
    {
        $this->debug = true;
    }

    /**
     * @return mixed
     */
    abstract public function parse();

    /**
     * @param $terminal
     * @return bool
     * @throws \RuntimeException
     */
    protected function consume($terminal)
    {
        $this->debug("  ->consume('$terminal')");
        $match = $this->match($terminal);

        // Throw a fit if we can't find what we expected.
        if ($match === false) {
            $terminal = str_replace("\n", '\n', $terminal);
            throw new \RuntimeException(
                "Unable to match terminal '$terminal' in content: '" .
                str_replace("\n", '\n', substr($this->content, 0, 50)) . "'..."
            );
        }

        // Strip the consumed bit off the beginning.
        $this->content = substr($this->content, strlen($match));
        $this->debug('  consumed: "' . str_replace("\n", '\n', $match) . '"');

        return $match;
    }

    /**
     * @param $terminal
     * @return bool
     */
    protected function lookAhead($terminal)
    {
        $matched = $this->match($terminal);
        $this->debug(
            "lookAhead('" . str_replace("\n", '\n', $terminal) . "') " .
            ($matched ? "found: '" . str_replace("\n", '\n', $matched) . "'" : 'not found' ) .
            " in content: '" . str_replace("\n", '\n', substr($this->content, 0, 120)) . "'"
        );
        return ($matched !== false);
    }

    /**
     * @param $terminal
     * @return bool
     */
    protected function match($terminal)
    {
        // Escape the delimiter.
        $terminal = str_replace('/', '\\/', $terminal);
        $regex = '/^(' . $terminal . ')/';

        $matches = [];
        if (!preg_match($regex, $this->content, $matches)) {
            return false;
        }

        return $matches[0];
    }

    /**
     * @param $line
     */
    protected function debug($line)
    {
        if (!$this->debug) {
            return;
        }

        echo $line . PHP_EOL;
    }
}
