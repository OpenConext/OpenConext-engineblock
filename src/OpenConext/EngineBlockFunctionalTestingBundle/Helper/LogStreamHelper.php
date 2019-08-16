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

/**
 * Class LogStreamHelper
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Helper
 * @SuppressWarnings("PMD")
 */
class LogStreamHelper
{
    const LINE_LENGTH = 2048;

    const STOP = false;

    protected $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
        $this->rewind();
    }

    public function foreachLine($fn)
    {
        while (!feof($this->stream) && $line = stream_get_line($this->stream, self::LINE_LENGTH, "\n")) {
            $line .= "\n";

            $callbackResult = $fn($line);

            if ($callbackResult === false) {
                return $this;
            }
        }
        return $this;
    }

    public function foreachLineReverse($fn)
    {
        $line = '';
        $pos = -2;

        if (feof($this->stream)) {
            fseek($this->stream, -1, SEEK_CUR);
            $line = fgetc($this->stream);
        }

        while (fseek($this->stream, $pos, SEEK_CUR) !== -1) {
            $char = fgetc($this->stream);

            if ($char !== "\n") {
                $line = $char . $line;
                continue;
            }
            $line = $line . $char;

            if ($fn($line) === static::STOP) {
                return $this;
            }

            $line = '';
        }
        $fn($line);

        $this->rewind();

        return $this;
    }

    public function write($content)
    {
        fwrite($this->stream, $content);
        return $this;
    }

    public function rewind()
    {
        rewind($this->stream);
        return $this;
    }

    public function close()
    {
        fclose($this->stream);
        return $this;
    }

    public function isAtEndOfFile()
    {
        return feof($this->stream);
    }

    public function onEndOfFile($fn)
    {
        if ($this->isAtEndOfFile()) {
            $fn();
        }
        return $this;
    }

    public function isAtStartOfFile()
    {
        return ftell($this->stream) === 0;
    }

    public function __toString()
    {
        $this->rewind();
        return stream_get_contents($this->stream);
    }

    public function toStream()
    {
        return $this->stream;
    }

    public function __destruct()
    {
        $this->close();
    }
}
